<?php
$current_nav = basename($_SERVER['PHP_SELF'] ?? '');

function nav_active(array $pages, string $current): string {
    return in_array($current, $pages, true) ? 'active' : '';
}

$today_students = 0;
$today_results = 0;
$today_questions = 0;

if (isset($conn) && $conn) {
    $today_students = (int) ((mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM students'))['total'] ?? 0));
    $today_results = (int) ((mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM results'))['total'] ?? 0));
    $today_questions = (int) ((mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM questions'))['total'] ?? 0));
}

$page_titles = [
    'dashboard.php' => 'System pulse',
    'manage_students.php' => 'Student desk',
    'add_student.php' => 'Student intake',
    'edit_student.php' => 'Student editing',
    'student_attendance.php' => 'Attendance flow',
    'manage_teachers.php' => 'Teacher desk',
    'add_teacher.php' => 'Teacher intake',
    'teacher_schedule.php' => 'Schedule board',
    'ict_questions.php' => 'Question bank',
    'exams_results.php' => 'Results tracker',
    'reports.php' => 'Reporting room',
    'settings.php' => 'System tuning',
];

$today_focus = $page_titles[$current_nav] ?? 'Admin workspace';
?>
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($site_logo) ?>" alt="School logo" class="logo-img">
        <div class="logo-copy">
            <strong><?= htmlspecialchars($site_title) ?></strong>
            <small>Admin workspace</small>
        </div>
    </div>

    <nav class="nav">
        <a href="dashboard.php" class="nav-link <?= nav_active(['dashboard.php'], $current_nav) ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-dropdown <?= nav_active(['add_student.php', 'edit_student.php', 'manage_students.php', 'student_attendance.php', 'student_profile.php'], $current_nav) ?>">
            <button class="nav-link dropdown-toggle" type="button">
                <span class="nav-link-main">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </span>
                <i class="fas fa-chevron-right arrow"></i>
            </button>
            <div class="submenu">
                <nav class="admin-nav">
                    <a href="add_student.php" class="nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Student</span>
                    </a>
                    <a href="manage_students.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Manage Students</span>
                    </a>
                    <!-- <a href="student_attendance.php" class="nav-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a> -->
                </nav>
            </div>
        </div>

        <!-- <div class="nav-dropdown <?= nav_active(['add_teacher.php', 'manage_teachers.php', 'teacher_schedule.php'], $current_nav) ?>">
            <button class="nav-link dropdown-toggle" type="button">
                <span class="nav-link-main">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Teachers</span>
                </span>
                <i class="fas fa-chevron-right arrow"></i>
            </button>
            <div class="submenu">
                <a href="add_teacher.php">Add Teacher</a>
                <a href="manage_teachers.php">Manage Teachers</a>
                <a href="teacher_schedule.php">Schedule</a>
            </div>
        </div> -->

        <a href="ict_questions.php" class="nav-link <?= nav_active(['ict_questions.php', 'question_view.php'], $current_nav) ?>">
            <i class="fas fa-laptop-code"></i>
            <span>Question Bank</span>
        </a>

        <a href="exams_results.php" class="nav-link <?= nav_active(['exams_results.php', 'result_view.php'], $current_nav) ?>">
            <i class="fas fa-clipboard-check"></i>
            <span>Results</span>
        </a>

        <a href="reports.php" class="nav-link <?= nav_active(['reports.php'], $current_nav) ?>">
            <i class="fas fa-file-lines"></i>
            <span>Reports</span>
        </a>

        <a href="settings.php" class="nav-link <?= nav_active(['settings.php'], $current_nav) ?>">
            <i class="fas fa-sliders"></i>
            <span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-beta-card">
        <span class="sidebar-beta-label">You are here!</span>
        <strong><?= htmlspecialchars($today_focus) ?></strong>
        <p><?= date('l, d M Y') ?></p>
    </div>

    <div class="system-health-card">
        <div class="today-panel-header">
            <div>
                <span class="today-panel-label">Today</span>
                <h4><?= htmlspecialchars($today_focus) ?></h4>
            </div>
            <span class="today-panel-dot"></span>
        </div>
        <div class="today-metric-grid">
            <div class="today-metric">
                <span>Students</span>
                <strong><?= number_format($today_students) ?></strong>
            </div>
            <div class="today-metric">
                <span>Results</span>
                <strong><?= number_format($today_results) ?></strong>
            </div>
            <div class="today-metric">
                <span>Questions</span>
                <strong><?= number_format($today_questions) ?></strong>
            </div>
            <div class="today-metric">
                <span>Mode</span>
                <strong><?= $theme === 'dark' ? 'Dark' : 'Light' ?></strong>
            </div>
        </div>
        <div class="today-note">
<!-- Keyboard Shortcuts - Compact Version -->
<div class="keyboard-shortcuts">
    <h5><i class="fas fa-keyboard"></i> Keyboard Shortcuts</h5>
    
    <div class="shortcuts-grid">
        <div class="shortcut-item">
            <span class="key">Ctrl + S</span>
            <span class="action">Add Student</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + R</span>
            <span class="action">View Results</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + M</span>
            <span class="action">Manage Students</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + Q</span>
            <span class="action">Questions</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + P</span>
            <span class="action">My Profile</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + D</span>
            <span class="action">Dashboard</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + A</span>
            <span class="action">Attendance</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + E</span>
            <span class="action">Exams</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + T</span>
            <span class="action">Take Attendance</span>
        </div>
        <div class="shortcut-item">
            <span class="key">Ctrl + H</span>
            <span class="action">Help</span>
        </div>
    </div>

    <div class="global-search-hint">
        <span>Shortcut</span>
        <strong>Press <code>/</code> to search from anywhere</strong>
    </div>
</div>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dropdown-toggle').forEach((button) => {
        button.addEventListener('click', function() {
            this.parentElement.classList.toggle('open');
        });

        if (button.parentElement.classList.contains('active')) {
            button.parentElement.classList.add('open');
        }
    });
});
</script>
