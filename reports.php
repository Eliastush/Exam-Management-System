<?php
include 'Includes/header.php';

$conn = mysqli_connect('localhost', 'root', '', 'quiz_system');
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

$errors = [];

function safeQuery($conn, $sql, &$errors, $label) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        $errors[] = "$label failed: " . mysqli_error($conn);
    }
    return $result;
}

function fetchScalar($conn, $sql, &$errors, $label, $field = 'c') {
    $result = safeQuery($conn, $sql, $errors, $label);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row[$field] ?? 0;
    }
    return 0;
}

$settings_res = safeQuery($conn, "SELECT * FROM settings", $errors, 'Load settings');
$settings = [];
if ($settings_res) {
    while ($row = mysqli_fetch_assoc($settings_res)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$school_name = $settings['school_name'] ?? 'ICT Quiz System';
$site_logo = $settings['site_logo'] ?? '';

$total_students = fetchScalar($conn, "SELECT COUNT(*) as c FROM students", $errors, 'Student count');
$total_questions = fetchScalar($conn, "SELECT COUNT(*) as c FROM questions", $errors, 'Question count');
$total_attempts = fetchScalar($conn, "SELECT COUNT(*) as c FROM results", $errors, 'Attempt count');
$active_students = fetchScalar($conn, "SELECT COUNT(DISTINCT student_name) as c FROM results", $errors, 'Active student count');
$overall_avg = round(fetchScalar($conn, "SELECT AVG(score/total*100) as a FROM results WHERE total>0", $errors, 'Average score', 'a'), 1);
$pass_rate = round(fetchScalar($conn, "SELECT AVG(CASE WHEN score/total*100 >= 50 THEN 100 ELSE 0 END) as p FROM results WHERE total>0", $errors, 'Pass rate', 'p'), 1);
$highest_score = round(fetchScalar($conn, "SELECT MAX(score/total*100) as h FROM results WHERE total>0", $errors, 'Highest score', 'h'), 1);
$lowest_score = round(fetchScalar($conn, "SELECT MIN(score/total*100) as l FROM results WHERE total>0", $errors, 'Lowest score', 'l'), 1);
$avg_attempt_per_student = $total_students > 0 ? round($total_attempts / $total_students, 1) : 0;

$median_score = 0;
$scores_res = safeQuery($conn, "SELECT score/total*100 as s FROM results WHERE total>0 ORDER BY s", $errors, 'Median score');
if ($scores_res) {
    $scores = [];
    while ($row = mysqli_fetch_assoc($scores_res)) {
        $scores[] = $row['s'];
    }
    if (count($scores) > 0) {
        $median_score = $scores[intval(count($scores) / 2)];
    }
}

$top_performers = safeQuery($conn, "
    SELECT student_name, class_level AS class,
           ROUND(AVG(score/total*100),1) as avg_score,
           MAX(score/total*100) as best_score,
           MIN(score/total*100) as worst_score,
           COUNT(*) as attempts
    FROM results
    WHERE total > 0
    GROUP BY student_name, class_level
    ORDER BY avg_score DESC
    LIMIT 15
", $errors, 'Top performers');

$weak_students = safeQuery($conn, "
    SELECT student_name, class_level AS class,
           ROUND(AVG(score/total*100),1) as avg_score,
           COUNT(*) as attempts
    FROM results
    WHERE total > 0
    GROUP BY student_name, class_level
    ORDER BY avg_score ASC
    LIMIT 10
", $errors, 'Lowest performers');

$class_report = safeQuery($conn, "
    SELECT class_level AS class,
           COUNT(DISTINCT student_name) as students,
           COUNT(*) as attempts,
           ROUND(AVG(score/total*100),1) as avg_score,
           ROUND(MAX(score/total*100),1) as best_score,
           ROUND(MIN(score/total*100),1) as lowest_score,
           ROUND(SUM(CASE WHEN score/total*100 >= 50 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as pass_rate
    FROM results
    WHERE total > 0
    GROUP BY class_level
    ORDER BY avg_score DESC
", $errors, 'Class performance');

$strongest_class = mysqli_fetch_assoc(safeQuery($conn, "
    SELECT class_level AS class,
           ROUND(AVG(score/total*100),1) as avg_score,
           ROUND(SUM(CASE WHEN score/total*100 >= 50 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as pass_rate
    FROM results
    WHERE total > 0
    GROUP BY class_level
    ORDER BY avg_score DESC
    LIMIT 1
", $errors, 'Strongest class'));

$weakest_class = mysqli_fetch_assoc(safeQuery($conn, "
    SELECT class_level AS class,
           ROUND(AVG(score/total*100),1) as avg_score,
           ROUND(SUM(CASE WHEN score/total*100 >= 50 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as pass_rate
    FROM results
    WHERE total > 0
    GROUP BY class_level
    ORDER BY avg_score ASC
    LIMIT 1
", $errors, 'Weakest class'));

$trend_labels = [];
$trend_data = [];
$trend_res = safeQuery($conn, "
    SELECT DATE_FORMAT(date_taken, '%b %Y') as month,
           ROUND(AVG(score/total*100),1) as avg
    FROM results
    WHERE total > 0
    GROUP BY DATE_FORMAT(date_taken, '%Y-%m')
    ORDER BY date_taken ASC
    LIMIT 12
", $errors, 'Trend chart');
if ($trend_res) {
    while ($row = mysqli_fetch_assoc($trend_res)) {
        $trend_labels[] = $row['month'];
        $trend_data[] = $row['avg'];
    }
}

$dist = [0,0,0,0];
$dist_res = safeQuery($conn, "SELECT score, total FROM results WHERE total > 0", $errors, 'Score distribution');
if ($dist_res) {
    while ($row = mysqli_fetch_assoc($dist_res)) {
        $p = ($row['score'] / $row['total']) * 100;
        if ($p >= 80) $dist[0]++;
        elseif ($p >= 60) $dist[1]++;
        elseif ($p >= 40) $dist[2]++;
        else $dist[3]++;
    }
}

$top_performers_data = $top_performers ? mysqli_fetch_all($top_performers, MYSQLI_ASSOC) : [];
$weak_students_data = $weak_students ? mysqli_fetch_all($weak_students, MYSQLI_ASSOC) : [];
$class_report_data = $class_report ? mysqli_fetch_all($class_report, MYSQLI_ASSOC) : [];

$recent_results = safeQuery($conn, "
    SELECT student_name, class_level, score, total, date_taken
    FROM results
    WHERE total > 0
    ORDER BY date_taken DESC
    LIMIT 5
", $errors, 'Recent results');
$recent_results_data = $recent_results ? mysqli_fetch_all($recent_results, MYSQLI_ASSOC) : [];

$weak_student = $weak_students_data[0] ?? null;

function betaInsights($questions, $pass, $attempts, $active, $students, $class_count, $weak_student, $weak_class) {
    $insights = [];

    if ($weak_student) {
        $insights[] = "{$weak_student['student_name']} in {$weak_student['class']} is averaging {$weak_student['avg_score']}%. Recommend focused revision and short weekly quizzes.";
    }

    if ($weak_class) {
        $insights[] = "{$weak_class['class']} is the weakest class with an average of {$weak_class['avg_score']}% and a pass rate of {$weak_class['pass_rate']}%. Add guided review sessions and targeted in-class practice.";
    }

    if ($pass < 50) {
        $insights[] = 'Overall pass rate is below 50%. Increase formative checks and add quick revision lessons before every quiz.';
    } elseif ($pass < 70) {
        $insights[] = 'Pass rate is moderate; focus on small-group support for learners scoring between 40% and 60%.';
    } else {
        $insights[] = 'Pass rate is strong; keep reinforcing success with stretch assignments for higher achievers.';
    }

    if ($questions < 70) {
        $insights[] = 'Question bank is still small and should grow to cover more topics and difficulty levels.';
    } else {
        $insights[] = 'Question bank is healthy; consider tagging questions by topic and difficulty for better reports.';
    }

    if ($attempts < 2) {
        $insights[] = 'Low activity per student suggests learners need more regular quiz practice and encouragement.';
    } else {
        $insights[] = 'Activity is sufficient; use leaderboard-style progress tracking to sustain motivation.';
    }

    $insights[] = 'Use these analytics to assign follow-up quizzes for the weakest classes and reward consistent performers.';
    return $insights;
}

$class_count = count($class_report_data);
$ai_insights = betaInsights($total_questions, $pass_rate, $avg_attempt_per_student, $active_students, $total_students, $class_count, $weak_student, $weakest_class);
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-chart-bar"></i>
                    System Analytics Dashboard
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Real-time performance insights, learner progress tracking, and actionable recommendations.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn btn-primary" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <h4>Report warnings</h4>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Overview Grid -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <i class="fas fa-users"></i>
                <div class="stat-value"><?= number_format($total_students) ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card secondary">
                <i class="fas fa-question-circle"></i>
                <div class="stat-value"><?= number_format($total_questions) ?></div>
                <div class="stat-label">Questions in Bank</div>
            </div>
            <div class="stat-card success">
                <i class="fas fa-chart-line"></i>
                <div class="stat-value"><?= $overall_avg ?>%</div>
                <div class="stat-label">Average Score</div>
            </div>
            <div class="stat-card warning">
                <i class="fas fa-check-circle"></i>
                <div class="stat-value"><?= $pass_rate ?>%</div>
                <div class="stat-label">Pass Rate</div>
            </div>
            <div class="stat-card info">
                <i class="fas fa-users-cog"></i>
                <div class="stat-value"><?= $avg_attempt_per_student ?></div>
                <div class="stat-label">Tests / Student</div>
            </div>
            <div class="stat-card neutral">
                <i class="fas fa-clock"></i>
                <div class="stat-value"><?= round($median_score, 1) ?>%</div>
                <div class="stat-label">Median Score</div>
            </div>
        </div>

        <!-- AI Summary Card -->
        <div class="card-section">
            <h3><i class="fas fa-robot"></i> AI Beta Digest</h3>
            <div class="ai-insights">
                <ul>
                    <?php foreach ($ai_insights as $item): ?>
                        <li><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="overview"><i class="fas fa-chart-pie"></i> Overview</button>
            <button class="tab-btn" data-tab="performance"><i class="fas fa-trophy"></i> Performance</button>
            <button class="tab-btn" data-tab="analytics"><i class="fas fa-chart-bar"></i> Analytics</button>
            <button class="tab-btn" data-tab="insights"><i class="fas fa-lightbulb"></i> Insights</button>
        </div>

        <!-- OVERVIEW TAB -->
        <div class="tab-content active" id="tab-overview">
            <div class="summary-grid">
                <div class="summary-card">
                    <h4>Strongest class</h4>
                    <p><?= htmlspecialchars($strongest_class['class'] ?? 'N/A') ?> with <?= $strongest_class ? $strongest_class['avg_score'] . '% average' : 'no data' ?></p>
                </div>
                <div class="summary-card">
                    <h4>Weakest class</h4>
                    <p><?= htmlspecialchars($weakest_class['class'] ?? 'N/A') ?> with <?= $weakest_class ? $weakest_class['avg_score'] . '% average' : 'no data' ?></p>
                </div>
                <div class="summary-card">
                    <h4>Key weakness</h4>
                    <p><?= $weak_student ? htmlspecialchars($weak_student['student_name']) . ' from ' . htmlspecialchars($weak_student['class']) . ' is struggling at ' . $weak_student['avg_score'] . '%' : 'No specific low performer yet' ?></p>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h4>Trend</h4>
                    <canvas id="trendChart"></canvas>
                </div>
                <div class="chart-card">
                    <h4>Score Distribution</h4>
                    <canvas id="distChart"></canvas>
                </div>
            </div>
        </div>

        <!-- PERFORMANCE TAB -->
        <div class="tab-content" id="tab-performance">
            <div class="data-grid">
                <div class="data-card">
                    <h4>Top Performers</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Avg</th>
                                    <th>Best</th>
                                    <th>Worst</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_performers_data)): ?>
                                    <tr><td colspan="7" class="empty-state">No performance data yet.</td></tr>
                                <?php else: $rank = 1; foreach ($top_performers_data as $row): ?>
                                    <tr>
                                        <td><?= $rank ?></td>
                                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td><?= htmlspecialchars($row['class']) ?></td>
                                        <td><?= $row['avg_score'] ?>%</td>
                                        <td><?= $row['best_score'] ?>%</td>
                                        <td><?= $row['worst_score'] ?>%</td>
                                        <td><?= $row['attempts'] ?></td>
                                    </tr>
                                <?php $rank++; endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="data-card">
                    <h4>Lowest Performers</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Avg</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($weak_students_data)): ?>
                                    <tr><td colspan="4" class="empty-state">No struggling learners detected.</td></tr>
                                <?php else: foreach ($weak_students_data as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td><?= htmlspecialchars($row['class']) ?></td>
                                        <td><?= $row['avg_score'] ?>%</td>
                                        <td><?= $row['attempts'] ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ANALYTICS TAB -->
        <div class="tab-content" id="tab-analytics">
            <div class="data-card full-width">
                <h4>Class Performance</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Students</th>
                                <th>Attempts</th>
                                <th>Average</th>
                                <th>Best</th>
                                <th>Lowest</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($class_report_data)): ?>
                                <tr><td colspan="7" class="empty-state">No class performance available yet.</td></tr>
                            <?php else: foreach ($class_report_data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['class']) ?></td>
                                    <td><?= $row['students'] ?></td>
                                    <td><?= $row['attempts'] ?></td>
                                    <td><?= $row['avg_score'] ?>%</td>
                                    <td><?= $row['best_score'] ?>%</td>
                                    <td><?= $row['lowest_score'] ?>%</td>
                                    <td><?= $row['pass_rate'] ?>%</td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="data-card">
                <h4>Recent Results</h4>
                <div class="recent-results">
                    <?php if (empty($recent_results_data)): ?>
                        <p class="empty-state">No recent results to display.</p>
                    <?php else: foreach ($recent_results_data as $row): ?>
                        <div class="result-item">
                            <div class="result-info">
                                <strong><?= htmlspecialchars($row['student_name']) ?></strong>
                                <span class="result-class"><?= htmlspecialchars($row['class_level']) ?></span>
                            </div>
                            <div class="result-score">
                                <?= $row['score'] ?>/<?= $row['total'] ?>
                                <small>(<?= round(($row['score'] / $row['total']) * 100, 1) ?>%)</small>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- INSIGHTS TAB -->
        <div class="tab-content" id="tab-insights">
            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <h4>Improvement Rate</h4>
                    <p>Learners progressing across assessments.</p>
                    <div class="insight-value"><?= $active_students > 0 ? round(($active_students / max($total_students,1)) * 100) . '%' : 'N/A' ?></div>
                    <small>Active participation</small>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Success Metrics</h4>
                    <p>System-wide pass performance.</p>
                    <div class="insight-value"><?= $pass_rate ?>%</div>
                    <small>Pass rate</small>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <h4>Engagement</h4>
                    <p>Average quiz attempts per student.</p>
                    <div class="insight-value"><?= $avg_attempt_per_student ?></div>
                    <small>Tests/student</small>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4>Performance Gap</h4>
                    <p>Spread between highest and lowest scores.</p>
                    <div class="insight-value"><?= round($highest_score - $lowest_score, 1) ?>%</div>
                    <small>Score gap</small>
                </div>
            </div>

            <div class="card-section">
                <h4><i class="fas fa-robot"></i> AI Beta Assistant</h4>
                <p>Suggested Beta improvements for quizzes, learners and system behavior.</p>
                <ul class="ai-recommendations">
                    <?php foreach ($ai_insights as $item): ?>
                        <li><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div id="pdfReportTemplate" class="pdf-export-template">
    <div class="pdf-report-header">
        <div class="pdf-brand">
            <?php if ($site_logo): ?>
                <img src="<?= htmlspecialchars($site_logo) ?>" alt="Logo" class="pdf-logo">
            <?php endif; ?>
            <div>
                <h2><?= htmlspecialchars($school_name) ?></h2>
                <p>Beta Analytics Report</p>
            </div>
        </div>
        <div class="pdf-meta">
            <p>Generated: <?= date('d F Y \a\t H:i') ?></p>
            <p>Total students: <?= $total_students ?></p>
        </div>
    </div>
    <div class="pdf-section">
        <h3>Summary Metrics</h3>
        <table class="pdf-table">
            <tr><td>Total students</td><td><?= $total_students ?></td></tr>
            <tr><td>Questions in bank</td><td><?= $total_questions ?></td></tr>
            <tr><td>Average score</td><td><?= $overall_avg ?>%</td></tr>
            <tr><td>Pass rate</td><td><?= $pass_rate ?>%</td></tr>
            <tr><td>Median score</td><td><?= round($median_score, 1) ?>%</td></tr>
        </table>
    </div>
    <div class="pdf-section">
        <h3>Top Weak Areas</h3>
        <p><?= $weak_student ? htmlspecialchars($weak_student['student_name']) . ' (' . htmlspecialchars($weak_student['class']) . ') is a top priority.' : 'No low performer present.' ?></p>
        <p><?= $weakest_class ? htmlspecialchars($weakest_class['class']) . ' has the lowest average at ' . $weakest_class['avg_score'] . '%.' : 'No weak class identified yet.' ?></p>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
function initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });
}
function initCharts() {
    const trendCanvas = document.getElementById('trendChart');
    const distCanvas = document.getElementById('distChart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: { labels: <?= json_encode($trend_labels) ?>, datasets: [{ label: 'Average Score', data: <?= json_encode($trend_data) ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37, 99, 235, 0.16)', tension: 0.35, pointRadius: 4 }] },
            options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ min:0,max:100 } }, plugins:{ legend:{ display:false } } }
        });
    }
    if (distCanvas) {
        new Chart(distCanvas, {
            type:'doughnut',
            data:{ labels:['Excellent','Good','Average','Needs Improvement'], datasets:[{ data: <?= json_encode($dist) ?>, backgroundColor:['#16a34a','#eab308','#f97316','#ef4444'], borderColor:'#fff', borderWidth:2 }] },
            options:{ responsive:true, maintainAspectRatio:false }
        });
    }
}
async function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p','pt','a4');
    const template = document.getElementById('pdfReportTemplate');
    const canvas = await html2canvas(template, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const imgData = canvas.toDataURL('image/png');
    const margin = 30;
    const imgWidth = doc.internal.pageSize.getWidth() - margin * 2;
    const imgHeight = canvas.height * imgWidth / canvas.width;
    doc.addImage(imgData, 'PNG', margin, margin, imgWidth, imgHeight);
    doc.save('<?= preg_replace('/[^A-Za-z0-9_-]/', '_', $school_name) ?>_Beta_Report_<?= date('Y-m-d_H-i') ?>.pdf');
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => { initTabs(); initCharts(); }); else { initTabs(); initCharts(); }
</script>

<?php include 'Includes/footer.php'; ?>
