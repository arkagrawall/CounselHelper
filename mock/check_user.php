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

$sql = "SELECT * FROM users WHERE name=? AND rank=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $rank);
$stmt->execute();
$result = $stmt->get_result();

$response = array("exists" => false);

if ($result->num_rows > 0) {
    $response["exists"] = true;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>