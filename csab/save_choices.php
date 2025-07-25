<?php
include 'config.php';

$servername = $DB_HOST;
$username   = $DB_USER;
$password   = $DB_PASS;
$dbname     = $DB_NAME;


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$rank = $data['rank'];
$choices = $data['choices'];

// Get user ID
$sql = "SELECT id FROM csab_users WHERE name = ? AND rank = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $name, $rank);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Delete existing choices
$sql = "DELETE FROM csab_choices WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Insert new choices
$sql = "INSERT INTO csab_choices (user_id, choice_order, college, state, branch) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

foreach ($choices as $index => $choice) {
    $choice_order = $index + 1;
    $college = $choice['college'];
    $state = $choice['state'];
    $branch = $choice['branch'];
    $stmt->bind_param("iisss", $user_id, $choice_order, $college, $state, $branch);
    $stmt->execute();
}

$response = ['success' => true];

$stmt->close();
$conn->close();

echo json_encode($response);
?>
