<?php
session_start();
$conn = mysqli_connect("localhost","root","","quiz_system");
if(!$conn) die("Connection failed");

// Fetch settings
$settings_res = mysqli_query($conn,"SELECT * FROM settings");
$system_settings = [];
while($row = mysqli_fetch_assoc($settings_res)){
    $system_settings[$row['setting_key']] = $row['setting_value'];
}

// Apply settings
$site_title = $system_settings['site_title'] ?? "Mustard Seed ICT Dashboard";
$site_logo = $system_settings['site_logo'] ?? '';
$school_name = $system_settings['school_name'] ?? $site_title;
$theme = $system_settings['theme'] ?? 'light';
$primary_color = $system_settings['primary_color'] ?? '#22c55e';
$enable_registration = ($system_settings['enable_registration'] ?? 1)==1;
$email_notifications = ($system_settings['email_notifications'] ?? 1)==1;
$result_notifications = ($system_settings['result_notifications'] ?? 1)==1;
$max_questions = intval($system_settings['max_questions'] ?? 10);
$time_limit = intval($system_settings['time_limit'] ?? 30);
$shuffle_questions = ($system_settings['shuffle_questions'] ?? 0)==1;
$user_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';
$user_email = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@school.edu';
$user_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'System Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($site_title) ?></title>

<!-- CSS -->
<link rel="stylesheet" href="Includes/style.css">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dynamic Theme -->
<style>
:root {
    --primary: <?= $primary_color ?>;
    --button-bg: <?= $primary_color ?>;
    --primary-dark: <?= $primary_color ?>;
}
</style>
</head>
<body class="<?= $theme=='dark'?'dark':'' ?>">

<!-- HEADER -->
<header class="header">
    <div class="header-left">
        <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
        <div class="search-box">
    <i class="fas fa-search"></i>
    <input type="text" id="globalSearch" placeholder="Search quizzes, students, questions..." autocomplete="off">
    
    <!-- Results Dropdown -->
    <div id="searchResults" class="search-dropdown"></div>
</div>
    </div>
    <div class="header-right">
        <i class="fas fa-moon icon-btn" onclick="toggleDarkMode()"></i>
        <div class="notification-btn" onclick="toggleNotifications()">
            <i class="fas fa-bell icon-btn"></i>
            <span class="beta-notification-label">Beta</span>
        </div>
        <div class="user-profile" onclick="toggleUserDropdown()">
            <i class="fas fa-user-circle user-icon"></i>
            <div class="user-text">
                <strong><?= htmlspecialchars($user_name) ?></strong>
                <small><?= htmlspecialchars($user_role) ?></small>
            </div>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
        </div>
    </div>
</header>

<!-- NOTIFICATION DROPDOWN -->
<div id="notificationDropdown" class="dropdown-panel notification-panel">
    <div class="notification-header">
        <h4><i class="fas fa-bell"></i> Notifications</h4>
        <button class="mark-all-btn" onclick="markAllAsRead()" title="Mark all as read">
            <i class="fas fa-check-double"></i> Mark all as read
        </button>
    </div>
    <div id="notificationList">
        <div class="notification-item loading">
            <i class="fas fa-spinner fa-spin"></i> Loading notifications...
        </div>
    </div>
    <div class="notification-footer">
        <a href="javascript:void(0)" onclick="window.location='dashboard.php'; toggleNotifications();">
            View all activity
        </a>
    </div>
</div>

<!-- USER PROFILE DROPDOWN -->
<div id="userDropdown" class="dropdown-panel user-dropdown-panel">
    <div class="user-info">
        <i class="fas fa-user-circle"></i>
        <div>
            <strong><?= htmlspecialchars($user_name) ?></strong>
            <small><?= htmlspecialchars($user_role) ?></small>
        </div>
    </div>
    <hr>
    <ul>
        <li onclick="window.location='my_profile.php'">
            <i class="fas fa-user"></i> My Profile
        </li>
        <li onclick="window.location='settings.php'">
            <i class="fas fa-cog"></i> Settings
        </li>
        <li onclick="toggleDarkMode()">
            <i class="fas fa-moon"></i> Toggle Theme
        </li>
        <hr>
        <li onclick="window.location='logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </li>
    </ul>
</div>

<!-- SIDEBAR -->
<?php include 'Includes/sidebar.php'; ?>

<!-- JS -->
<script>
// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const footer = document.querySelector('.footer');

    if (window.innerWidth <= 1024) {
        // Mobile: toggle open class
        sidebar.classList.toggle('open');
    } else {
        // Desktop: toggle collapsed class
        sidebar.classList.toggle('collapsed');
        if (mainContent) {
            mainContent.classList.toggle('expanded');
        }
        if (footer) {
            footer.classList.toggle('expanded');
        }
    }
}

// Dark mode toggle
function toggleDarkMode() {
    document.body.classList.toggle('dark');
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
}

// Notification dropdown toggle
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    const userDropdown = document.getElementById('userDropdown');

    userDropdown.classList.remove('show');
    dropdown.classList.toggle('show');

    if (dropdown.classList.contains('show')) {
        loadNotifications();
    }
}

// User dropdown toggle
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    const notificationDropdown = document.getElementById('notificationDropdown');

    notificationDropdown.classList.remove('show');
    dropdown.classList.toggle('show');
}

// Load notifications
async function loadNotifications() {
    const notificationList = document.getElementById('notificationList');

    try {
        const response = await fetch('includes/get_notifications.php');
        const data = await response.json();

        if (data.success && data.notifications.length > 0) {
            notificationList.innerHTML = data.notifications.map((notif, idx) => `
                <div class="notification-item" data-id="${idx}">
                    <div class="notif-icon ${notif.type || 'default'}">
                        <i class="${notif.icon}"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">${notif.title}</div>
                        <div class="notif-subtitle">${notif.activity}</div>
                        <div class="notif-meta">
                            <span class="notif-actor">${notif.actor}</span>
                            <span class="notif-time">${notif.time}</span>
                        </div>
                    </div>
                    <div class="notif-actions">
                        <a href="${notif.url}" class="notif-link" title="View details">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            `).join('');
        } else {
            notificationList.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-inbox"></i>
                    <p>No notifications yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Notification load error:', error);
        notificationList.innerHTML = `
            <div class="notification-error">
                <i class="fas fa-exclamation-circle"></i>
                <p>Failed to load notifications</p>
            </div>
        `;
    }
}

// Mark all notifications as read
function markAllAsRead() {
    const notificationList = document.getElementById('notificationList');
    
    // Visual feedback
    const items = notificationList.querySelectorAll('.notification-item:not(.loading)');
    items.forEach(item => {
        item.classList.add('read');
    });
    
    // Show "all marked as read" message
    setTimeout(() => {
        showToast('âœ“ All notifications marked as read', 'success');
    }, 300);
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userDropdown = document.getElementById('userDropdown');
    const notificationBtn = document.querySelector('.notification-btn');
    const userProfile = document.querySelector('.user-profile');

    if (notificationBtn && !notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
        notificationDropdown.classList.remove('show');
    }

    if (userProfile && !userProfile.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('show');
    }
});

// Load theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark');
    }
    loadNotifications(); // Load notifications on page load
});
</script>



<?php include 'Includes/footer.php'; ?>
