<?php
// listingDetails.php

require_once '../connectDatabase.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['listing_id']) && !isset($_POST['listing_id'])) {
    echo "Listing ID not provided!";
    exit();
}

// ENTITY LAYER: Represents the CarListing entity
class CarListing {
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_image;
    public $listing_color;
    public $listing_price;
    public $listing_description;
    public $mime_type;

    public function __construct($listing_id, $manufacturer_name, $model_name, $model_year, $listing_image, $listing_color, $listing_price, $listing_description, $mime_type) {
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
        $this->listing_image = $listing_image;
        $this->listing_color = $listing_color;
        $this->listing_price = $listing_price;
        $this->listing_description = $listing_description;
        $this->mime_type = $mime_type;
    }
}

// CONTROL LAYER: Manages data retrieval and deletion
class ListingController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getListingDetails($listing_id) {
        $stmt = $this->db->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $row = $result->fetch_assoc();
        $imageData = null;
        $mimeType = null;

        if (!empty($row['listing_image'])) {
            if ($this->is_blob($row['listing_image'])) {
                $mimeType = 'image/jpeg';
                $imageData = base64_encode($row['listing_image']);
            } else {
                $imageData = htmlspecialchars($row['listing_image']);
                $mimeType = $this->getMimeType($imageData);
            }
        }

        return new CarListing(
            $row['listing_id'],
            $row['manufacturer_name'],
            $row['model_name'],
            $row['model_year'],
            $imageData,
            $row['listing_color'],
            $row['listing_price'],
            $row['listing_description'],
            $mimeType
        );
    }

    public function deleteListing($listing_id) {
        $stmt = $this->db->prepare("DELETE FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        return $stmt->execute();
    }

    private function is_blob($image) {
        return is_string($image) && strlen($image) > 0;
    }

    private function getMimeType($filePath) {
        if (file_exists($filePath)) {
            $mimeType = mime_content_type($filePath);
            if ($mimeType) {
                return $mimeType;
            }
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return 'application/octet-stream';
        }
    }
}

// MAIN LOGIC: Handle deletion and display confirmation
$db = new Database();
$controller = new ListingController($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $listing_id = $_POST['listing_id'];

    if ($controller->deleteListing($listing_id)) {
        header("Location: agent_view_listings.php?message=Listing deleted successfully");
    } else {
        echo "Failed to delete listing.";
    }
    exit();
}

$listing_id = $_GET['listing_id'];
$listing = $controller->getListingDetails($listing_id);

if ($listing === null) {
    echo "Listing not found.";
    exit();
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Listing</title>
    <style>
        .details-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .details-container img { max-width: 100%; height: auto; border: 2px solid #ccc; border-radius: 5px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
        .details-container h2 { text-align: center; }
        .details-container table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-container th, .details-container td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .details-container th { background-color: #f2f2f2; }
        .button-container { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="details-container">
        <h2>Are you sure you want to delete this listing?</h2>                
        <table>
            <tr>
                <th>Image</th>
                <td>
                    <?php if (!empty($listing->listing_image)): ?>
                        <img src="<?php echo (strpos($listing->listing_image, 'data:') === 0) ? $listing->listing_image : 'data:' . $listing->mime_type . ';base64,' . $listing->listing_image; ?>" alt="Car Picture" />
                    <?php else: ?>
                        <p>No image available.</p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr><th>Manufacturer</th><td><?php echo htmlspecialchars($listing->manufacturer_name); ?></td></tr>
            <tr><th>Model</th><td><?php echo htmlspecialchars($listing->model_name); ?></td></tr>
            <tr><th>Year</th><td><?php echo htmlspecialchars($listing->model_year); ?></td></tr>
            <tr><th>Color</th><td><?php echo htmlspecialchars($listing->listing_color); ?></td></tr>
            <tr><th>Price</th><td><?php echo "$" . number_format($listing->listing_price, 2); ?></td></tr>
            <tr><th>Description</th><td><?php echo htmlspecialchars($listing->listing_description); ?></td></tr>
        </table>

        <div class="button-container">
            <form action="" method="post">
                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>" />
                <button type="submit" name="confirm_delete">Delete</button>
                <a href="agent_view_listings.php"><button type="button">Cancel</button></a>
            </form>
        </div>
    </div>
</body>
</html>
