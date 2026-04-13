<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $query = "INSERT INTO attendance (student_id, date, status, notes)
              VALUES ($student_id, '$date', '$status', '$notes')
              ON DUPLICATE KEY UPDATE
              status='$status', notes='$notes'";

    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
?>