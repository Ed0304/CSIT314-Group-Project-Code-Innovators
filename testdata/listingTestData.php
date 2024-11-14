<?php
// Assuming you have a PDO connection in $pdo
$pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');

// Function to load image as BLOB
function loadImageAsBlob($filePath) {
    return file_get_contents($filePath);
}

// Directory containing your images
$imageDirectory = '/var/www/html/testdata/car_images';

// Update the file paths to use forward slashes
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
        'views' => 0,
        'shortlisted' => 0,
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
        'views' => 0,
        'shortlisted' => 0,
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
        'views' => 0,
        'shortlisted' => 0,
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
        'views' => 0,
        'shortlisted' => 0,
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
        'views' => 0,
        'shortlisted' => 0,
    ]
];

// Add 95 more listings with the correct file path
for ($i = 6; $i <= 100; $i++) {
    if ($i % 2 == 0 && $i % 4 != 0) {
        for ($j = 0; $j < 4; $j++) {
            $testListings[] = [
                'manufacturer_name' => 'Manufacturer' . $i,
                'model_name' => 'Model' . ($i + $j),
                'model_year' => 2000 + (($i + $j) % 10),
                'listing_image' => loadImageAsBlob($imageDirectory . '/car' . ($i + $j) . '.jpg'),
                'listing_color' => 'Color' . ($i * 4 + $j),
                'listing_price' => rand(100000, 1000000),
                'listing_description' => 'This is a description for car ' . ($i + $j) . '.',
                'user_id' => $i,
                'views' => 0,
                'shortlisted' => 0,
            ];
        }
    }
}


echo "Listing test data inserted successfully!";
?>
