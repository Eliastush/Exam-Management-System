<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system") or die("Connection failed");

// Get current user info
$user_name = $_SESSION['admin_name'] ?? 'Administrator';
$user_email = $_SESSION['admin_email'] ?? 'admin@school.edu';
$user_role = 'System Administrator';

// System stats
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'] ?? 0;
$total_questions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM questions"))['c'] ?? 0;
$total_results = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM results"))['c'] ?? 0;

// Get settings from database
$settings = [];
$settings_res = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings");
while ($row = mysqli_fetch_assoc($settings_res)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$school_name     = $settings['school_name'] ?? 'Mustard Seed International Schools';
$site_title      = $settings['site_title'] ?? 'ICT Quiz System';
$principal_name  = $settings['principal_name'] ?? 'Not Set';
$pass_mark       = $settings['pass_mark'] ?? '50';
$max_questions   = $settings['max_questions'] ?? '10';
$time_limit      = $settings['time_limit'] ?? '30';
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user"></i>
                    My Profile
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Administrator profile and system overview</p>
            </div>
        </div>

        <!-- Profile Info -->
        <div class="profile-main">
            <div class="admin-avatar">
                <i class="fas fa-user-shield"></i>
            </div>

            <div class="profile-info">
                <h2><?= htmlspecialchars($user_name) ?></h2>
                <p class="role"><?= htmlspecialchars($user_role) ?></p>
                <p class="email"><?= htmlspecialchars($user_email) ?></p>
                <div class="meta">
                    <span><i class="fas fa-clock"></i> Last active: <?= date('g:i A') ?></span>
                    <span class="live-time" id="liveClock"></span>
                </div>
            </div>

            <div class="status-badge">
                <span class="online-dot"></span> ONLINE
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="overview">Overview</button>
            <button class="tab-btn" data-tab="security">Security</button>
            <button class="tab-btn" data-tab="system">System Settings</button>
            <button class="tab-btn" data-tab="actions">Quick Actions</button>
        </div>

        <!-- OVERVIEW TAB -->
        <div class="tab-content active" id="tab-overview">
            <div class="stats-grid">
                <div class="card">
                    <i class="fas fa-users"></i>
                    <div>
                        <div class="stat-value"><?= number_format($total_students) ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                <div class="card">
                    <i class="fas fa-question-circle"></i>
                    <div>
                        <div class="stat-value"><?= number_format($total_questions) ?></div>
                        <div class="stat-label">Questions</div>
                    </div>
                </div>
                <div class="card">
                    <i class="fas fa-chart-bar"></i>
                    <div>
                        <div class="stat-value"><?= number_format($total_results) ?></div>
                        <div class="stat-label">Assessments</div>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <h3>School Information</h3>
                <p><strong>School Name:</strong> <?= htmlspecialchars($school_name) ?></p>
                <p><strong>Principal:</strong> <?= htmlspecialchars($principal_name) ?></p>
                <p><strong>System Title:</strong> <?= htmlspecialchars($site_title) ?></p>
            </div>
        </div>

        <!-- SECURITY TAB -->
        <div class="tab-content" id="tab-security">
            <div class="card-section">
                <h3>Security Settings</h3>
                <div class="security-grid">
                    <div class="security-card">
                        <i class="fas fa-key"></i>
                        <div class="info">
                            <h4>Password</h4>
                            <p>Last changed: 45 days ago</p>
                        </div>
                        <button class="btn" onclick="openPasswordModal()">Change Password</button>
                    </div>
                    <div class="security-card">
                        <i class="fas fa-mobile-alt"></i>
                        <div class="info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Status: <strong>Inactive</strong></p>
                        </div>
                        <button class="btn" onclick="enableTwoFactor()">Enable 2FA</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SYSTEM SETTINGS TAB -->
        <div class="tab-content" id="tab-system">
            <div class="card-section">
                <h3>System Information</h3>
                <div class="system-grid">
                    <div class="info-row">
                        <span>Pass Mark</span>
                        <strong><?= $pass_mark ?>%</strong>
                    </div>
                    <div class="info-row">
                        <span>Max Questions per Quiz</span>
                        <strong><?= $max_questions ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Default Time Limit</span>
                        <strong><?= $time_limit ?> minutes</strong>
                    </div>
                    <div class="info-row">
                        <span>PHP Version</span>
                        <strong><?= phpversion() ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Server Software</span>
                        <strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE']) ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Current Time</span>
                        <strong id="systemTime"><?= date('F j, Y g:i:s A') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS TAB -->
        <div class="tab-content" id="tab-actions">
            <div class="actions-grid">
                <a href="settings.php" class="action-card">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
                <a href="reports.php" class="action-card">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
                <a href="manage_students.php" class="action-card">
                    <i class="fas fa-users"></i>
                    <span>Manage Students</span>
                </a>
                <a href="ict_questions.php" class="action-card">
                    <i class="fas fa-question-circle"></i>
                    <span>Question Bank</span>
                </a>
                <a href="logout.php" class="action-card danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile specific styles */
.profile-main {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 30px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
}

.admin-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: white;
    border: 4px solid white;
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.25);
}

.profile-info h2 {
    margin: 0 0 6px 0;
    font-size: 24px;
    color: var(--text-primary);
}

.role {
    color: var(--primary);
    font-weight: 600;
    margin: 0 0 4px 0;
}

.email {
    color: var(--text-secondary);
    margin: 0 0 12px 0;
}

.meta {
    color: var(--text-muted);
    font-size: 14px;
}

.live-time {
    font-family: monospace;
    color: var(--primary);
}

.status-badge {
    margin-left: auto;
    background: #dcfce7;
    color: #166534;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.online-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Tabs */
.tabs {
    display: flex;
    background: var(--bg-light);
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
}

.tab-btn {
    padding: 14px 24px;
    background: none;
    border: none;
    color: var(--text-secondary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: var(--primary);
    border-bottom: 3px solid var(--primary);
}

.tab-btn:hover {
    color: var(--primary);
}

/* Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: var(--bg-white);
    padding: 20px;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    display: flex;
    gap: 16px;
    align-items: center;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.card i {
    font-size: 32px;
    color: var(--primary);
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-label {
    color: var(--text-secondary);
    font-size: 14px;
}

/* Security and System grids */
.security-grid, .system-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}

.security-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: var(--bg-light);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.security-card i {
    font-size: 24px;
    color: var(--primary);
}

.info h4 {
    margin: 0 0 4px 0;
    color: var(--text-primary);
}

.info p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 14px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.info-row:last-child {
    border-bottom: none;
}

/* Actions */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
}

.action-card {
    background: var(--bg-white);
    border: 1px solid var(--border);
    padding: 20px 16px;
    border-radius: var(--radius);
    text-align: center;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.action-card:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.action-card i {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
    color: var(--primary);
}

.action-card span {
    font-weight: 600;
    font-size: 14px;
}

.action-card.danger:hover {
    background: #fef2f2;
    color: var(--danger);
    border-color: var(--danger);
}

.action-card.danger:hover i {
    color: var(--danger);
}
</style>

<script>
// Live Clock
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true
    });
    document.getElementById('liveClock').textContent = timeString;
    document.getElementById('systemTime').textContent = timeString;
}
setInterval(updateClock, 1000);
updateClock();

// Tab functionality
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

function openPasswordModal() {
    alert("Password change feature coming soon.");
}

function enableTwoFactor() {
    alert("Two-Factor Authentication setup coming soon.");
}
</script>

<?php include 'Includes/footer.php'; ?>