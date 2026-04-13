<?php $site_logo = $system_settings['site_logo'] ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS4GGqCSG91HLyo8h_2kjnEuQQyRJ_JiRyG6Q&s';?>

<div class="sidebar" id="sidebar">

    <!-- LOGO -->
<div class="logo">
    <img src="<?= htmlspecialchars($site_logo) ?>" alt="Logo" class="logo-img">
    <h3 class="logo-text"><?= htmlspecialchars($site_title) ?></h3>
</div>

    <!-- NAVIGATION -->
<nav class="nav">

    <!-- Dashboard -->
    <a href="dashboard.php" class="nav-link active">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>

    <!-- Students Dropdown -->
    <div class="nav-dropdown">
        <a href="#" class="nav-link dropdown-toggle">
            <i class="fas fa-users"></i>
            <span>Students</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <div class="submenu">
            <a href="add_student.php"><i class="fas fa-user-plus"></i> </a>
            <a href="manage_students.php"><i class="fas fa-list"></i> </a>
            <a href="student_attendance.php"><i class="fas fa-calendar-check"></i> </a>
        </div>
    </div>

    <!-- Teachers Dropdown -->
    <div class="nav-dropdown">
        <a href="#" class="nav-link dropdown-toggle">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Teachers</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <div class="submenu">
            <a href="add_teacher.php"><i class="fas fa-user-plus"></i> Add Teacher</a>
            <a href="manage_teachers.php"><i class="fas fa-list"></i> Manage Teachers</a>
            <a href="teacher_schedule.php"><i class="fas fa-clock"></i> Schedule</a>
        </div>
    </div>

    <!-- ICT Questions -->
    <a href="ict_questions.php" class="nav-link">
        <i class="fas fa-laptop-code"></i>
        <span>ICT Questions</span>
    </a>

    <!-- Exams & Results -->
    <a href="exams_results.php" class="nav-link">
        <i class="fas fa-chart-bar"></i>
        <span>Exams & Results</span>
    </a>

    <!-- Settings -->
    <a href="settings.php" class="nav-link">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
    </a>

    <!-- Reports -->
    <a href="reports.php" class="nav-link">
        <i class="fas fa-file-alt"></i>
        <span>Reports</span>
    </a>

</nav>


    <div class="system-health-card">
    <h4>System Health</h4><hr>
    <div class="health-bar">
        <span>CPU</span>
        <div class="bar"><div class="fill cpu-fill"></div></div>
    </div>
    <div class="health-bar">
        <span>Memory</span>
        <div class="bar"><div class="fill mem-fill"></div></div>
    </div>
</div>
</div>



<script>
// Sidebar toggle
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

// Dropdowns
document.querySelectorAll(".dropdown-toggle").forEach(btn => {
    btn.addEventListener("click", function(e) {
        e.preventDefault();
        this.parentElement.classList.toggle("open");
    });
});

// Animate CPU and Memory usage dynamically
const cpuFill = document.querySelector('.cpu-fill');
const memFill = document.querySelector('.mem-fill');

function animateHealth() {
    const cpuUsage = Math.floor(Math.random() * (85 - 20 + 1)) + 20;  // 20-85%
    const memUsage = Math.floor(Math.random() * (85 - 20 + 1)) + 20;

    cpuFill.style.width = cpuUsage + '%';
    memFill.style.width = memUsage + '%';

    // Color based on value - using CSS variables
    const successColor = '#16a34a';
    const warningColor = '#f59e0b';
    const dangerColor = '#ef4444';
    const infoColor = '#3b82f6';
    
    cpuFill.style.background = cpuUsage < 50 ? successColor : cpuUsage < 70 ? warningColor : dangerColor;
    memFill.style.background = memUsage < 50 ? infoColor : memUsage < 70 ? warningColor : dangerColor;
}

// Animate every 500ms
animateHealth();
setInterval(animateHealth, 500);
</script>