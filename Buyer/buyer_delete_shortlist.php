<?php
require_once '../connectDatabase.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['shortlist_id']) && !isset($_POST['shortlist_id'])) {
    echo "Shortlist ID not provided!";
    exit();
}

// ENTITY LAYER: Manages database interactions
class Shortlist
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getShortlistDetails($shortlist_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.shortlist_id, 
                l.manufacturer_name, 
                l.model_name, 
                l.model_year, 
                l.listing_color, 
                l.listing_price, 
                l.listing_description, 
                l.listing_image 
            FROM 
                shortlist s 
            JOIN 
                listing l 
            ON 
                s.listing_id = l.listing_id 
            WHERE 
                s.shortlist_id = ?
        ");
        $stmt->bind_param("i", $shortlist_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        return (object) [
            'shortlist_id' => $row['shortlist_id'],
            'manufacturer_name' => $row['manufacturer_name'],
            'model_name' => $row['model_name'],
            'model_year' => $row['model_year'],
            'listing_color' => $row['listing_color'],
            'listing_price' => $row['listing_price'],
            'listing_description' => $row['listing_description'],
            'listing_image' => $this->getImageData($row['listing_image']),
            'mime_type' => $this->getMimeType($row['listing_image'])
        ];
    }

    public function deleteShortlist($shortlist_id, $user_id)
    {
        $stmt = $this->db->prepare("DELETE FROM shortlist WHERE shortlist_id = ? AND buyer_id = ?");
        $stmt->bind_param("ii", $shortlist_id, $user_id);
        return $stmt->execute();
    }

    private function getImageData($image)
    {
        return $image ? base64_encode($image) : null;
    }

    private function getMimeType($image)
    {
        return $image ? 'image/jpeg' : 'application/octet-stream';
    }
}

// CONTROLLER LAYER: Interacts with the Entity layer
class DeleteShortlistController
{
    private $shortlist;

    public function __construct($shortlist)
    {
        $this->shortlist = $shortlist;
    }

    public function getShortlistDetails($shortlist_id)
    {
        return $this->shortlist->getShortlistDetails($shortlist_id);
    }

    public function deleteShortlist($shortlist_id, $user_id)
    {
        return $this->shortlist->deleteShortlist($shortlist_id, $user_id);
    }
}

// BOUNDARY LAYER: Manages requests and renders the confirmation page
class DeleteShortlistPage
{
    private $controller;
    private $shortlist;

    // Constructor initializes the boundary with a controller
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    // Main request handler
    public function handleRequest()
    {
        $shortlist_id = $_GET['shortlist_id'] ?? $_POST['shortlist_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;

        // Check for POST request to handle delete confirmation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            if ($user_id && $this->controller->deleteShortlist($shortlist_id, $user_id)) {
                header("Location: buyer_view_shortlist.php?message=Shortlist item deleted successfully");
                exit();
            } else {
                echo "Failed to delete listing.";
            }
        }

        // Retrieve shortlist details to display for confirmation
        $this->shortlist = $this->controller->getShortlistDetails($shortlist_id);
        if ($this->shortlist === null) {
            echo "Shortlist not found.";
            exit();
        }

        // Render the confirmation page
        $this->DeleteShortlistUI();
    }

    // Render the confirmation HTML page with listing details
    public function DeleteShortlistUI()
    {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Delete Listing</title>
            <style>
                .details-container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .details-container img {
                    max-width: 100%;
                    height: auto;
                    border: 2px solid #ccc;
                    border-radius: 5px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                }
                .details-container h2 {
                    text-align: center;
                }
                .details-container table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .details-container th, .details-container td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    text-align: left;
                }
                .details-container th {
                    background-color: #f2f2f2;
                }
                .button-container {
                    text-align: center;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="details-container">
                <h2>Are you sure you want to delete this listing?</h2>
                <table>
                    <tr>
                        <th>Image</th>
                        <td>
                            <?php if (!empty($this->shortlist->listing_image)): ?>
                                <img src="<?php echo 'data:' . $this->shortlist->mime_type . ';base64,' . $this->shortlist->listing_image; ?>" alt="Car Picture" />
                            <?php else: ?>
                                <p>No image available.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Manufacturer</th>
                        <td><?php echo htmlspecialchars($this->shortlist->manufacturer_name); ?></td>
                    </tr>
                    <tr>
                        <th>Model</th>
                        <td><?php echo htmlspecialchars($this->shortlist->model_name); ?></td>
                    </tr>
                    <tr>
                        <th>Year</th>
                        <td><?php echo htmlspecialchars($this->shortlist->model_year); ?></td>
                    </tr>
                    <tr>
                        <th>Color</th>
                        <td><?php echo htmlspecialchars($this->shortlist->listing_color); ?></td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td><?php echo "$" . number_format($this->shortlist->listing_price, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?php echo htmlspecialchars($this->shortlist->listing_description); ?></td>
                    </tr>
                </table>
                <div class="button-container">
                    <form action="" method="post">
                        <input type="hidden" name="shortlist_id" value="<?php echo $this->shortlist->shortlist_id; ?>" />
                        <button type="submit" name="confirm_delete">Delete</button>
                        <a href="buyer_view_shortlist.php"><button type="button">Cancel</button></a>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

// MAIN LOGIC
$db = new Database();
$shortlist = new Shortlist($db->getConnection());
$controller = new DeleteShortlistController($shortlist);
$boundary = new DeleteShortlistPage($controller);

$boundary->handleRequest();

$db->closeConnection();
?>
