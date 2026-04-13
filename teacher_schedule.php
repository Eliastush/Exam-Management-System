<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// Handle Schedule Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $day = mysqli_real_escape_string($conn, $_POST['day']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $room = mysqli_real_escape_string($conn, $_POST['room']);

    $query = "INSERT INTO teacher_schedule (teacher_id, subject, day, start_time, end_time, room)
              VALUES ($teacher_id, '$subject', '$day', '$start_time', '$end_time', '$room')
              ON DUPLICATE KEY UPDATE
              subject='$subject', start_time='$start_time', end_time='$end_time', room='$room'";

    mysqli_query($conn, $query);
    header("Location: teacher_schedule.php?status=saved");
    exit;
}

// Handle Delete Schedule
if (isset($_GET['delete_schedule'])) {
    $id = intval($_GET['delete_schedule']);
    mysqli_query($conn, "DELETE FROM teacher_schedule WHERE id = $id");
    header("Location: teacher_schedule.php?status=deleted");
    exit;
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt"></i>
                    Teacher Schedule
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Manage and track teacher class schedules</p>
            </div>
        </div>

        <!-- Add Schedule Form -->
        <div class="card">
            <h3><i class="fas fa-calendar-plus"></i> Add/Edit Schedule</h3>
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <label for="teacher_id">Teacher:</label>
                    <select name="teacher_id" id="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php
                        $teachers = mysqli_query($conn, "SELECT id, fullname FROM teachers ORDER BY fullname");
                        while ($teacher = mysqli_fetch_assoc($teachers)) {
                            echo '<option value="' . $teacher['id'] . '">' . htmlspecialchars($teacher['fullname']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" name="subject" id="subject" required>
                </div>

                <div class="form-group">
                    <label for="day">Day:</label>
                    <select name="day" id="day" required>
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time:</label>
                    <input type="time" name="start_time" id="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time:</label>
                    <input type="time" name="end_time" id="end_time" required>
                </div>

                <div class="form-group">
                    <label for="room">Room/Class:</label>
                    <input type="text" name="room" id="room" placeholder="e.g., Room 101" required>
                </div>

                <div class="form-group full-width">
                    <button type="submit" name="save_schedule" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                </div>
            </form>
        </div>

        <!-- Schedule Table -->
        <div class="card">
            <h3><i class="fas fa-calendar-alt"></i> Current Schedules</h3>
            <div class="table-container">
                <table class="table" id="scheduleTable">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $schedule_res = mysqli_query($conn, "
                            SELECT ts.*, t.fullname
                            FROM teacher_schedule ts
                            JOIN teachers t ON ts.teacher_id = t.id
                            ORDER BY FIELD(ts.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), ts.start_time
                        ");
                        while($row = mysqli_fetch_assoc($schedule_res)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td><span class="subject-badge"><?= htmlspecialchars($row['subject']) ?></span></td>
                            <td><span class="day-badge"><?= htmlspecialchars($row['day']) ?></span></td>
                            <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                            <td><span class="room-badge"><?= htmlspecialchars($row['room']) ?></span></td>
                            <td class="action-col">
                                <button class="btn-icon" title="Edit" onclick="editSchedule(<?= $row['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete_schedule=<?= $row['id'] ?>"
                                   class="btn-icon btn-danger"
                                   onclick="return confirm('Delete this schedule entry?')"
                                   title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Weekly View -->
        <div class="card">
            <h3><i class="fas fa-calendar-week"></i> Weekly Schedule Overview</h3>
            <div class="weekly-schedule">
                <?php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($days as $day):
                ?>
                <div class="day-column">
                    <h4 class="day-header"><?= $day ?></h4>
                    <div class="day-schedule">
                        <?php
                        $day_schedule = mysqli_query($conn, "
                            SELECT ts.*, t.fullname
                            FROM teacher_schedule ts
                            JOIN teachers t ON ts.teacher_id = t.id
                            WHERE ts.day = '$day'
                            ORDER BY ts.start_time
                        ");
                        while ($schedule = mysqli_fetch_assoc($day_schedule)):
                        ?>
                        <div class="schedule-item">
                            <div class="time-slot">
                                <?= htmlspecialchars($schedule['start_time']) ?> - <?= htmlspecialchars($schedule['end_time']) ?>
                            </div>
                            <div class="class-info">
                                <strong><?= htmlspecialchars($schedule['fullname']) ?></strong><br>
                                <small><?= htmlspecialchars($schedule['subject']) ?> - <?= htmlspecialchars($schedule['room']) ?></small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($day_schedule) == 0): ?>
                        <div class="no-classes">No classes scheduled</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function editSchedule(id) {
    // Load schedule data and populate form
    fetch(`get_schedule.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('teacher_id').value = data.teacher_id;
            document.getElementById('subject').value = data.subject;
            document.getElementById('day').value = data.day;
            document.getElementById('start_time').value = data.start_time;
            document.getElementById('end_time').value = data.end_time;
            document.getElementById('room').value = data.room;
            document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => console.error('Error loading schedule:', error));
}
</script>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.subject-badge, .day-badge, .room-badge {
    background: #dbeafe;
    color: #1e40af;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.day-badge {
    background: #fef3c7;
    color: #92400e;
}

.room-badge {
    background: #d1fae5;
    color: #065f46;
}

.weekly-schedule {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1rem;
}

.day-column {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.day-header {
    background: #f8fafc;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}

.day-schedule {
    padding: 0.5rem;
    min-height: 200px;
}

.schedule-item {
    background: #f1f5f9;
    border-radius: 6px;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
}

.time-slot {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}

.class-info strong {
    color: #1f2937;
}

.no-classes {
    color: #9ca3af;
    font-style: italic;
    text-align: center;
    padding: 2rem 0;
}
</style>