<?php
include 'includes/header.php';

if (!$conn) {
    die('Database connection failed');
}

function fetch_value(mysqli $conn, string $sql, string $field, $fallback = 0) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return $fallback;
    }
    $row = mysqli_fetch_assoc($result);
    return $row[$field] ?? $fallback;
}

$total_students = (int) fetch_value($conn, 'SELECT COUNT(*) AS total FROM students', 'total', 0);
$total_results = (int) fetch_value($conn, 'SELECT COUNT(*) AS total FROM results', 'total', 0);
$total_questions = (int) fetch_value($conn, 'SELECT COUNT(*) AS total FROM questions', 'total', 0);
$avg_score = round((float) fetch_value($conn, 'SELECT AVG(score / total * 100) AS avg_score FROM results WHERE total > 0', 'avg_score', 0), 1);
$pass_rate = round((float) fetch_value($conn, 'SELECT AVG(CASE WHEN score / total * 100 >= 50 THEN 100 ELSE 0 END) AS pass_rate FROM results WHERE total > 0', 'pass_rate', 0), 1);
$active_students = (int) fetch_value($conn, 'SELECT COUNT(DISTINCT student_name) AS total FROM results', 'total', 0);

$top_class = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT class_level, ROUND(AVG(score / total * 100), 1) AS avg_score
    FROM results
    WHERE total > 0
    GROUP BY class_level
    ORDER BY avg_score DESC
    LIMIT 1
")) ?: ['class_level' => 'N/A', 'avg_score' => 0];

$top_student = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT student_name, class_level, ROUND(AVG(score / total * 100), 1) AS avg_score
    FROM results
    WHERE total > 0
    GROUP BY student_name, class_level
    ORDER BY avg_score DESC
    LIMIT 3
")) ?: ['student_name' => 'N/A', 'class_level' => 'N/A', 'avg_score' => 0];

$recent_results_result = mysqli_query($conn, "
    SELECT COALESCE(s.fullname, r.student_name) AS student_name, r.class_level, r.score, r.total, r.date_taken
    FROM results r
    LEFT JOIN students s ON TRIM(LOWER(s.fullname)) = TRIM(LOWER(r.student_name))
    WHERE r.total > 0
    ORDER BY r.date_taken DESC
    LIMIT 8
");
$recent_results = $recent_results_result ? mysqli_fetch_all($recent_results_result, MYSQLI_ASSOC) : [];

$class_highlights_result = mysqli_query($conn, "
    SELECT class_level,
           COUNT(*) AS attempts,
           ROUND(AVG(score / total * 100), 1) AS avg_score,
           ROUND(SUM(CASE WHEN score / total * 100 >= 50 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) AS pass_rate
    FROM results
    WHERE total > 0
    GROUP BY class_level
    ORDER BY avg_score DESC
    LIMIT 6
");
$class_highlights = $class_highlights_result ? mysqli_fetch_all($class_highlights_result, MYSQLI_ASSOC) : [];

$trend_result = mysqli_query($conn, "
    SELECT DATE_FORMAT(date_taken, '%b %Y') AS month, ROUND(AVG(score / total * 100), 1) AS avg_score
    FROM results
    WHERE total > 0
    GROUP BY DATE_FORMAT(date_taken, '%Y-%m')
    ORDER BY MIN(date_taken) ASC
    LIMIT 12
");
$trend_labels = [];
$trend_scores = [];
if ($trend_result) {
    while ($row = mysqli_fetch_assoc($trend_result)) {
        $trend_labels[] = $row['month'];
        $trend_scores[] = (float) $row['avg_score'];
    }
}

$distribution = [0, 0, 0, 0];
$distribution_result = mysqli_query($conn, 'SELECT score, total FROM results WHERE total > 0');
if ($distribution_result) {
    while ($row = mysqli_fetch_assoc($distribution_result)) {
        $percent = ($row['score'] / $row['total']) * 100;
        if ($percent >= 80) {
            $distribution[0]++;
        } elseif ($percent >= 60) {
            $distribution[1]++;
        } elseif ($percent >= 40) {
            $distribution[2]++;
        } else {
            $distribution[3]++;
        }
    }
}

$class_chart_labels = [];
$class_chart_values = [];
foreach ($class_highlights as $class_row) {
    $class_chart_labels[] = $class_row['class_level'];
    $class_chart_values[] = (float) $class_row['avg_score'];
}

$question_breakdown_result = mysqli_query($conn, "
    SELECT class_level, COUNT(*) AS total
    FROM questions
    GROUP BY class_level
    ORDER BY class_level ASC
");
$question_labels = [];
$question_values = [];
if ($question_breakdown_result) {
    while ($row = mysqli_fetch_assoc($question_breakdown_result)) {
        $question_labels[] = $row['class_level'];
        $question_values[] = (int) $row['total'];
    }
}

$focus_tip = 'Keep learner activity steady by scheduling one short quiz per class each week.';
if ($pass_rate < 50) {
    $focus_tip = 'Pass rate is below target. Prioritize revision drills and extra support for classes under 50%.';
} elseif ($pass_rate < 70) {
    $focus_tip = 'Pass rate is improving. Small-group coaching for learners in the 40% to 60% range can lift results quickly.';
} elseif ($total_questions < 50) {
    $focus_tip = 'Performance is healthy. Expanding the question bank will keep assessments fresh and more balanced.';
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-chart-line"></i> ICT Dashboard <span class="beta-badge">System Pulse</span></h1>
                <p class="page-subtitle">A cleaner overview of participation, performance, and what needs attention next.</p>
            </div>
            <div class="header-actions">
                <a href="reports.php" class="btn btn-secondary"><i class="fas fa-file-lines"></i> Reports</a>
                <a href="exams_results.php" class="btn btn-primary"><i class="fas fa-arrow-up-right-from-square"></i> Open Results</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card stat-card">
                <i class="fas fa-users fa-2x"></i>
                <div class="stat-value"><?= number_format($total_students) ?></div>
                <div class="stat-label">Registered Students</div>
            </div>
            <div class="card stat-card">
                <i class="fas fa-clipboard-check fa-2x"></i>
                <div class="stat-value"><?= number_format($total_results) ?></div>
                <div class="stat-label">Quiz Attempts</div>
            </div>
            <div class="card stat-card">
                <i class="fas fa-percent fa-2x"></i>
                <div class="stat-value"><?= number_format($avg_score, 1) ?>%</div>
                <div class="stat-label">Average Score</div>
            </div>
            <div class="card stat-card">
                <i class="fas fa-square-poll-vertical fa-2x"></i>
                <div class="stat-value"><?= number_format($pass_rate, 1) ?>%</div>
                <div class="stat-label">Pass Rate</div>
            </div>
        </div>

        <div class="charts-grid space-lg">
            <div class="card chart-card">
                <h3><i class="fas fa-wave-square"></i> Monthly Trend</h3>
                <div class="chart-shell"><canvas id="trendChart"></canvas></div>
            </div>
            <div class="card chart-card">
                <h3><i class="fas fa-chart-pie"></i> Score Distribution</h3>
                <div class="chart-shell"><canvas id="distributionChart"></canvas></div>
            </div>
            <div class="card chart-card">
                <h3><i class="fas fa-school"></i> Class Performance</h3>
                <div class="chart-shell"><canvas id="classChart"></canvas></div>
            </div>
            <div class="card chart-card">
                <h3><i class="fas fa-book-open"></i> Question Coverage</h3>
                <div class="chart-shell"><canvas id="questionChart"></canvas></div>
            </div>
        </div>

        <div class="learner-grid space-lg">
            <div class="card summary-card">
                <h3><i class="fas fa-trophy"></i> Best Performing Learner</h3>
                <div class="stat-value"><?= htmlspecialchars($top_student['student_name']) ?></div>
                <p><?= htmlspecialchars($top_student['class_level']) ?> averaging <?= number_format((float) $top_student['avg_score'], 1) ?>%</p>
                <a href="manage_students.php" class="btn btn-secondary btn-sm">Review student list</a>
            </div>
            <div class="card summary-card">
                <h3><i class="fas fa-building-columns"></i> Strongest Class</h3>
                <div class="stat-value"><?= htmlspecialchars($top_class['class_level']) ?></div>
                <p>Class average is currently <?= number_format((float) $top_class['avg_score'], 1) ?>%.</p>
                <a href="reports.php" class="btn btn-secondary btn-sm">Open class reports</a>
            </div>
            <div class="card insight-card">
                <h3><i class="fas fa-lightbulb"></i> Recommended Focus</h3>
                <p><?= htmlspecialchars($focus_tip) ?></p>
                <div class="card-section">
                    <div class="stat-label">Active learners</div>
                    <div class="stat-value"><?= number_format($active_students) ?></div>
                </div>
                <div class="stat-label">Questions in bank: <?= number_format($total_questions) ?></div>
            </div>
        </div>

        <div class="learner-grid space-lg">
            <div class="card">
                <h3><i class="fas fa-history"></i> Recent Quiz Results</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Score</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_results): ?>
                                <?php foreach ($recent_results as $row): ?>
                                    <?php $percent = round(($row['score'] / $row['total']) * 100, 1); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td><span class="class-badge"><?= htmlspecialchars($row['class_level']) ?></span></td>
                                        <td><span class="score-badge <?= $percent >= 50 ? 'pass' : 'fail' ?>"><?= $percent ?>%</span></td>
                                        <td><?= date('d M Y', strtotime($row['date_taken'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No quiz data is available yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-medal"></i> Class Highlights</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Attempts</th>
                                <th>Average</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($class_highlights): ?>
                                <?php foreach ($class_highlights as $row): ?>
                                    <tr>
                                        <td><span class="class-badge"><?= htmlspecialchars($row['class_level']) ?></span></td>
                                        <td><?= number_format((int) $row['attempts']) ?></td>
                                        <td><?= number_format((float) $row['avg_score'], 1) ?>%</td>
                                        <td><?= number_format((float) $row['pass_rate'], 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No class performance data available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const rootStyles = getComputedStyle(document.documentElement);
const primaryColor = rootStyles.getPropertyValue('--primary-color').trim() || '#1f8f63';
const primaryRgb = rootStyles.getPropertyValue('--primary-rgb').trim() || '31, 143, 99';
const primarySoft = `rgba(${primaryRgb}, 0.14)`;
const chartOptionsBase = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 650
    }
};

const trendChartEl = document.getElementById('trendChart');
if (trendChartEl) {
    new Chart(trendChartEl, {
        type: 'line',
        data: {
            labels: <?= json_encode($trend_labels) ?>,
            datasets: [{
                label: 'Average score',
                data: <?= json_encode($trend_scores) ?>,
                borderColor: primaryColor,
                backgroundColor: primarySoft,
                fill: true,
                tension: 0.35
            }]
        },
        options: chartOptionsBase
    });
}

const distributionChartEl = document.getElementById('distributionChart');
if (distributionChartEl) {
    new Chart(distributionChartEl, {
        type: 'doughnut',
        data: {
            labels: ['80% and above', '60% to 79%', '40% to 59%', 'Below 40%'],
            datasets: [{ data: <?= json_encode($distribution) ?>, backgroundColor: ['#16a34a', '#2563eb', '#d97706', '#dc2626'] }]
        },
        options: chartOptionsBase
    });
}

const classChartEl = document.getElementById('classChart');
if (classChartEl) {
    new Chart(classChartEl, {
        type: 'bar',
        data: {
            labels: <?= json_encode($class_chart_labels) ?>,
            datasets: [{ label: 'Average %', data: <?= json_encode($class_chart_values) ?>, backgroundColor: primaryColor }]
        },
        options: {
            ...chartOptionsBase,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });
}

const questionChartEl = document.getElementById('questionChart');
if (questionChartEl) {
    new Chart(questionChartEl, {
        type: 'bar',
        data: {
            labels: <?= json_encode($question_labels) ?>,
            datasets: [{ label: 'Questions', data: <?= json_encode($question_values) ?>, backgroundColor: '#2563eb' }]
        },
        options: {
            ...chartOptionsBase,
            plugins: { legend: { display: false } }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
