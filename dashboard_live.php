<?php
// dashboard_live.php - Live Data for Auto Update
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) {
    echo json_encode(['status' => 'error']);
    exit;
}

// Online Students (Simple method: students who took a quiz in last 10 minutes)
$online_res = mysqli_query($conn, "SELECT COUNT(DISTINCT student_name) as online 
                                  FROM results 
                                  WHERE date_taken >= NOW() - INTERVAL 10 MINUTE");
$online_students = mysqli_fetch_assoc($online_res)['online'] ?? 0;

// KPIs
$total_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM results"))['c'] ?? 0;
$avg_score     = round(mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score/total*100) as a FROM results WHERE total>0"))['a'] ?? 0, 1);
$pass_rate     = round(mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(CASE WHEN score/total*100 >=50 THEN 100 ELSE 0 END) as p FROM results WHERE total>0"))['p'] ?? 0, 1);

$best_class = mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_level FROM results GROUP BY class_level ORDER BY AVG(score/total*100) DESC LIMIT 1"))['class_level'] ?? 'N/A';

// Recent Results (last 10)
$recent = [];
$res = mysqli_query($conn, "SELECT r.student_name, r.class_level, r.score, r.total, r.date_taken 
                           FROM results r 
                           ORDER BY r.date_taken DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($res)) {
    $recent[] = [
        'student_name' => htmlspecialchars($row['student_name']),
        'class_level'  => htmlspecialchars($row['class_level']),
        'percentage'   => round(($row['score'] / $row['total']) * 100, 1),
        'date'         => date('d M Y H:i', strtotime($row['date_taken']))
    ];
}

// Charts Data
$trend_labels = []; $trend_data = [];
$trend_res = mysqli_query($conn, "SELECT DATE_FORMAT(date_taken,'%b %d') as month, ROUND(AVG(score/total*100),1) as avg 
                                 FROM results GROUP BY DATE_FORMAT(date_taken,'%Y-%m-%d') ORDER BY date_taken ASC LIMIT 12");
while ($r = mysqli_fetch_assoc($trend_res)) {
    $trend_labels[] = $r['month'];
    $trend_data[] = $r['avg'];
}

$distribution_data = [0,0,0,0];
$res = mysqli_query($conn, "SELECT score, total FROM results WHERE total > 0");
while ($row = mysqli_fetch_assoc($res)) {
    $perc = ($row['score'] / $row['total']) * 100;
    if ($perc >= 80) $distribution_data[0]++;
    elseif ($perc >= 60) $distribution_data[1]++;
    elseif ($perc >= 40) $distribution_data[2]++;
    else $distribution_data[3]++;
}

$class_labels = []; $class_avg = [];
$res = mysqli_query($conn, "SELECT class_level, ROUND(AVG(score/total*100),1) as avg 
                           FROM results GROUP BY class_level");
while ($r = mysqli_fetch_assoc($res)) {
    $class_labels[] = $r['class_level'];
    $class_avg[] = $r['avg'];
}

$questions_labels = []; $questions_count = [];
$res = mysqli_query($conn, "SELECT class_level, COUNT(*) as total FROM questions GROUP BY class_level");
while ($r = mysqli_fetch_assoc($res)) {
    $questions_labels[] = $r['class_level'];
    $questions_count[] = $r['total'];
}

echo json_encode([
    'status'            => 'success',
    'total_quizzes'     => $total_quizzes,
    'avg_score'         => $avg_score,
    'pass_rate'         => $pass_rate,
    'online_students'   => $online_students,
    'best_class'        => $best_class,
    'recent_results'    => $recent,
    'trend_labels'      => $trend_labels,
    'trend_data'        => $trend_data,
    'distribution_data' => $distribution_data,
    'class_labels'      => $class_labels,
    'class_avg'         => $class_avg,
    'questions_labels'  => $questions_labels,
    'questions_count'   => $questions_count
]);

mysqli_close($conn);
exit;