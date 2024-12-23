<?php
// Assuming you have a PDO connection in $pdo
$pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');

// Initial test data with images, excluding Muzan
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
        'profile_image' => loadImageAsBlob('/var/www/html/testdata/profile_images/takumi.jpg'),
        'status_id' => 1,
    ],
    [
        'user_id' => 4,
        'first_name' => 'Bunta',
        'last_name' => 'Fujiwara',
        'about' => 'I want to sell my impreza, looking an agent that seriously values my car!',
        'gender' => 'M',
        'profile_image' => loadImageAsBlob('/var/www/html/testdata/profile_images/bunta.jpg'),
        'status_id' => 1,
    ]
];

// Function to load image as BLOB
function loadImageAsBlob($filePath) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return null;
    }
    $data = file_get_contents($filePath);
    if ($data === false) {
        echo "Failed to read file: $filePath\n";
    }
    return $data;
}
// Add 95 more profiles with NULL profile images
for ($i = 5; $i <= 99; $i++) {
    $testProfiles[] = [
        'user_id' => $i,
        'first_name' => 'User' . $i,
        'last_name' => 'Last' . $i,
        'about' => 'This is user ' . $i . ' profile.',
        'gender' => $i % 2 == 0 ? 'M' : 'F',
        'profile_image' => NULL,
        'status_id' => 1,
    ];
}

// Append Muzan's profile as the last entry
$testProfiles[] = [
    'user_id' => 100,
    'first_name' => 'Muzan',
    'last_name' => 'Kibutsuji',
    'about' => 'Why I got suspended sia, is it because I am a demon?!',
    'gender' => 'M',
    'profile_image' => loadImageAsBlob('/var/www/html/testdata/profile_images/muzan.jpg'),
    'status_id' => 1,
];

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
    $stmt->bindValue(6, $profile['profile_image'], PDO::PARAM_LOB);
    $stmt->bindParam(7, $profile['status_id']);

    $stmt->execute();
}

echo "Test profile data inserted successfully!";
?>
