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

$sql = "SELECT * FROM users WHERE rank=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rank);
$stmt->execute();
$result = $stmt->get_result();

$response = array("success" => false);

if ($result->num_rows > 0) {
    $response["message"] = "User is already registered";
} else {
    $sql = "INSERT INTO users (name, rank) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $rank);
    if ($stmt->execute()) {
        $response["success"] = true;
    } else {
        $response["message"] = "Error registering user";
    }
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>