<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// Handle Attendance Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $student_id = intval($_POST['student_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $query = "INSERT INTO attendance (student_id, date, status, notes)
              VALUES ($student_id, '$date', '$status', '$notes')
              ON DUPLICATE KEY UPDATE
              status='$status', notes='$notes'";

    mysqli_query($conn, $query);
    header("Location: student_attendance.php?status=saved");
    exit;
}

// Handle Bulk Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_attendance'])) {
    $date = mysqli_real_escape_string($conn, $_POST['bulk_date']);
    $status = mysqli_real_escape_string($conn, $_POST['bulk_status']);

    $students = mysqli_query($conn, "SELECT id FROM students");
    while ($student = mysqli_fetch_assoc($students)) {
        $query = "INSERT INTO attendance (student_id, date, status)
                  VALUES ({$student['id']}, '$date', '$status')
                  ON DUPLICATE KEY UPDATE status='$status'";
        mysqli_query($conn, $query);
    }

    header("Location: student_attendance.php?status=bulk_saved");
    exit;
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-check"></i>
                    Student Attendance
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Track and manage student attendance records</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="controls">
                <div class="form-group">
                    <label for="dateFilter">Select Date:</label>
                    <input type="date" id="dateFilter" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" onclick="markAllPresent()">
                        <i class="fas fa-check-circle"></i> Mark All Present
                    </button>
                    <button class="btn btn-warning" onclick="markAllAbsent()">
                        <i class="fas fa-times-circle"></i> Mark All Absent
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Attendance Form -->
        <div class="card">
            <h3><i class="fas fa-calendar-check"></i> Bulk Attendance Entry</h3>
            <form method="POST" class="form-inline">
                <div class="form-group">
                    <label for="bulk_date">Date:</label>
                    <input type="date" name="bulk_date" id="bulk_date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="bulk_status">Status:</label>
                    <select name="bulk_status" id="bulk_status" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                        <option value="Excused">Excused</option>
                    </select>
                </div>

                <button type="submit" name="bulk_attendance" class="btn btn-primary">
                    <i class="fas fa-save"></i> Apply to All Students
                </button>
            </form>
        </div>

        <!-- Individual Attendance -->
        <div class="card">
            <h3><i class="fas fa-user-check"></i> Individual Attendance</h3>
            <div class="table-container">
                <table class="table" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                        $students_res = mysqli_query($conn, "
                            SELECT s.*, a.status, a.notes
                            FROM students s
                            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$selected_date'
                            ORDER BY s.fullname
                        ");
                        while($row = mysqli_fetch_assoc($students_res)):
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td><span class="class-badge"><?= htmlspecialchars($row['class'] ?: 'Not assigned') ?></span></td>
                            <td>
                                <select class="status-select" data-student-id="<?= $row['id'] ?>" data-date="<?= $selected_date ?>">
                                    <option value="Present" <?= ($row['status'] == 'Present') ? 'selected' : '' ?>>Present</option>
                                    <option value="Absent" <?= ($row['status'] == 'Absent') ? 'selected' : '' ?>>Absent</option>
                                    <option value="Late" <?= ($row['status'] == 'Late') ? 'selected' : '' ?>>Late</option>
                                    <option value="Excused" <?= ($row['status'] == 'Excused') ? 'selected' : '' ?>>Excused</option>
                                </select>
                            </td>
                            <td>
                                <input type="text"
                                       class="notes-input"
                                       data-student-id="<?= $row['id'] ?>"
                                       data-date="<?= $selected_date ?>"
                                       value="<?= htmlspecialchars($row['notes'] ?: '') ?>"
                                       placeholder="Optional notes">
                            </td>
                            <td class="action-col">
                                <button class="btn-icon btn-success" onclick="saveAttendance(<?= $row['id'] ?>, '<?= $selected_date ?>')">
                                    <i class="fas fa-save"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> Today's Summary</h3>
            <div class="stats-grid">
                <?php
                $today = date('Y-m-d');
                $present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE date='$today' AND status='Present'"))['count'];
                $absent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE date='$today' AND status='Absent'"))['count'];
                $late = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE date='$today' AND status='Late'"))['count'];
                $excused = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE date='$today' AND status='Excused'"))['count'];
                $total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'];
                ?>
                <div class="stat-card">
                    <div class="stat-number present"><?= $present ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number absent"><?= $absent ?></div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number late"><?= $late ?></div>
                    <div class="stat-label">Late</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number excused"><?= $excused ?></div>
                    <div class="stat-label">Excused</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number total"><?= $total_students ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('dateFilter').addEventListener('change', function() {
    const date = this.value;
    window.location.href = `student_attendance.php?date=${date}`;
});

function markAllPresent() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = 'Present';
    });
}

function markAllAbsent() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = 'Absent';
    });
}

function saveAttendance(studentId, date) {
    const status = document.querySelector(`select[data-student-id="${studentId}"][data-date="${date}"]`).value;
    const notes = document.querySelector(`input[data-student-id="${studentId}"][data-date="${date}"]`).value;

    fetch('save_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `student_id=${studentId}&date=${date}&status=${status}&notes=${encodeURIComponent(notes)}`
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            showNotification('Attendance saved successfully!', 'success');
        } else {
            showNotification('Error saving attendance.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving attendance.', 'error');
    });
}

function showNotification(message, type) {
    // Simple notification - you can enhance this
    alert(message);
}
</script>

<style>
.form-inline {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.status-select, .notes-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
}

.status-select:focus, .notes-input:focus {
    outline: none;
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.class-badge {
    background: #dbeafe;
    color: #1e40af;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.stat-card {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #e2e8f0;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-number.present { color: #22c55e; }
.stat-number.absent { color: #ef4444; }
.stat-number.late { color: #f59e0b; }
.stat-number.excused { color: #8b5cf6; }
.stat-number.total { color: #6b7280; }

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
}
</style>
<?php include 'includes/footer.php'; ?>
