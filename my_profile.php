<?php
include 'includes/header.php';

if (!$conn) {
    die('Database connection failed');
}

function profile_scalar(mysqli $conn, string $sql, string $field, $fallback = 0) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return $fallback;
    }
    $row = mysqli_fetch_assoc($result);
    return $row[$field] ?? $fallback;
}

$user_name = $_SESSION['admin_name'] ?? $_SESSION['username'] ?? 'Administrator';
$user_email = $_SESSION['admin_email'] ?? 'admin@school.edu';
$user_role = $_SESSION['admin_role'] ?? 'System Administrator';

$total_students = (int) profile_scalar($conn, "SELECT COUNT(*) AS total FROM students", 'total', 0);
$total_questions = (int) profile_scalar($conn, "SELECT COUNT(*) AS total FROM questions", 'total', 0);
$total_results = (int) profile_scalar($conn, "SELECT COUNT(*) AS total FROM results", 'total', 0);
$active_students = (int) profile_scalar($conn, "SELECT COUNT(DISTINCT student_name) AS total FROM results", 'total', 0);
$average_score = round((float) profile_scalar($conn, "SELECT AVG(score / total * 100) AS avg_score FROM results WHERE total > 0", 'avg_score', 0), 1);
$pass_rate = round((float) profile_scalar($conn, "SELECT AVG(CASE WHEN score / total * 100 >= 50 THEN 100 ELSE 0 END) AS pass_rate FROM results WHERE total > 0", 'pass_rate', 0), 1);

$settings = [];
$settings_res = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings");
if ($settings_res) {
    while ($row = mysqli_fetch_assoc($settings_res)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$school_name = $settings['school_name'] ?? 'Mustard Seed International Schools';
$site_title = $settings['site_title'] ?? 'Mustard Seed ICT Dashboard';
$principal_name = $settings['principal_name'] ?? 'Not Set';
$pass_mark = (int) ($settings['pass_mark'] ?? 50);
$max_questions = (int) ($settings['max_questions'] ?? 10);
$time_limit = (int) ($settings['time_limit'] ?? 30);
$theme_name = $settings['theme'] ?? 'light';
$primary_color = $settings['primary_color'] ?? '#1f8f63';

$coverage_ratio = $total_students > 0 ? round(($active_students / $total_students) * 100, 1) : 0;
$profile_headline = 'The platform is in active beta and improving quickly.';
if ($pass_rate < 50) {
    $profile_headline = 'Learner performance needs intervention, so the admin focus should stay on coaching and quiz quality.';
} elseif ($pass_rate < 70) {
    $profile_headline = 'Results are promising, but there is room to tighten support for mid-performing learners.';
} elseif ($total_questions < 80) {
    $profile_headline = 'Performance is stable; growing the question bank is the next high-value upgrade.';
}

$profile_digest = [
    "Your workspace currently serves {$total_students} students and {$total_results} recorded attempts, which means the dashboard is already carrying meaningful operational data.",
    "The latest average score is {$average_score}% with a {$pass_rate}% pass rate, so this beta is already useful for decision-making rather than simple record storage.",
    "Participation coverage is {$coverage_ratio}%, and that tells us how much of the student body is actively visible inside the analytics layer.",
    "The current system defaults of {$max_questions} questions, {$time_limit} minutes, and a {$pass_mark}% pass mark should stay visible because they shape every result the school sees."
];
?>

<div class="main-content">
    <div class="view-card profile-page-shell">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-shield"></i>
                    <?= htmlspecialchars($user_name) ?> Profile
                    <span class="beta-badge"><?= htmlspecialchars($user_name) ?> Workspace</span>
                </h1>
                <p class="page-subtitle">Administrator identity, live system posture, and the story behind this beta web app.</p>
            </div>
            <div class="header-actions">
                <a href="settings.php" class="btn btn-secondary"><i class="fas fa-sliders"></i> Edit Settings</a>
                <a href="reports.php" class="btn btn-primary"><i class="fas fa-chart-line"></i> Open Reports</a>
            </div>
        </div>

        <section class="profile-hero">
            <div class="profile-hero-main">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="profile-hero-copy">
                    <span class="profile-kicker">Admin Identity</span>
                    <h2><?= htmlspecialchars($user_name) ?></h2>
                    <p><?= htmlspecialchars($user_role) ?> for <?= htmlspecialchars($school_name) ?></p>
                    <div class="profile-meta-row">
                        <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($user_email) ?></span>
                        <span><i class="fas fa-clock"></i> <span id="liveClock"><?= date('F j, Y g:i:s A') ?></span></span>
                        <span><i class="fas fa-palette"></i> <?= htmlspecialchars(ucfirst($theme_name)) ?> theme</span>
                    </div>
                </div>
            </div>
            <div class="profile-hero-side">
                <span class="status-badge active"><span class="online-dot"></span> Online</span>
                <div class="profile-color-chip">
                    <span>Brand Color</span>
                    <strong><?= htmlspecialchars($primary_color) ?></strong>
                </div>
                <div class="profile-hero-note">
                    <span>Beta Readiness</span>
                    <strong><?= htmlspecialchars($profile_headline) ?></strong>
                </div>
            </div>
        </section>

        <div class="tabs">
            <button class="tab-btn active" data-tab="overview">Overview</button>
            <button class="tab-btn" data-tab="digest">AI Digest</button>
            <button class="tab-btn" data-tab="system">System Posture</button>
            <button class="tab-btn" data-tab="actions">Quick Actions</button>
        </div>

        <div class="tab-content active" id="tab-overview">
            <div class="stats-grid">
                <div class="card stat-card">
                    <i class="fas fa-users"></i>
                    <div>
                        <div class="stat-value"><?= number_format($total_students) ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                <div class="card stat-card">
                    <i class="fas fa-question-circle"></i>
                    <div>
                        <div class="stat-value"><?= number_format($total_questions) ?></div>
                        <div class="stat-label">Question Bank</div>
                    </div>
                </div>
                <div class="card stat-card">
                    <i class="fas fa-chart-column"></i>
                    <div>
                        <div class="stat-value"><?= number_format($total_results) ?></div>
                        <div class="stat-label">Recorded Attempts</div>
                    </div>
                </div>
                <div class="card stat-card">
                    <i class="fas fa-bullseye"></i>
                    <div>
                        <div class="stat-value"><?= number_format($average_score, 1) ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>
            </div>

            <div class="profile-story-grid">
                <div class="card profile-story-card">
                    <h3><i class="fas fa-building-columns"></i> School Identity</h3>
                    <div class="info-row"><span>School</span><strong><?= htmlspecialchars($school_name) ?></strong></div>
                    <div class="info-row"><span>Principal</span><strong><?= htmlspecialchars($principal_name) ?></strong></div>
                    <div class="info-row"><span>System Title</span><strong><?= htmlspecialchars($site_title) ?></strong></div>
                </div>
                <div class="card profile-story-card">
                    <h3><i class="fas fa-wave-square"></i> Beta Snapshot</h3>
                    <div class="info-row"><span>Participation Coverage</span><strong><?= number_format($coverage_ratio, 1) ?>%</strong></div>
                    <div class="info-row"><span>Current Pass Rate</span><strong><?= number_format($pass_rate, 1) ?>%</strong></div>
                    <div class="info-row"><span>Current Theme</span><strong><?= htmlspecialchars(ucfirst($theme_name)) ?></strong></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-digest">
            <div class="card beta-digest-card">
                <h3><i class="fas fa-robot"></i> AI Profile Digest</h3>
                <p class="beta-digest-lead">This is not the final release. It is a beta V2.1 web app already producing useful signals for school leadership, classroom follow-up, and system tuning.</p>
                <div class="beta-digest-list">
                    <?php foreach ($profile_digest as $paragraph): ?>
                        <div class="beta-digest-item">
                            <span class="beta-digest-dot"></span>
                            <p><?= htmlspecialchars($paragraph) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-system">
            <div class="settings-grid">
                <div class="card">
                    <h3><i class="fas fa-sliders"></i> Assessment Defaults</h3>
                    <div class="info-row"><span>Pass Mark</span><strong><?= $pass_mark ?>%</strong></div>
                    <div class="info-row"><span>Max Questions</span><strong><?= $max_questions ?></strong></div>
                    <div class="info-row"><span>Time Limit</span><strong><?= $time_limit ?> minutes</strong></div>
                </div>
                <div class="card">
                    <h3><i class="fas fa-server"></i> Runtime Environment</h3>
                    <div class="info-row"><span>PHP Version</span><strong><?= htmlspecialchars(phpversion()) ?></strong></div>
                    <div class="info-row"><span>Server Software</span><strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></strong></div>
                    <div class="info-row"><span>Current Time</span><strong id="systemTime"><?= date('F j, Y g:i:s A') ?></strong></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-actions">
            <div class="actions-grid">
                <a href="settings.php" class="action-card">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
                <a href="reports.php" class="action-card">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics Reports</span>
                </a>
                <a href="manage_students.php" class="action-card">
                    <i class="fas fa-users"></i>
                    <span>Manage Students</span>
                </a>
                <a href="ict_questions.php" class="action-card">
                    <i class="fas fa-laptop-code"></i>
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

<script>
function updateProfileClock() {
    const now = new Date();
    const timeString = now.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });

    const liveClock = document.getElementById('liveClock');
    const systemTime = document.getElementById('systemTime');
    if (liveClock) {
        liveClock.textContent = timeString;
    }
    if (systemTime) {
        systemTime.textContent = timeString;
    }
}

document.querySelectorAll('.tab-btn').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach((item) => item.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach((item) => item.classList.remove('active'));
        button.classList.add('active');
        document.getElementById('tab-' + button.dataset.tab)?.classList.add('active');
    });
});

updateProfileClock();
setInterval(updateProfileClock, 1000);
</script>

<?php include 'includes/footer.php'; ?>
