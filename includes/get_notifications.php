<?php
header('Content-Type: application/json');

$conn = mysqli_connect('localhost', 'root', '', 'quiz_system');
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$notifications = [];

// Get recent quiz results (last 5)
$result = mysqli_query($conn, "
    SELECT r.*, COALESCE(s.fullname, r.student_name) as student_name
    FROM results r
    LEFT JOIN students s ON TRIM(LOWER(r.student_name)) = TRIM(LOWER(s.fullname))
    ORDER BY r.date_taken DESC
    LIMIT 10
");

while ($row = mysqli_fetch_assoc($result)) {
    if ($row['score'] !== null && $row['total'] !== null && $row['total'] > 0) {
        $percentage = round(($row['score'] / $row['total']) * 100, 1);
        $status = $percentage >= 50 ? 'Pass' : 'Fail';
        $type = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'default' : 'warning');
        $studentName = trim($row['student_name'] ?: 'Learner');

        $notifications[] = [
            'title' => 'TEST COMPLETED',
            'activity' => htmlspecialchars($studentName) . ' scored ' . $percentage . '% (' . $status . ')',
            'time' => formatTime($row['date_taken']),
            'actor' => htmlspecialchars($studentName),
            'icon' => 'fas fa-check-circle',
            'type' => $type,
            'url' => 'exams_results.php'
        ];
    }
}

// Get recently added students (last 3)
$result = mysqli_query($conn, "
    SELECT * FROM students
    ORDER BY id DESC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = [
        'title' => 'NEW STUDENT',
        'activity' => htmlspecialchars($row['fullname']) . ' joined ' . htmlspecialchars($row['class'] ?? 'the system'),
        'time' => 'Added recently',
        'actor' => 'System',
        'icon' => 'fas fa-user-plus',
        'type' => 'default',
        'url' => 'manage_students.php'
    ];
}

// Get recently added questions (last 3)
$result = mysqli_query($conn, "
    SELECT * FROM questions
    ORDER BY id DESC
    LIMIT 2
");

while ($row = mysqli_fetch_assoc($result)) {
    $preview = substr(htmlspecialchars($row['question']), 0, 35);
    $notifications[] = [
        'title' => 'NEW QUESTION',
        'activity' => 'For ' . htmlspecialchars($row['class_level']) . ': "' . $preview . '..."',
        'time' => 'Added recently',
        'actor' => 'Admin',
        'icon' => 'fas fa-question-circle',
        'type' => 'default',
        'url' => 'ict_questions.php'
    ];
}

// Remove duplicates and keep the most recent 10
$seen = [];
$unique = [];
foreach ($notifications as $notification) {
    $key = $notification['activity'] . '|' . $notification['title'];
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $notification;
    }
}

$notifications = array_slice($unique, 0, 12);

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'count' => count($notifications)
]);

mysqli_close($conn);

function formatTime($timestamp) {
    $time = strtotime($timestamp);
    if ($time === false) {
        return 'Recently';
    }

    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M d', $time);
}
?>