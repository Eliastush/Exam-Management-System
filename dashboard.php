<?php
include 'Includes/header.php';
// Sidebar will be included later

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// --- KPI CARDS ---
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'];
$total_quizzes  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM results"))['c'];
$avg_score      = round(mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score/total*100) as a FROM results WHERE total>0"))['a'] ?? 0, 1);
$pass_rate      = round(mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(CASE WHEN score/total*100 >=50 THEN 100 ELSE 0 END) as p FROM results WHERE total>0"))['p'] ?? 0, 1);

// --- Recent 7 marks ---
$recent7 = mysqli_query($conn,"SELECT r.*, s.fullname as student_name FROM results r LEFT JOIN students s ON r.student_name=s.fullname ORDER BY r.date_taken DESC LIMIT 7");

$best_class = mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_level, ROUND(AVG(score/total*100),1) as avg FROM results GROUP BY class_level ORDER BY avg DESC LIMIT 1"));
$best_student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT student_name, class_level, ROUND(score/total*100,1) as perc FROM results ORDER BY perc DESC LIMIT 1"));

$recent_results = mysqli_query($conn, "SELECT r.*, s.fullname as student_name FROM results r LEFT JOIN students s ON r.student_name=s.fullname ORDER BY r.date_taken DESC LIMIT 10");

$questions_by_level = mysqli_query($conn, "SELECT class_level, COUNT(*) as total FROM questions GROUP BY class_level");

$trend_labels = []; $trend_data = [];
$trend_res = mysqli_query($conn,"SELECT DATE_FORMAT(date_taken,'%b') as month, ROUND(AVG(score/total*100),1) as avg FROM results GROUP BY month ORDER BY date_taken ASC");
while($row = mysqli_fetch_assoc($trend_res)) {
    $trend_labels[] = $row['month'];
    $trend_data[] = $row['avg'];
}

$distribution_data = [0,0,0,0];
$res = mysqli_query($conn,"SELECT score,total FROM results WHERE total>0");
while($row = mysqli_fetch_assoc($res)){
    $perc = ($row['score']/$row['total'])*100;
    if($perc>=80) $distribution_data[0]++;
    else if($perc>=60) $distribution_data[1]++;
    else if($perc>=40) $distribution_data[2]++;
    else $distribution_data[3]++;
}

$class_labels=[]; $class_avg=[];
$class_res=mysqli_query($conn,"SELECT class_level, ROUND(AVG(score/total*100),1) as avg FROM results GROUP BY class_level");
while($r=mysqli_fetch_assoc($class_res)){ $class_labels[]=$r['class_level']; $class_avg[]=$r['avg']; }

$questions_labels=[]; $questions_count=[];
while($r=mysqli_fetch_assoc($questions_by_level)){ $questions_labels[]=$r['class_level']; $questions_count[]=$r['total']; }

// --- TOP & BOTTOM 5 ---
$top5 = mysqli_query($conn,"SELECT student_name,class_level,ROUND(score/total*100,1) as perc FROM results WHERE total>0 ORDER BY perc DESC LIMIT 5");
$bottom5 = mysqli_query($conn,"SELECT student_name,class_level,ROUND(score/total*100,1) as perc FROM results WHERE total>0 ORDER BY perc ASC LIMIT 5");

// --- CALENDAR ---
$calendar_events = [
    'ICT Quiz Week' => ['start'=>'2026-04-05','end'=>'2026-04-10'],
    'Digital Literacy Workshop' => ['start'=>'2026-04-12','end'=>'2026-04-12'],
    'Parent-Teacher ICT Review' => ['start'=>'2026-04-15','end'=>'2026-04-15']
];

// --- LATEST NEWS / ANNOUNCEMENTS ---
$classes_res = mysqli_query($conn,"SELECT DISTINCT class_level FROM results");
$news=[];
while($c=mysqli_fetch_assoc($classes_res)){
    $class=$c['class_level'];
    $improved = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT student_name, ROUND(score/total*100,1) as perc 
        FROM results WHERE class_level='$class' ORDER BY perc DESC LIMIT 1"));
    $dropped = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT student_name, ROUND(score/total*100,1) as perc 
        FROM results WHERE class_level='$class' ORDER BY perc ASC LIMIT 1"));
    $news[$class] = ['top'=>$improved,'low'=>$dropped];
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-chart-line"></i>
                    ICT Dashboard
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Welcome! Here's your system overview</p>
            </div>
        </div>

    <!-- KPI Cards -->
    <div class="stats-grid">
        <div class="card card-primary">
            <i class="fas fa-users fa-2x"></i>
            <h2 class="counter"><?= $total_students ?></h2>
            <p>Total Students</p>
        </div>
        <div class="card card-primary">
            <i class="fas fa-clipboard-list fa-2x"></i>
            <h2 class="counter"><?= $total_quizzes ?></h2>
            <p>Quizzes Taken</p>
        </div>
        <div class="card card-primary">
            <i class="fas fa-chart-line fa-2x"></i>
            <h2 class="counter"><?= $avg_score ?>%</h2>
            <p>Average Score</p>
        </div>
        <div class="card card-primary">
            <i class="fas fa-trophy fa-2x"></i>
            <h2 class="counter"><?= $pass_rate ?>%</h2>
            <p>Pass Rate</p>
        </div>
        <div class="card card-secondary">
            <i class="fas fa-school fa-2x"></i>
            <h2><?= $best_class['class_level'] ?? 'N/A' ?></h2>
            <p>Top Class</p>
        </div>
        <div class="card card-secondary">
            <i class="fas fa-user-graduate fa-2x"></i>
            <h2><?= $best_student['student_name'] ?? 'N/A' ?></h2>
            <p>Top Student</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Performance Trend</h3>
            <canvas id="trendChart"></canvas>
        </div>
        <div class="card">
            <h3><i class="fas fa-chart-pie"></i> Score Distribution</h3>
            <canvas id="distributionChart"></canvas>
        </div>
        <div class="card">
            <h3><i class="fas fa-school"></i> Class Performance</h3>
            <canvas id="classChart"></canvas>
        </div>
        <div class="card">
            <h3><i class="fas fa-question-circle"></i> Questions by Level</h3>
            <canvas id="questionsChart"></canvas>
        </div>
    </div>

    <!-- Top & Bottom 5 Learners -->
    <div class="learner-grid">
        <div class="card">
            <h3><i class="fas fa-trophy"></i> Top 5 Learners</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Score %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($top5)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><span class="class-badge"><?= htmlspecialchars($row['class_level']) ?></span></td>
                            <td><span class="score-badge pass"><?= $row['perc'] ?>%</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-arrow-down"></i> Bottom 5 Learners</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Score %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($bottom5)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><span class="class-badge"><?= htmlspecialchars($row['class_level']) ?></span></td>
                            <td><span class="score-badge fail"><?= $row['perc'] ?>%</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Recent 7 Marks -->
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
                    <?php while($row = mysqli_fetch_assoc($recent7)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><span class="class-badge"><?= htmlspecialchars($row['class_level']) ?></span></td>
                        <td>
                            <span class="score-badge <?= round($row['score']/$row['total']*100,1) >= 50 ? 'pass' : 'fail' ?>">
                                <?= round($row['score']/$row['total']*100,1) ?>%
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($row['date_taken'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Announcements / Latest News -->
    <div class="card">
        <h3><i class="fas fa-newspaper"></i> Class Highlights</h3>
        <div class="news-grid">
            <?php foreach($news as $class => $data): ?>
            <div class="news-item">
                <h4><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($class) ?></h4>
                <p><strong class="text-success">Most Improved:</strong> <?= htmlspecialchars($data['top']['student_name'] ?? 'N/A') ?> (<?= $data['top']['perc'] ?? 0 ?>%)</p>
                <p><strong class="text-warning">Needs Improvement:</strong> <?= htmlspecialchars($data['low']['student_name'] ?? 'N/A') ?> (<?= $data['low']['perc'] ?? 0 ?>%)</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
.charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 40px; }
.learner-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 30px; margin-bottom: 40px; }
.news-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
.news-item { background: var(--card-bg); padding: 20px; border-radius: 12px; border-left: 4px solid var(--primary-color); transition: all 0.3s ease; }
.news-item:hover { transform: translateY(-2px); box-shadow: var(--card-shadow); }
.news-item h4 { margin: 0 0 12px 0; color: var(--text-color); font-size: 1.1rem; }
.news-item p { margin: 6px 0; font-size: 0.95rem; }
.class-badge { background: rgba(59, 130, 246, 0.2); color: #0369a1; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500; }
.counter { font-size: 2rem; font-weight: 700; color: var(--text-color); animation: countUp 2s ease-out; }
@keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Get colors from CSS variables
const computedStyle = getComputedStyle(document.documentElement);
const primaryColor = computedStyle.getPropertyValue('--primary-color').trim();
const successColor = '#16a34a';
const warningColor = '#f59e0b';
const dangerColor = '#ef4444';
const infoColor = '#3b82f6';

// Charts
new Chart(document.getElementById('trendChart'), {
    type:'line',
    data:{labels:<?= json_encode($trend_labels) ?>, datasets:[{label:'Avg Score %', data:<?= json_encode($trend_data) ?>, borderColor: primaryColor, backgroundColor: primaryColor + '33', tension:0.4, fill:true}]},
    options:{responsive:true}
});

new Chart(document.getElementById('distributionChart'), {
    type:'doughnut',
    data:{labels:['Excellent','Good','Average','Low'], datasets:[{data:<?= json_encode($distribution_data) ?>, backgroundColor:[successColor, warningColor, infoColor, dangerColor]}]},
    options:{responsive:true}
});

new Chart(document.getElementById('classChart'), {
    type:'bar',
    data:{labels:<?= json_encode($class_labels) ?>, datasets:[{label:'Average %', data:<?= json_encode($class_avg) ?>, backgroundColor: primaryColor}]},
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, max:100}}}
});

new Chart(document.getElementById('questionsChart'), {
    type:'bar',
    data:{labels:<?= json_encode($questions_labels) ?>, datasets:[{label:'Questions', data:<?= json_encode($questions_count) ?>, backgroundColor: warningColor}]},
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
</script>


