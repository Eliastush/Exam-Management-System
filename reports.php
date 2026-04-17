<?php
include 'includes/header.php';

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

$errors = [];

function report_query(mysqli $conn, string $sql, array &$errors, string $label) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        $errors[] = $label . ' failed: ' . mysqli_error($conn);
    }
    return $result;
}

function report_scalar(mysqli $conn, string $sql, string $field, array &$errors, string $label, $fallback = 0) {
    $result = report_query($conn, $sql, $errors, $label);
    if (!$result) {
        return $fallback;
    }
    $row = mysqli_fetch_assoc($result);
    return $row[$field] ?? $fallback;
}

$settings = [];
$settings_res = report_query($conn, "SELECT setting_key, setting_value FROM settings", $errors, 'Load settings');
if ($settings_res) {
    while ($row = mysqli_fetch_assoc($settings_res)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$school_name = $settings['school_name'] ?? 'Mustard Seed ICT Dashboard';
$site_logo = $settings['site_logo'] ?? 'msis logo.png';
$pass_mark = (int) ($settings['pass_mark'] ?? 50);

$total_students = (int) report_scalar($conn, "SELECT COUNT(*) AS total FROM students", 'total', $errors, 'Student count', 0);
$total_questions = (int) report_scalar($conn, "SELECT COUNT(*) AS total FROM questions", 'total', $errors, 'Question count', 0);
$total_attempts = (int) report_scalar($conn, "SELECT COUNT(*) AS total FROM results", 'total', $errors, 'Attempt count', 0);
$active_students = (int) report_scalar($conn, "SELECT COUNT(DISTINCT student_name) AS total FROM results", 'total', $errors, 'Active student count', 0);
$overall_avg = round((float) report_scalar($conn, "SELECT AVG(score / total * 100) AS avg_score FROM results WHERE total > 0", 'avg_score', $errors, 'Average score', 0), 1);
$pass_rate = round((float) report_scalar($conn, "SELECT AVG(CASE WHEN score / total * 100 >= {$pass_mark} THEN 100 ELSE 0 END) AS pass_rate FROM results WHERE total > 0", 'pass_rate', $errors, 'Pass rate', 0), 1);
$highest_score = round((float) report_scalar($conn, "SELECT MAX(score / total * 100) AS high_score FROM results WHERE total > 0", 'high_score', $errors, 'Highest score', 0), 1);
$lowest_score = round((float) report_scalar($conn, "SELECT MIN(score / total * 100) AS low_score FROM results WHERE total > 0", 'low_score', $errors, 'Lowest score', 0), 1);
$avg_attempt_per_student = $total_students > 0 ? round($total_attempts / $total_students, 1) : 0;
$participation_rate = $total_students > 0 ? round(($active_students / $total_students) * 100, 1) : 0;

$median_score = 0;
$median_res = report_query($conn, "SELECT score / total * 100 AS score_percent FROM results WHERE total > 0 ORDER BY score_percent", $errors, 'Median score');
if ($median_res) {
    $scores = [];
    while ($row = mysqli_fetch_assoc($median_res)) {
        $scores[] = (float) $row['score_percent'];
    }
    $count_scores = count($scores);
    if ($count_scores > 0) {
        $middle = intdiv($count_scores, 2);
        $median_score = $count_scores % 2 === 0 ? round(($scores[$middle - 1] + $scores[$middle]) / 2, 1) : round($scores[$middle], 1);
    }
}

$top_performers_res = report_query($conn, "
    SELECT student_name, class_level AS class_name,
           ROUND(AVG(score / total * 100), 1) AS avg_score,
           ROUND(MAX(score / total * 100), 1) AS best_score,
           ROUND(MIN(score / total * 100), 1) AS worst_score,
           COUNT(*) AS attempts
    FROM results
    WHERE total > 0
    GROUP BY student_name, class_level
    ORDER BY avg_score DESC
    LIMIT 10
", $errors, 'Top performers');
$top_performers = $top_performers_res ? mysqli_fetch_all($top_performers_res, MYSQLI_ASSOC) : [];

$weak_students_res = report_query($conn, "
    SELECT student_name, class_level AS class_name,
           ROUND(AVG(score / total * 100), 1) AS avg_score,
           COUNT(*) AS attempts
    FROM results
    WHERE total > 0
    GROUP BY student_name, class_level
    ORDER BY avg_score ASC
    LIMIT 8
", $errors, 'Weak students');
$weak_students = $weak_students_res ? mysqli_fetch_all($weak_students_res, MYSQLI_ASSOC) : [];
$weak_student = $weak_students[0] ?? null;

$class_report_res = report_query($conn, "
    SELECT class_level AS class_name,
           COUNT(DISTINCT student_name) AS students,
           COUNT(*) AS attempts,
           ROUND(AVG(score / total * 100), 1) AS avg_score,
           ROUND(MAX(score / total * 100), 1) AS best_score,
           ROUND(MIN(score / total * 100), 1) AS low_score,
           ROUND(SUM(CASE WHEN score / total * 100 >= {$pass_mark} THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) AS pass_rate
    FROM results
    WHERE total > 0
    GROUP BY class_level
    ORDER BY avg_score DESC
", $errors, 'Class report');
$class_report = $class_report_res ? mysqli_fetch_all($class_report_res, MYSQLI_ASSOC) : [];
$strongest_class = $class_report[0] ?? null;
$weakest_class = !empty($class_report) ? $class_report[count($class_report) - 1] : null;

$recent_results_res = report_query($conn, "
    SELECT student_name, class_level, score, total, date_taken
    FROM results
    WHERE total > 0
    ORDER BY date_taken DESC
    LIMIT 6
", $errors, 'Recent results');
$recent_results = $recent_results_res ? mysqli_fetch_all($recent_results_res, MYSQLI_ASSOC) : [];

$trend_res = report_query($conn, "
    SELECT DATE_FORMAT(date_taken, '%b %Y') AS month_label,
           ROUND(AVG(score / total * 100), 1) AS avg_score
    FROM results
    WHERE total > 0
    GROUP BY DATE_FORMAT(date_taken, '%Y-%m')
    ORDER BY MIN(date_taken) ASC
    LIMIT 12
", $errors, 'Trend chart');
$trend_labels = [];
$trend_data = [];
if ($trend_res) {
    while ($row = mysqli_fetch_assoc($trend_res)) {
        $trend_labels[] = $row['month_label'];
        $trend_data[] = (float) $row['avg_score'];
    }
}

$dist = [0, 0, 0, 0];
$dist_res = report_query($conn, "SELECT score, total FROM results WHERE total > 0", $errors, 'Score distribution');
if ($dist_res) {
    while ($row = mysqli_fetch_assoc($dist_res)) {
        $percent = ($row['score'] / $row['total']) * 100;
        if ($percent >= 80) {
            $dist[0]++;
        } elseif ($percent >= 60) {
            $dist[1]++;
        } elseif ($percent >= 40) {
            $dist[2]++;
        } else {
            $dist[3]++;
        }
    }
}

$class_labels = array_map(fn($row) => $row['class_name'], $class_report);
$class_avg = array_map(fn($row) => (float) $row['avg_score'], $class_report);

$report_summary = 'This beta web app is already giving leadership-level visibility into attainment, participation, and question-bank maturity.';
if ($pass_rate < 50) {
    $report_summary = 'The reporting layer is flagging urgent performance concerns, so the beta is already doing its job as an early-warning system.';
} elseif ($pass_rate < 70) {
    $report_summary = 'Performance is mixed but actionable, which is exactly where a beta analytics tool should help the school move faster.';
} elseif ($participation_rate < 70) {
    $report_summary = 'Attainment is encouraging, but the next beta priority is increasing participation so the insights represent more learners.';
}

$report_headline = $weak_student
    ? $weak_student['student_name'] . ' in ' . $weak_student['class_name'] . ' is the clearest immediate intervention case.'
    : 'No single learner stands out as a critical intervention case yet.';

$report_digest = [];
$report_digest[] = "The system currently covers {$total_students} registered students, {$total_questions} questions, and {$total_attempts} recorded attempts, which means the beta has enough depth to support real operational decisions.";
$report_digest[] = "Average attainment is {$overall_avg}% with a {$pass_rate}% pass rate against a {$pass_mark}% threshold, so the reporting layer is already translating raw results into leadership-friendly performance language.";
$report_digest[] = "Median score is {$median_score}% and the spread between the highest and lowest recorded outcomes is " . round($highest_score - $lowest_score, 1) . " percentage points, showing whether the school is dealing with broad consistency or a sharp attainment gap.";
if ($weak_student) {
    $report_digest[] = $weak_student['student_name'] . ' in ' . $weak_student['class_name'] . ' is averaging ' . $weak_student['avg_score'] . '%, so that learner should move to the front of any focused revision plan.';
}
if ($weakest_class) {
    $report_digest[] = $weakest_class['class_name'] . ' is currently the weakest class at ' . $weakest_class['avg_score'] . '% with a pass rate of ' . $weakest_class['pass_rate'] . '%, which makes it the clearest target for guided support.';
}
if ($strongest_class) {
    $report_digest[] = $strongest_class['class_name'] . ' is leading the school at ' . $strongest_class['avg_score'] . '%, so its revision habits or teaching approach may be worth copying elsewhere.';
}
$report_digest[] = $avg_attempt_per_student < 2
    ? 'Learner activity is still relatively light, so the next beta improvement should encourage more frequent low-stakes quizzes.'
    : 'Learner activity is healthy enough for the analytics to stay credible, so the next beta improvement can focus on sharper recommendations and richer teacher actions.';

$report_actions = [];
if ($weak_student) {
    $report_actions[] = 'Create a short follow-up quiz specifically for ' . $weak_student['student_name'] . ' and closely related learners in ' . $weak_student['class_name'] . '.';
}
if ($weakest_class) {
    $report_actions[] = 'Schedule a guided revision block for ' . $weakest_class['class_name'] . ' before the next assessment cycle.';
}
$report_actions[] = 'Tag more questions by topic and difficulty so the next analytics iteration can explain why learners are missing marks, not just that they are missing them.';
$report_actions[] = 'Keep this labeled as Beta V2.1 because the system is insightful already, but still evolving toward final submission quality.';
?>

<div class="main-content">
    <div class="view-card reports-page-shell">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-chart-bar"></i>
                    Beta Analytics Report
                    <span class="beta-badge">Reporting room</span>
                </h1>
                <p class="page-subtitle">Interpreted school analytics for a web app that is useful now, but still clearly in beta and not final for submission.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" type="button" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                <button class="btn btn-primary" type="button" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-warning">
                <strong>Report warnings:</strong>
                <?= htmlspecialchars(implode(' | ', $errors)) ?>
            </div>
        <?php endif; ?>

        <div class="card reports-hero-card">
            <span class="profile-kicker">Executive Readout</span>
            <h2><?= htmlspecialchars($report_summary) ?></h2>
            <p><?= htmlspecialchars($report_headline) ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary"><i class="fas fa-users"></i><div class="stat-value"><?= number_format($total_students) ?></div><div class="stat-label">Total Students</div></div>
            <div class="stat-card secondary"><i class="fas fa-question-circle"></i><div class="stat-value"><?= number_format($total_questions) ?></div><div class="stat-label">Questions in Bank</div></div>
            <div class="stat-card success"><i class="fas fa-chart-line"></i><div class="stat-value"><?= number_format($overall_avg, 1) ?>%</div><div class="stat-label">Average Score</div></div>
            <div class="stat-card warning"><i class="fas fa-square-poll-vertical"></i><div class="stat-value"><?= number_format($pass_rate, 1) ?>%</div><div class="stat-label">Pass Rate</div></div>
            <div class="stat-card info"><i class="fas fa-wave-square"></i><div class="stat-value"><?= number_format($median_score, 1) ?>%</div><div class="stat-label">Median Score</div></div>
            <div class="stat-card neutral"><i class="fas fa-chart-pie"></i><div class="stat-value"><?= number_format($participation_rate, 1) ?>%</div><div class="stat-label">Participation</div></div>
        </div>

        <div class="learner-grid space-lg">
            <div class="card beta-digest-card">
                <h3><i class="fas fa-robot"></i> AI Beta Digest</h3>
                <div class="beta-digest-list">
                    <?php foreach ($report_digest as $item): ?>
                        <div class="beta-digest-item">
                            <span class="beta-digest-dot"></span>
                            <p><?= htmlspecialchars($item) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card beta-digest-card">
                <h3><i class="fas fa-bolt"></i> Suggested Next Actions</h3>
                <div class="beta-digest-list">
                    <?php foreach ($report_actions as $item): ?>
                        <div class="beta-digest-item">
                            <span class="beta-digest-dot"></span>
                            <p><?= htmlspecialchars($item) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="overview">Overview</button>
            <button class="tab-btn" data-tab="performance">Performance</button>
            <button class="tab-btn" data-tab="analytics">Analytics</button>
            <button class="tab-btn" data-tab="export">Export View</button>
        </div>

        <div class="tab-content active" id="tab-overview">
            <div class="summary-grid">
                <div class="summary-card">
                    <h4>Strongest class</h4>
                    <p><?= $strongest_class ? htmlspecialchars($strongest_class['class_name']) . ' is leading at ' . $strongest_class['avg_score'] . '% with a ' . $strongest_class['pass_rate'] . '% pass rate.' : 'No class data available yet.' ?></p>
                </div>
                <div class="summary-card">
                    <h4>Weakest class</h4>
                    <p><?= $weakest_class ? htmlspecialchars($weakest_class['class_name']) . ' is lowest at ' . $weakest_class['avg_score'] . '%, so it should receive the next intervention block.' : 'No weak class identified yet.' ?></p>
                </div>
                <div class="summary-card">
                    <h4>Top weak area</h4>
                    <p><?= $weak_student ? htmlspecialchars($weak_student['student_name']) . ' in ' . htmlspecialchars($weak_student['class_name']) . ' is averaging ' . $weak_student['avg_score'] . '% and should be treated as a priority learner.' : 'No clear weak learner available yet.' ?></p>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h4>Performance Trend</h4>
                    <div class="chart-shell"><canvas id="trendChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <h4>Score Distribution</h4>
                    <div class="chart-shell"><canvas id="distChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-performance">
            <div class="data-grid">
                <div class="data-card">
                    <h4>Top Performers</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Avg</th>
                                    <th>Best</th>
                                    <th>Worst</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_performers)): ?>
                                    <tr><td colspan="7" class="empty-state">No performance data yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($top_performers as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                                            <td><?= $row['avg_score'] ?>%</td>
                                            <td><?= $row['best_score'] ?>%</td>
                                            <td><?= $row['worst_score'] ?>%</td>
                                            <td><?= $row['attempts'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="data-card">
                    <h4>Support Priority Learners</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Average</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($weak_students)): ?>
                                    <tr><td colspan="5" class="empty-state">No struggling learners detected.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($weak_students as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                                            <td><?= $row['avg_score'] ?>%</td>
                                            <td><?= $row['attempts'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-analytics">
            <div class="data-card full-width">
                <h4>Class Performance Breakdown</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
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
                            <?php if (empty($class_report)): ?>
                                <tr><td colspan="8" class="empty-state">No class performance available yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($class_report as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                                        <td><?= $row['students'] ?></td>
                                        <td><?= $row['attempts'] ?></td>
                                        <td><?= $row['avg_score'] ?>%</td>
                                        <td><?= $row['best_score'] ?>%</td>
                                        <td><?= $row['low_score'] ?>%</td>
                                        <td><?= $row['pass_rate'] ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="data-card full-width space-lg">
                <h4>Recent Results</h4>
                <div class="recent-results">
                    <?php if (empty($recent_results)): ?>
                        <p class="empty-state">No recent results to display.</p>
                    <?php else: ?>
                        <?php foreach ($recent_results as $row): ?>
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
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-export">
            <div id="pdfReportTemplate" class="pdf-export-template report-export-card">
                <div class="pdf-report-header">
                    <div class="pdf-brand">
                        <?php if ($site_logo): ?>
                            <img src="<?= htmlspecialchars($site_logo) ?>" alt="Logo" class="pdf-logo">
                        <?php endif; ?>
                        <div>
                            <h2><?= htmlspecialchars($school_name) ?></h2>
                            <p>ANALITICS REPORT</p>
                        </div>
                    </div>
                    <div class="pdf-meta">
                        <p>Generated: <?= date('d F Y \a\t H:i') ?></p>
                        <p>Total students: <?= number_format($total_students) ?></p>
                    </div>
                </div>

                <div class="pdf-section">
                    <h3>Summary Metrics</h3>
                    <table class="pdf-table">
                        <tr><td>Total students</td><td><?= number_format($total_students) ?></td></tr>
                        <tr><td>Questions in bank</td><td><?= number_format($total_questions) ?></td></tr>
                        <tr><td>Average score</td><td><?= number_format($overall_avg, 1) ?>%</td></tr>
                        <tr><td>Pass rate</td><td><?= number_format($pass_rate, 1) ?>%</td></tr>
                        <tr><td>Median score</td><td><?= number_format($median_score, 1) ?>%</td></tr>
                    </table>
                </div>

                <div class="pdf-section">
                    <h3>Top Weak Areas</h3>
                    <p><?= $weak_student ? htmlspecialchars($weak_student['student_name']) . ' (' . htmlspecialchars($weak_student['class_name']) . ') is a top priority because the current average is ' . $weak_student['avg_score'] . '%.' : 'No low performer has been isolated yet.' ?></p>
                    <p><?= $weakest_class ? htmlspecialchars($weakest_class['class_name']) . ' has the lowest class average at ' . $weakest_class['avg_score'] . '%, which makes it the clearest group intervention target.' : 'No weak class identified yet.' ?></p>
                </div>

                <div class="pdf-section">
                    <h3>AI Beta Digest</h3>
                    <?php foreach ($report_digest as $item): ?>
                        <p><?= htmlspecialchars($item) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
const reportRootStyles = getComputedStyle(document.documentElement);
const reportPrimary = reportRootStyles.getPropertyValue('--primary-color').trim() || '#1f8f63';
const reportPrimaryRgb = reportRootStyles.getPropertyValue('--primary-rgb').trim() || '31, 143, 99';
const reportSoft = `rgba(${reportPrimaryRgb}, 0.16)`;
const reportChartOptions = { responsive: true, maintainAspectRatio: false, animation: { duration: 650 } };

function initReportTabs() {
    document.querySelectorAll('.tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach((item) => item.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach((item) => item.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab)?.classList.add('active');
        });
    });
}

function initReportCharts() {
    const trendCanvas = document.getElementById('trendChart');
    const distCanvas = document.getElementById('distChart');

    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: <?= json_encode($trend_labels) ?>,
                datasets: [{
                    label: 'Average Score',
                    data: <?= json_encode($trend_data) ?>,
                    borderColor: reportPrimary,
                    backgroundColor: reportSoft,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4
                }]
            },
            options: {
                ...reportChartOptions,
                scales: { y: { min: 0, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });
    }

    if (distCanvas) {
        new Chart(distCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Needs Support'],
                datasets: [{ data: <?= json_encode($dist) ?>, backgroundColor: ['#16a34a', reportPrimary, '#f59e0b', '#dc2626'], borderWidth: 2, borderColor: '#ffffff' }]
            },
            options: reportChartOptions
        });
    }
}

async function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');
    const template = document.getElementById('pdfReportTemplate');
    const canvas = await html2canvas(template, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const imgData = canvas.toDataURL('image/png');
    const margin = 30;
    const imgWidth = doc.internal.pageSize.getWidth() - (margin * 2);
    const imgHeight = canvas.height * imgWidth / canvas.width;
    doc.addImage(imgData, 'PNG', margin, margin, imgWidth, imgHeight);
    doc.save('<?= preg_replace('/[^A-Za-z0-9_-]/', '_', $school_name) ?>_Beta_Analytics_V2_1_<?= date('Y-m-d_H-i') ?>.pdf');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initReportTabs();
        initReportCharts();
    });
} else {
    initReportTabs();
    initReportCharts();
}
</script>

<?php include 'includes/footer.php'; ?>
