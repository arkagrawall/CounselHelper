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

$name = $_GET['name'];
$rank = $_GET['rank'];

$sql = "SELECT id FROM csab_users WHERE name = ? AND rank = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $name, $rank);
$stmt->execute();
$stmt->store_result();

$response = [];
if ($stmt->num_rows > 0) {
    $response['exists'] = true;
} else {
    $response['exists'] = false;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
