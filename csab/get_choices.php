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

$sql = "SELECT u.id, c.choice_order, c.college, c.state, c.branch FROM csab_users u 
        JOIN csab_choices c ON u.id = c.user_id 
        WHERE u.name = ? AND u.rank = ? ORDER BY c.choice_order";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $name, $rank);
$stmt->execute();
$result = $stmt->get_result();

$choices = [];
while ($row = $result->fetch_assoc()) {
    $choices[] = $row;
}

$response = ['success' => true, 'choices' => $choices];

$stmt->close();
$conn->close();

echo json_encode($response);
?>
