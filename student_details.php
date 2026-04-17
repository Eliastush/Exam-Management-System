<?php
// student_details.php - Returns HTML for Student Modal

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) {
    echo "<p style='color:red;'>Database connection failed.</p>";
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<p style='color:red;'>Invalid Student ID.</p>";
    exit;
}

// Fetch student basic info
$query = "SELECT * FROM students WHERE id = $id";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    echo "<p style='color:red;'>Student not found.</p>";
    exit;
}

// Fetch all quiz results for this student
$results_query = "SELECT * FROM results 
                  WHERE student_name = '" . mysqli_real_escape_string($conn, $student['fullname']) . "' 
                  ORDER BY date_taken DESC";
$results = mysqli_query($conn, $results_query);
?>

<div class="modal-student-info">
    <div class="info-row">
        <strong>Full Name:</strong>
        <span><?= htmlspecialchars($student['fullname']) ?></span>
    </div>
    <div class="info-row">
        <strong>Class:</strong>
        <span><?= htmlspecialchars($student['class']) ?></span>
    </div>
    <div class="info-row">
        <strong>Student ID:</strong>
        <span><?= $student['id'] ?></span>
    </div>
</div>

<h4 style="margin: 25px 0 15px 0; color:#159d10;">Quiz Performance History</h4>

<?php if (mysqli_num_rows($results) > 0): ?>
    <table class="modal-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Class Level</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($results)): 
                $percentage = round(($row['score'] / $row['total']) * 100, 1);
                $status_class = $percentage >= 70 ? 'high' : ($percentage >= 50 ? 'medium' : 'low');
            ?>
            <tr>
                <td><?= date('d M Y H:i', strtotime($row['date_taken'])) ?></td>
                <td><strong><?= $row['score'] ?>/<?= $row['total'] ?></strong></td>
                <td><span class="percentage <?= $status_class ?>"><?= $percentage ?>%</span></td>
                <td><?= htmlspecialchars($row['class_level']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="color:#64748b; font-style:italic; text-align:center; padding:30px 0;">
        No quiz results found for this student yet.
    </p>
<?php endif; ?>

<style>
.modal-student-info {
    background: #f8fafc;
    padding: 18px 22px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row strong {
    color: #475569;
}

.modal-table {
    width: 100%;
    border-collapse: collapse;
}

.modal-table th {
    background: #f1f5f9;
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    color: #475569;
}

.modal-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #f1f5f9;
}

.percentage {
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 20px;
}

.percentage.high { background: #ecfdf5; color: #159d10; }
.percentage.medium { background: #fefce8; color: #ca8a04; }
.percentage.low { background: #fef2f2; color: #ef4444; }
</style>
