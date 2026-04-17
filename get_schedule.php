<?php
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM teacher_schedule WHERE id = $id");

    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Schedule not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID parameter required']);
}
?>
