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

$sql = "SELECT * FROM choices WHERE name=? AND rank=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $rank);
$stmt->execute();
$result = $stmt->get_result();

$choices = array();
$response = array("success" => false, "choices" => $choices);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $choices[] = array("college" => $row['college'], "branch" => $row['branch']);
    }
    $response["success"] = true;
    $response["choices"] = $choices;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
