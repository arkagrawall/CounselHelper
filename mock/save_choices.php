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

$sql = "DELETE FROM choices WHERE name=? AND rank=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $rank);
$stmt->execute();
$stmt->close();

$sql = "INSERT INTO choices (name, rank, college, branch) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
foreach ($choices as $choice) {
    $stmt->bind_param("ssss", $name, $rank, $choice['college'], $choice['branch']);
    $stmt->execute();
}

$response = array("success" => true);

$stmt->close();
$conn->close();

echo json_encode($response);
?>
