<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='main-content'><div class='view-card'><h2>Error</h2><p>Invalid Student ID.</p></div></div>";
    include 'Includes/footer.php';
    exit;
}

// Fetch student details
$query = "SELECT * FROM students WHERE id = $id";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    echo "<div class='main-content'><div class='view-card'><h2>Not Found</h2><p>Student not found.</p></div></div>";
    include 'Includes/footer.php';
    exit;
}

// Fetch student's quiz results
$results_query = "SELECT * FROM results 
                  WHERE student_name = '" . mysqli_real_escape_string($conn, $student['fullname']) . "' 
                  ORDER BY date_taken DESC";
$results = mysqli_query($conn, $results_query);
?>

<div class="main-content">
    <div class="view-card profile-card beta-enhanced">

        <!-- Student Header -->
        <header class="profile-header">
            <div class="profile-header-content">
                <div class="profile-avatar-section">
                    <div class="profile-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="profile-info">
                        <span class="beta-badge"><i class="fas fa-rocket"></i> Student desk</span>
                        <h1 class="profile-name"><?= htmlspecialchars($student['fullname']) ?></h1>
                        <p class="profile-class"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($student['class']) ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-primary btn-profile">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="manage_students.php" class="btn btn-secondary btn-profile">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats Overview -->
        <?php 
        $stats_res = mysqli_query($conn, "
            SELECT 
                COUNT(*) as total_attempts,
                ROUND(AVG(score/total*100),1) as avg_score,
                MAX(score/total*100) as best_score,
                MIN(score/total*100) as worst_score
            FROM results 
            WHERE student_name = '" . mysqli_real_escape_string($conn, $student['fullname']) . "' 
            AND total > 0
        ");
        $stats = mysqli_fetch_assoc($stats_res);
        ?>
        <div class="profile-stats-grid">
            <div class="stat-card">
                <i class="fas fa-tasks"></i>
                <div class="stat-value"><?= $stats['total_attempts'] ?? 0 ?></div>
                <div class="stat-label">Quizzes Taken</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="stat-value"><?= $stats['avg_score'] ?? 0 ?>%</div>
                <div class="stat-label">Average Score</div>
            </div>
            <div class="stat-card success">
                <i class="fas fa-trophy"></i>
                <div class="stat-value"><?= round($stats['best_score'] ?? 0) ?>%</div>
                <div class="stat-label">Best Score</div>
            </div>
            <div class="stat-card warning">
                <i class="fas fa-arrow-down"></i>
                <div class="stat-value"><?= round($stats['worst_score'] ?? 0) ?>%</div>
                <div class="stat-label">Lowest Score</div>
            </div>
        </div>

        <!-- Results Section -->

        <!-- Results Section -->
        <section class="performance-section">
            <div class="performance-header">
                <h2><i class="fas fa-chart-bar"></i> Quiz Performance History</h2>
                <span class="results-count"><?= mysqli_num_rows($results) ?> Results</span>
            </div>

        <?php if (mysqli_num_rows($results) > 0): ?>
            <div class="table-wrapper-enhanced">
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-check-circle"></i> Score</th>
                            <th><i class="fas fa-percent"></i> Percentage</th>
                            <th><i class="fas fa-graduation-cap"></i> Class Level</th>
                            <th><i class="fas fa-badge"></i> Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($results)): 
                            $percentage = round(($row['score'] / $row['total']) * 100, 1);
                            $grade = $percentage >= 90 ? 'A' : ($percentage >= 80 ? 'B' : ($percentage >= 70 ? 'C' : ($percentage >= 60 ? 'D' : 'F')));
                            $grade_class = $percentage >= 70 ? 'high' : ($percentage >= 50 ? 'medium' : 'low');
                        ?>
                        <tr class="result-row">
                            <td class="date-cell"><i class="fas fa-clock"></i> <?= date('d M Y • H:i', strtotime($row['date_taken'])) ?></td>
                            <td class="score-cell"><strong><?= $row['score'] ?>/<?= $row['total'] ?></strong></td>
                            <td>
                                <div class="percentage-badge <?= $grade_class ?>">
                                    <?= $percentage ?>%
                                </div>
                            </td>
                            <td><span class="class-badge"><?= htmlspecialchars($row['class_level']) ?></span></td>
                            <td><span class="grade-badge <?= strtolower($grade_class) ?>"><?= $grade ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No quiz results found for this student yet.</p>
                <small>Results will appear here after the student completes a quiz.</small>
            </div>
        <?php endif; ?>
        </section>

    </div>
</div>

<style>
/* Profile Card Enhanced */
.profile-card {
    background: linear-gradient(135deg, var(--card-bg) 0%, rgba(59, 130, 246, 0.02) 100%);
    border-radius: 24px;
    box-shadow: var(--card-shadow);
    padding: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-card.beta-enhanced {
    border: 1px solid rgba(59, 130, 246, 0.2);
}

/* Profile Header */
.profile-header {
    margin-bottom: 40px;
    padding-bottom: 32px;
    border-bottom: 2px solid rgba(148, 163, 184, 0.15);
}

.profile-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 30px;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 24px;
}

.profile-avatar {
    width: 110px;
    height: 110px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.2rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 12px 32px rgba(59, 130, 246, 0.3);
}

.beta-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1d4ed8;
    padding: 8px 16px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.02em;
    margin-bottom: 12px;
}

.profile-name {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--text-color);
    margin: 0;
}

.profile-class {
    font-size: 1.05rem;
    color: var(--text-color);
    opacity: 0.75;
    margin: 8px 0 0 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-profile {
    padding: 11px 22px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: rgba(148, 163, 184, 0.12);
    color: var(--text-color);
    border: 1px solid rgba(148, 163, 184, 0.35);
}

.btn-secondary:hover {
    background: rgba(148, 163, 184, 0.2);
    transform: translateY(-2px);
}

/* Stats Grid */
.profile-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 18px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 18px;
    padding: 22px;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.stat-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
    transform: translateY(-4px);
}

.stat-card i {
    font-size: 1.8rem;
    color: #3b82f6;
}

.stat-card.success i {
    color: #16a34a;
}

.stat-card.warning i {
    color: #f97316;
}

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-color);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-color);
    opacity: 0.7;
}

/* Performance Section */
.performance-section {
    margin-top: 40px;
}

.performance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(148, 163, 184, 0.15);
}

.performance-header h2 {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text-color);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.results-count {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    padding: 6px 14px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Table Enhanced */
.table-wrapper-enhanced {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.performance-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.performance-table thead {
    background: rgba(59, 130, 246, 0.08);
}

.performance-table th {
    padding: 16px;
    font-weight: 700;
    color: var(--text-color);
    text-align: left;
    border-bottom: 2px solid rgba(148, 163, 184, 0.2);
}

.performance-table tbody tr {
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    transition: all 0.2s ease;
}

.performance-table tbody tr:hover {
    background: rgba(59, 130, 246, 0.04);
}

.performance-table td {
    padding: 16px;
    color: var(--text-color);
}

.date-cell {
    font-weight: 500;
}

.score-cell {
    color: #2563eb;
    font-weight: 600;
}

.percentage-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.9rem;
}

.percentage-badge.high {
    background: #dcfce7;
    color: #166534;
}

.percentage-badge.medium {
    background: #fef3c7;
    color: #92400e;
}

.percentage-badge.low {
    background: #fee2e2;
    color: #991b1b;
}

.class-badge {
    display: inline-block;
    background: rgba(59, 130, 246, 0.15);
    color: #2563eb;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.grade-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: 800;
    font-size: 1.1rem;
}

.grade-badge.high {
    background: #dcfce7;
    color: #166534;
}

.grade-badge.medium {
    background: #fef3c7;
    color: #92400e;
}

.grade-badge.low {
    background: #fee2e2;
    color: #991b1b;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.empty-state p {
    font-size: 1.1rem;
    margin: 0;
    font-weight: 500;
}

.empty-state small {
    display: block;
    margin-top: 8px;
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-card {
        padding: 24px;
    }

    .profile-header-content {
        flex-direction: column;
    }

    .profile-actions {
        width: 100%;
    }

    .btn-profile {
        flex: 1;
        justify-content: center;
    }

    .performance-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .profile-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .performance-table {
        font-size: 0.85rem;
    }

    .performance-table th,
    .performance-table td {
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .profile-card {
        padding: 18px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }

    .profile-name {
        font-size: 1.6rem;
    }

    .profile-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
