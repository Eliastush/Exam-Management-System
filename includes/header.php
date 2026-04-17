<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF'] ?? '');
if ($current_page !== 'login.php' && !isset($_SESSION['username']) && !isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit;
}

$conn = mysqli_connect('localhost', 'root', '', 'quiz_system');
$system_settings = [];
if ($conn) {
    $settings_res = mysqli_query($conn, 'SELECT setting_key, setting_value FROM settings');
    if ($settings_res) {
        while ($row = mysqli_fetch_assoc($settings_res)) {
            $system_settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}


$site_logo = $system_settings['site_logo'] ?? 'msis logo.png';
$school_name = $system_settings['school_name'] ?? $site_title;
$site_title = $school_name; /* $system_settings['site_title'] ?? 'Mustard Seed ICT Dashboard';*/
$theme = $system_settings['theme'] ?? 'light';
$primary_color = $system_settings['primary_color'] ?? '#1f8f63';
$user_name = $_SESSION['admin_name'] ?? $_SESSION['username'] ?? 'Administrator';
$user_email = $_SESSION['admin_email'] ?? 'info@school.com';
$user_role = $_SESSION['admin_role'] ?? 'System Administrator';

function hex_to_rgb_csv(string $hex): string {
    $hex = ltrim(trim($hex), '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return '31, 143, 99';
    }

    return implode(', ', [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ]);
}

$primary_rgb = hex_to_rgb_csv($primary_color);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($site_title) ?></title>
<link rel="stylesheet" href="includes/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --primary-color: <?= htmlspecialchars($primary_color) ?>;
    --primary-rgb: <?= htmlspecialchars($primary_rgb) ?>;
}
</style>
</head>
<body class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<div id="pageLoader" class="page-loader">
    <div class="page-loader-card">
        <div class="page-loader-logo-wrap">
            <img src="<?= htmlspecialchars($site_logo) ?>" alt="<?= htmlspecialchars($school_name) ?> logo" class="page-loader-logo">
        </div>
        <strong><?= htmlspecialchars($school_name) ?></strong>
        <span>Loading <?= htmlspecialchars($user_name) ?> Experience...</span>
        <div class="page-loader-bar"><div class="page-loader-progress"></div></div>
    </div>
</div>
<header class="header">
    <div class="header-left">
        <button class="icon-toggle" type="button" onclick="toggleSidebar()" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-brand">
            <span class="header-kicker">School Analytics</span>
            <strong><?= htmlspecialchars($school_name) ?></strong>
        </div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Search students, results, questions..." autocomplete="off">
            <div id="searchResults" class="search-dropdown"></div>
        </div>
    </div>
    <div class="header-right">
        <!-- <button class="icon-toggle" type="button" onclick="toggleDarkMode()" aria-label="Toggle theme">
            <i class="fas fa-moon"></i>
        </button> -->
        <div class="notification-btn" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span id="notificationCount" class="notification-pill">0</span>
        </div>
        <div class="user-profile" onclick="toggleUserDropdown()">
            <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
            <div class="user-text">
                <strong><?= htmlspecialchars($user_name) ?></strong><br>
                <small><?= htmlspecialchars($user_role) ?></small>
            </div>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
        </div>
    </div>
</header>

<div id="notificationDropdown" class="dropdown-panel notification-panel">
    <div class="panel-header">
        <h4><i class="fas fa-bell"></i> Recent Activity</h4>
        <button class="btn btn-secondary btn-sm" type="button" onclick="markAllAsRead()">Mark all read</button>
    </div>
    <div id="notificationList" class="panel-body">
        <div class="notification-item loading">Loading notifications...</div>
    </div>
</div>

<div id="userDropdown" class="dropdown-panel user-dropdown-panel">
    <div class="user-dropdown-top">
        <strong><?= htmlspecialchars($user_name) ?></strong> <br>
        <small><?= htmlspecialchars($user_email) ?></small>
    </div>
    <a href="my_profile.php"><i class="fas fa-user"></i> My Profile</a>
    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
    <!-- <button type="button" onclick="toggleDarkMode()"><i class="fas fa-circle-half-stroke"></i> Toggle Theme</button> -->
    <a href="logout.php" class="danger-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<?php include 'includes/sidebar.php'; ?>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    if (window.innerWidth <= 1024) {
        sidebar.classList.toggle('open');
        return;
    }
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed', sidebar.classList.contains('collapsed'));
}

function toggleDarkMode() {
    const isDark = document.body.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    document.getElementById('userDropdown')?.classList.remove('show');
    dropdown?.classList.toggle('show');
    if (dropdown?.classList.contains('show')) loadNotifications();
}

function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    document.getElementById('notificationDropdown')?.classList.remove('show');
    dropdown?.classList.toggle('show');
}

async function loadNotifications() {
    const list = document.getElementById('notificationList');
    const count = document.getElementById('notificationCount');
    if (!list) return;
    list.innerHTML = '<div class="notification-item loading">Loading notifications...</div>';
    try {
        const response = await fetch('includes/get_notifications.php');
        const data = await response.json();
        const notifications = Array.isArray(data.notifications) ? data.notifications : [];
        if (count) count.textContent = notifications.length;
        if (!notifications.length) {
            list.innerHTML = '<div class="notification-item empty">No notifications yet.</div>';
            return;
        }
        list.innerHTML = notifications.map((notification) => `
            <a class="notification-item" href="${notification.url || '#'}">
                <span class="notif-icon ${notification.type || 'default'}"><i class="${notification.icon || 'fas fa-bell'}"></i></span>
                <span class="notif-copy">
                    <strong>${notification.title || 'Update'}</strong>
                    <small>${notification.activity || ''}</small>
                    <em>${notification.time || 'Recently'}</em>
                </span>
            </a>
        `).join('');
    } catch (error) {
        console.error(error);
        list.innerHTML = '<div class="notification-item empty">Failed to load notifications.</div>';
    }
}

function markAllAsRead() {
    document.querySelectorAll('#notificationList .notification-item').forEach((item) => item.classList.add('read'));
}

document.addEventListener('click', function(event) {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationButton = document.querySelector('.notification-btn');
    const userDropdown = document.getElementById('userDropdown');
    const userProfile = document.querySelector('.user-profile');
    if (notificationDropdown && notificationButton && !notificationDropdown.contains(event.target) && !notificationButton.contains(event.target)) {
        notificationDropdown.classList.remove('show');
    }
    if (userDropdown && userProfile && !userDropdown.contains(event.target) && !userProfile.contains(event.target)) {
        userDropdown.classList.remove('show');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
    }
    loadNotifications();
});

window.addEventListener('load', function() {
    document.body.classList.add('page-ready');
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => loader.classList.add('hide'), 180);
        setTimeout(() => loader.remove(), 650);
    }
});
</script>
