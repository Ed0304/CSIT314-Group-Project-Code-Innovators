<?php
require '../connectDatabase.php';

// ENTITY LAYER: Represents Listing
class CarListing {
    public function getListingDetails($conn, $listing_id) {
        $stmt = $conn->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateListing($conn, $data) {
        // Prepare the SQL statement for listing update
        $stmt = $conn->prepare(
            "UPDATE listing 
             SET manufacturer_name = ?, model_name = ?, model_year = ?, listing_image = ?, listing_color = ?, listing_price = ?, listing_description = ? 
             WHERE listing_id = ?"
        );
        
        // Handle the image upload
        if ($data['listing_image'] !== null) {
            $stmt->bind_param(
                "ssissisi",
                $data['manufacturer_name'],
                $data['model_name'],
                $data['model_year'],
                $data['listing_image'], // BLOB data for image
                $data['listing_color'],
                $data['listing_price'],
                $data['listing_description'],
                $data['listing_id']
            );
        } else {
            // Exclude the image if not provided
            $stmt = $conn->prepare(
                "UPDATE listing 
                 SET manufacturer_name = ?, model_name = ?, model_year = ?, listing_color = ?, listing_price = ?, listing_description = ? 
                 WHERE listing_id = ?"
            );
    
            // Bind parameters excluding the image
            $stmt->bind_param(
                "ssisisi",
                $data['manufacturer_name'],
                $data['model_name'],
                $data['model_year'],
                $data['listing_color'],
                $data['listing_price'],
                $data['listing_description'],
                $data['listing_id']
            );
        }
        
        if (!$stmt->execute()) {
            error_log("Listing update failed: " . $stmt->error);
            return false;
        }

        return true;
    }
}

// CONTROL LAYER: Handles listing updates
class UpdateCarListingController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest($conn, $listing_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            // Assuming user_id is passed from the form and is not being set from session
            $data['user_id'] = $_POST['user_id']; 

            if ($_FILES['listing_image']['error'] === UPLOAD_ERR_OK) {
                $data['listing_image'] = file_get_contents($_FILES['listing_image']['tmp_name']);
            } else {
                $data['listing_image'] = null;
            }
            $this->model->updateListing($conn, $data);
            header("Location: agent_view_listings.php");
            exit();
        }
        return $this->model->getListingDetails($conn, $listing_id);
    }
}

// VIEW LAYER: Displays the form
class UpdateCarListingPage {
    private $listing;

    public function __construct($listing) {
        $this->listing = $listing;
    }

    public function render() {
        ?>
        <!DOCTYPE html>
<html>
<head>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        h1 {
            font-size: 36px;
            text-align: center;
            color: #555;
            margin-top: 20px;
        }
        
        /* Form Container */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Form Elements */
        label {
            font-size: 18px;
            color: #555;
        }
        input, textarea, button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-top: 5px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Buttons */
        button {
            background-color: #4CAF50;
            color: #fff;
            font-size: 18px;
            margin-top: 10px;
            border: none;
            cursor: pointer;
        }
        button[type="button"] {
            background-color: #ccc;
        }
        button:hover {
            background-color: #45a049;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<h1>Update Listing</h1>
<div class="form-container">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="listing_id" value="<?= htmlspecialchars($this->listing['listing_id']); ?>" />
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($this->listing['user_id']); ?>" />

        <label for="manufacturer_name">Manufacturer Name:</label>
        <input type="text" id="manufacturer_name" name="manufacturer_name" value="<?= htmlspecialchars($this->listing['manufacturer_name']); ?>" required />

        <label for="model_name">Model Name:</label>
        <input type="text" id="model_name" name="model_name" value="<?= htmlspecialchars($this->listing['model_name']); ?>" required />

        <label for="model_year">Model Year:</label>
        <input type="number" id="model_year" name="model_year" value="<?= htmlspecialchars($this->listing['model_year']); ?>" required />

        <label for="listing_color">Color:</label>
        <input type="text" id="listing_color" name="listing_color" value="<?= htmlspecialchars($this->listing['listing_color']); ?>" required />

        <label for="listing_price">Price:</label>
        <input type="number" step="0.01" id="listing_price" name="listing_price" value="<?= htmlspecialchars($this->listing['listing_price']); ?>" />

        <label for="listing_description">Description:</label>
        <textarea id="listing_description" name="listing_description" required><?= htmlspecialchars($this->listing['listing_description']); ?></textarea>

        <label for="listing_image">Listing Image:</label>
        <input type="file" id="listing_image" name="listing_image" />

        <div class="button-container">
            <button type="submit">Update Listing</button>
            <a href="agent_view_listings.php">
                <button type="button">Return to listing info</button>
            </a>
        </div>
    </form>
</div>

</body>
</html>


        <?php
    }
}

$listing_id = $_GET['listing_id'];
$model = new CarListing();
$controller = new UpdateCarListingController($model);
$listing = $controller->handleRequest($conn, $listing_id);
$view = new UpdateCarListingPage($listing);
$view->render();
?>
