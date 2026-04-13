<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='main-content'><div class='view-card'><h2>Error</h2><p>Invalid Result ID.</p></div></div>";
    include 'Includes/footer.php';
    exit;
}

// Fetch result details
$query = "SELECT r.*, s.fullname as student_fullname 
          FROM results r 
          LEFT JOIN students s ON r.student_name = s.fullname 
          WHERE r.id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "<div class='main-content'><div class='view-card'><h2>Not Found</h2><p>Result not found.</p></div></div>";
    include 'Includes/footer.php';
    exit;
}

$percentage = round(($row['score'] / $row['total']) * 100, 1);
$status = $percentage >= 70 ? 'Excellent' : ($percentage >= 50 ? 'Good' : 'Needs Improvement');
$status_color = $percentage >= 70 ? '#159d10' : ($percentage >= 50 ? '#eab308' : '#ef4444');
?>

<div class="main-content">
    <div class="view-card">

        <!-- Result Header -->
        <div class="result-header">
            <h1 class="page-title">Quiz Result</h1>
            <p class="page-subtitle">Result ID: #<?= $row['id'] ?> • <?= date('d M Y • H:i', strtotime($row['date_taken'])) ?></p>
        </div>

        <div class="student-info-box">
            <strong>Student:</strong> <?= htmlspecialchars($row['student_name']) ?><br>
            <strong>Class:</strong> <?= htmlspecialchars($row['class_level']) ?>
        </div>

        <!-- Score Display -->
        <div class="score-card">
            <div class="score-circle" style="border-color: <?= $status_color ?>;">
                <div class="score-inner">
                    <h2><?= $percentage ?>%</h2>
                    <p><?= $row['score'] ?>/<?= $row['total'] ?></p>
                </div>
            </div>
            <div class="score-details">
                <h3><?= $status ?></h3>
                <p>Performance Status</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="results.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to All Results
            </a>
            <?php if ($percentage < 50): ?>
            <a href="#" class="btn btn-primary" onclick="alert('Remedial action can be added here');">
                <i class="fas fa-redo"></i> Recommend Remedial
            </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
.view-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.09);
    padding: 45px 50px;
    max-width: 850px;
    margin: 0 auto;
}

.result-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.page-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.page-subtitle {
    color: #64748b;
    font-size: 1.05rem;
    margin: 8px 0 0 0;
}

.student-info-box {
    background: #f8fafc;
    padding: 18px 24px;
    border-radius: 12px;
    margin-bottom: 35px;
    font-size: 1.1rem;
    line-height: 1.8;
}

.score-card {
    display: flex;
    align-items: center;
    gap: 40px;
    background: #f8fafc;
    padding: 35px 40px;
    border-radius: 16px;
    margin-bottom: 40px;
}

.score-circle {
    width: 160px;
    height: 160px;
    border: 12px solid #159d10;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.8rem;
}

.score-inner {
    text-align: center;
    line-height: 1.2;
}

.score-inner h2 {
    font-size: 3.2rem;
    font-weight: 700;
    margin: 0;
    color: #0f172a;
}

.score-inner p {
    margin: 5px 0 0 0;
    font-size: 1.1rem;
    color: #64748b;
}

.score-details h3 {
    font-size: 1.6rem;
    margin: 0 0 8px 0;
    color: #0f172a;
}

.actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 13px 26px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #159d10;
    color: white;
}

.btn-primary:hover {
    background: #0f7a0d;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-secondary:hover {
    background: #475569;
}

@media (max-width: 768px) {
    .score-card {
        flex-direction: column;
        text-align: center;
        gap: 25px;
    }
    .view-card {
        padding: 30px 25px;
    }
}
</style>
