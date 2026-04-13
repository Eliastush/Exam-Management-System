<?php
// search_global.php - Fixed for your actual database
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "quiz_system");

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$query = isset($data['query']) ? trim($data['query']) : '';

if (strlen($query) < 2) {
    echo json_encode(['status' => 'success', 'results' => []]);
    mysqli_close($conn);
    exit;
}

$search = mysqli_real_escape_string($conn, $query);
$results = [];

// 1. Search Questions
$sql = "SELECT id, question, class_level 
        FROM questions 
        WHERE question LIKE '%$search%' 
        LIMIT 10";
$res = mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $short = substr($row['question'], 0, 80);
        if (strlen($row['question']) > 80) $short .= '...';
        
        $results[] = [
            'title'    => htmlspecialchars($short),
            'subtitle' => 'Class: ' . htmlspecialchars($row['class_level']),
            'type'     => 'Question',
            'icon'     => 'fas fa-question-circle',
            'url'      => "question_view.php?id=" . $row['id']   // Change if your edit/view page has different name
        ];
    }
}

// 2. Search Students
$sql = "SELECT id, fullname, class 
        FROM students 
        WHERE fullname LIKE '%$search%' 
        LIMIT 8";
$res = mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        if (empty($row['fullname'])) continue;
        $results[] = [
            'title'    => htmlspecialchars($row['fullname']),
            'subtitle' => 'Class: ' . htmlspecialchars($row['class']),
            'type'     => 'Student',
            'icon'     => 'fas fa-user-graduate',
            'url'      => "student_profile.php?id=" . $row['id']
        ];
    }
}

// 3. Search Results (by student name)
$sql = "SELECT id, student_name, class_level, score, total 
        FROM results 
        WHERE student_name LIKE '%$search%' 
        LIMIT 8";
$res = mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'title'    => htmlspecialchars($row['student_name']),
            'subtitle' => 'Score: ' . $row['score'] . '/' . $row['total'] . ' | ' . htmlspecialchars($row['class_level']),
            'type'     => 'Result',
            'icon'     => 'fas fa-chart-bar',
            'url'      => "result_view.php?id=" . $row['id']
        ];
    }
}

// Return results
echo json_encode([
    'status'  => 'success',
    'results' => $results
], JSON_UNESCAPED_SLASHES);

mysqli_close($conn);
exit;