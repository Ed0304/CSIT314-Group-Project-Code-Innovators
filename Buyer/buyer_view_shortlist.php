<?php
session_start();
require_once "../connectDatabase.php";

class Shortlist
{
    private $conn;

    public $shortlist_id;
    public $listing_id;
    public $user_id;
    public $date_added;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_color;
    public $listing_price;
    public $listing_description;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Fetch all shortlists for a given user
    public function getShortlistsByUser($user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT s.shortlist_id, s.listing_id, l.user_id AS user_id, s.shortlist_date AS date_added, 
                   l.manufacturer_name, l.model_name, l.model_year, l.listing_color, l.listing_price, l.listing_description
            FROM shortlist s
            JOIN listing l ON s.listing_id = l.listing_id
            WHERE s.buyer_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $shortlists = [];
        while ($row = $result->fetch_assoc()) {
            $shortlists[] = $row; // Return data as associative array
        }
        return $shortlists;
    }
}

class ViewShortlistController
{
    private $shortlist;

    public function __construct($shortlist)
    {
        $this->shortlist = $shortlist;
    }

    public function getBuyerID()
    {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    public function getShortlists()
    {
        $buyerID = $this->getBuyerID();
        return $buyerID !== null ? $this->shortlist->getShortlistsByUser($buyerID) : [];
    }
}

class ViewShortlistPage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function ViewShortlistUI()
    {
        $shortlists = $this->controller->getShortlists();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Your Shortlisted Cars</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                }
                header {
                    padding: 20px;
                    background-color: #343a40;
                    color: #ffffff;
                }
                header h1 {
                    margin: 0;
                    font-size: 1.5em;
                }
                h2 {
                    text-align: center;
                    color: #343a40;
                    margin-top: 20px;
                }
                table {
                    width: 90%;
                    margin: 20px auto;
                    border-collapse: collapse;
                    background-color: #ffffff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                table, th, td {
                    border: 1px solid #dee2e6;
                }
                th, td {
                    padding: 12px;
                    text-align: center;
                    color: #343a40;
                }
                th {
                    background-color: #6c757d;
                    color: #ffffff;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f1f1f1;
                }
                .return-button, .search-button, .remove-button, .agent-button, .listing-button {
                    display: inline-block;
                    margin: 5px;
                    padding: 10px 15px;
                    font-size: 14px;
                    border-radius: 5px;
                    color: white;
                    text-decoration: none;
                }
                .return-button { background-color: #007bff; }
                .return-button:hover { background-color: #0056b3; }
                .search-button { background-color: #007bff; }
                .search-button:hover { background-color: #0056b3; }
                .remove-button { background-color: red; }
                .remove-button:hover { background-color: darkred; }
                .agent-button { background-color: green; }
                .agent-button:hover { background-color: darkgreen; }
                .listing-button { background-color: green; }
                .listing-button:hover { background-color: darkgreen; }
            </style>
        </head>
        <body>
            <header>
                <h1>Your Shortlisted Cars</h1>
            </header>
            <h2>Shortlisted Cars</h2>
            <!-- Search Form -->
            <form method="POST" action="buyer_search_shortlist.php" style="text-align: center;font-size: 18px;">
                <label for="vehicle" style="text-align: center;font-size: 18px;">Search based on:</label>
                <select id="vehicle" name="role" style="text-align: center;font-size: 18px;">
                    <option value="manufacturer_name">Manufacturer</option>
                    <option value="model_name">Model</option>
                    <option value="model_year">Year</option>
                    <option value="listing_color">Color</option>
                    <option value="listing_price">Price</option>
                </select>
                <input type="text" id="search" name="search" placeholder="Enter Text Here" style="text-align: center;font-size: 18px;" />
                <button class="search-button" type="submit" name="searchButton">Search</button>
            </form>
            <table>
                <tr>
                    <th>Manufacturer</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Color</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($shortlists as $shortlist): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shortlist['manufacturer_name']); ?></td>
                        <td><?php echo htmlspecialchars($shortlist['model_name']); ?></td>
                        <td><?php echo htmlspecialchars($shortlist['model_year']); ?></td>
                        <td><?php echo htmlspecialchars($shortlist['listing_color']); ?></td>
                        <td><?php echo htmlspecialchars($shortlist['listing_price']); ?></td>
                        <td><?php echo htmlspecialchars($shortlist['listing_description']); ?></td>
                        <td><?php echo htmlspecialchars($shortlist['date_added']); ?></td>
                        <td>
                            <form action="buyerListingDetails.php" method="post" style="display:inline;">
                                <input type="hidden" name="listing_id" value="<?php echo $shortlist['listing_id']; ?>">
                                <input type="hidden" name="referrer" value="shortlist">
                                <button type="submit" class="listing-button">View Details</button>
                            </form>
                            <a href="buyer_view_agent_details.php?user_id=<?php echo $shortlist['user_id']; ?>&referrer=shortlist" class="agent-button">View Agent</a>
                            <form method="get" action="buyer_delete_shortlist.php" style="display:inline;">
                                <input type="hidden" name="shortlist_id" value="<?php echo $shortlist['shortlist_id']; ?>">
                                <button type="submit" class="remove-button">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <a href="buyer_dashboard.php" class="return-button">Return to Main Page</a>
        </body>
        </html>
        <?php
    }
}

// Main script
$database = new Database();
$conn = $database->getConnection();

$shortlistEntity = new Shortlist($conn);
$controller = new ViewShortlistController($shortlistEntity);
$page = new ViewShortlistPage($controller);
$page->ViewShortlistUI();

$database->closeConnection();
?>
