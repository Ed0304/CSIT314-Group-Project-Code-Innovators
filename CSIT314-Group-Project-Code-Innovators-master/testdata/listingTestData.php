<?php
// Assuming you have a PDO connection in $pdo
$pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');

// Function to load image as BLOB
function loadImageAsBlob($filePath) {
    return file_get_contents($filePath);
}

// Directory containing your images
$imageDirectory = 'C:/xampp/htdocs/CSIT314-Project/testdata/car_images';

// Test data generation
$testListings = [
    [
        'manufacturer_name' => 'Toyota',
        'model_name' => 'Camry',
        'model_year' => 2018,
        'listing_image' => loadImageAsBlob($imageDirectory . '/camry.jpg'),
        'listing_color' => 'White',
        'listing_price' => 500000,
        'listing_description' => 'A reliable and comfortable sedan.',
        'user_id' => 2,
    ],
    [
        'manufacturer_name' => 'Nissan',
        'model_name' => 'Fairlady Z',
        'model_year' => 1973,
        'listing_image' => loadImageAsBlob($imageDirectory . '/DEVIL-Z.jpg'),
        'listing_color' => 'Midnight Blue',
        'listing_price' => 400000,
        'listing_description' => 'A classic Japanese sports car from Wangan Midnight Anime. It has a horsepower of 600 HP.',
        'user_id' => 2,
    ],
    [
        'manufacturer_name' => 'Toyota',
        'model_name' => 'Sprinter Trueno GT-Apex',
        'model_year' => 1985,
        'listing_image' => loadImageAsBlob($imageDirectory . '/AE86-TAKUMI.jpg'),
        'listing_color' => 'High Tech Two Tone',
        'listing_price' => 500000,
        'listing_description' => 'A classic Japanese car. All decals are modified to match Takumi Fujiwara\'s iconic vehicle in Initial D Anime.',
        'user_id' => 2,
    ],
    [
        'manufacturer_name' => 'Mazda',
        'model_name' => 'RX-7 (FD3S)',
        'model_year' => 1993,
        'listing_image' => loadImageAsBlob($imageDirectory . '/rx7.jpg'),
        'listing_color' => 'Red',
        'listing_price' => 600000,
        'listing_description' => 'A classic Japanese car. Rotary engine still works fine.',
        'user_id' => 2,
    ],
    [
        'manufacturer_name' => 'Honda',
        'model_name' => 'CR-V',
        'model_year' => 2024,
        'listing_image' => loadImageAsBlob($imageDirectory . '/crv.png'),
        'listing_color' => 'Blue',
        'listing_price' => 300000,
        'listing_description' => 'Everyone\'s favorite MPV! Powerful engine and still in mint condition!',
        'user_id' => 2,
    ],
];

// Insert test data into the database
$sql = "INSERT INTO listing (manufacturer_name, model_name, model_year, listing_image, listing_color, listing_price, listing_description, user_id)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

foreach ($testListings as $listing) {
    $stmt->bindParam(1, $listing['manufacturer_name']);
    $stmt->bindParam(2, $listing['model_name']);
    $stmt->bindParam(3, $listing['model_year']);
    $stmt->bindParam(4, $listing['listing_image'], PDO::PARAM_LOB);
    $stmt->bindParam(5, $listing['listing_color']);
    $stmt->bindParam(6, $listing['listing_price']);
    $stmt->bindParam(7, $listing['listing_description']);
    $stmt->bindParam(8, $listing['user_id']);

    $stmt->execute();
}

echo "Listing test data inserted successfully!";
?>