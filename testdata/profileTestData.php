<?php
// Assuming you have a PDO connection in $pdo
$pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');

// Test data generation
$testProfiles = [
    [
        'user_id' => 1,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'about' => 'I am the only user admin here.',
        'gender' => 'M',
        'profile_image' => NULL,
        'status_id' => 1,
    ],
    [
        'user_id' => 2,
        'first_name' => 'Alice',
        'last_name' => 'Tan',
        'about' => 'A',
        'gender' => 'F',
        'profile_image' => NULL,
        'status_id' => 1,
    ],
    [
        'user_id' => 3,
        'first_name' => 'Takumi',
        'last_name' => 'Fujiwara',
        'about' => 'I am looking for a replacement car for my AE86!',
        'gender' => 'M',
        'profile_image' => loadImageAsBlob('profile_images\takumi.jpg'),
        'status_id' => 1,
    ],
    [
        'user_id' => 4,
        'first_name' => 'Bunta',
        'last_name' => 'Fujiwara',
        'about' => 'I want to sell my impreza, looking an agent that seriously values my car!',
        'gender' => 'M',
        'profile_image' => loadImageAsBlob('profile_images\bunta.jpg'),
        'status_id' => 1,
    ],
    [
        'user_id' => 5,
        'first_name' => 'Muzan',
        'last_name' => 'Kibutsuji',
        'about' => 'Why I got suspended sia, is it because I am a demon?!',
        'gender' => 'M',
        'profile_image' => loadImageAsBlob('profile_images\muzan.jpg'),
        'status_id' => 1,
    ]
];

// Function to load image as BLOB
function loadImageAsBlob($filePath) {
    return file_get_contents($filePath);
}

// Insert test data into the database
$sql = "INSERT INTO profile (user_id, first_name, last_name, about, gender, profile_image, status_id)
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

foreach ($testProfiles as $profile) {
    $stmt->bindParam(1, $profile['user_id']);
    $stmt->bindParam(2, $profile['first_name']);
    $stmt->bindParam(3, $profile['last_name']);
    $stmt->bindParam(4, $profile['about']);
    $stmt->bindParam(5, $profile['gender']);
    $stmt->bindParam(6, $profile['profile_image'], PDO::PARAM_LOB);
    $stmt->bindParam(7, $profile['status_id']);

    $stmt->execute();
}

echo "Test profile data inserted successfully!";
?>
