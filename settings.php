<?php
include 'includes/header.php';

if (!$conn) {
    die('Connection failed');
}

$settings_res = mysqli_query($conn, "SELECT * FROM settings");
$system_settings = [];
if ($settings_res) {
    while ($row = mysqli_fetch_assoc($settings_res)) {
        $system_settings[$row['setting_key']] = $row['setting_value'];
    }
}

function getSettingValue(string $key, $default = '') {
    global $system_settings;
    return $system_settings[$key] ?? $default;
}

$site_title = getSettingValue('site_title', 'Mustard Seed - ICT');
$site_logo = getSettingValue('site_logo', 'msis logo.png');
$school_name = getSettingValue('school_name', 'Mustard Seed International Schools');
$school_address = getSettingValue('school_address', '');
$school_phone = getSettingValue('school_phone', '');
$school_email = getSettingValue('school_email', '');
$principal_name = getSettingValue('principal_name', '');
$theme = getSettingValue('theme', 'light');
$primary_color = getSettingValue('primary_color', '#1f8f63');
$default_language = getSettingValue('default_language', 'en');
$max_questions = (int) getSettingValue('max_questions', '10');
$time_limit = (int) getSettingValue('time_limit', '30');
$shuffle_questions = getSettingValue('shuffle_questions', '1') === '1';
$random_options = getSettingValue('random_options', '1') === '1';
$pass_mark = (int) getSettingValue('pass_mark', '50');
$show_results = getSettingValue('show_results', '1') === '1';
$show_answers = getSettingValue('show_answers', '0') === '1';
$question_difficulty = getSettingValue('question_difficulty', 'mixed');
$enable_email = getSettingValue('enable_email', '0') === '1';
$email_notifications = getSettingValue('email_notifications', '0') === '1';
$result_notifications = getSettingValue('result_notifications', '0') === '1';
$admin_notifications = getSettingValue('admin_notifications', '1') === '1';
$smtp_host = getSettingValue('smtp_host', '');
$smtp_port = (int) getSettingValue('smtp_port', '587');
$smtp_username = getSettingValue('smtp_username', '');
$smtp_password = getSettingValue('smtp_password', '');
$session_timeout = (int) getSettingValue('session_timeout', '30');
$min_password_length = (int) getSettingValue('min_password_length', '6');
$password_complexity = getSettingValue('password_complexity', '0') === '1';
$two_factor_auth = getSettingValue('two_factor_auth', '0') === '1';
$enable_registration = getSettingValue('enable_registration', '0') === '1';
$auto_backup = getSettingValue('auto_backup', '1') === '1';
$maintenance_mode = getSettingValue('maintenance_mode', '0') === '1';
$allow_api_access = getSettingValue('allow_api_access', '0') === '1';

$total_students = (int) ((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students"))['total'] ?? 0));
$total_questions = (int) ((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM questions"))['total'] ?? 0));
$total_results = (int) ((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM results"))['total'] ?? 0));
$avg_score = round((float) ((mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score / total * 100) AS avg_score FROM results WHERE total > 0"))['avg_score'] ?? 0)), 1);

$settings_digest = [
    'The saved branding now controls the live beta experience, including loader logo, primary color, sidebar tone, and footer mood.',
    "Current defaults are {$max_questions} questions, {$time_limit} minutes, and a {$pass_mark}% pass mark, so any change here directly reshapes learner outcomes.",
    $maintenance_mode ? 'Maintenance mode is enabled, which is useful for safe updates but should stay temporary during beta refinement.' : 'Maintenance mode is off, so the system is positioned for active everyday use while beta improvements continue.',
    $enable_email ? 'Email capabilities are configured, which opens the door for richer result alerts and admin workflows later.' : 'Email remains disabled, so the beta still leans on in-app workflows more than outbound communication.'
];
?>

<div class="main-content">
    <div class="view-card settings-page-shell">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-cog"></i>
                    System Settings
                    <span class="beta-badge">System tuning</span>
                </h1>
                <p class="page-subtitle">Brand the beta, tune assessment behavior, and shape how the web app feels before final submission.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" type="button" onclick="location.reload()"><i class="fas fa-rotate-left"></i> Refresh</button>
                <button class="btn btn-primary" type="button" onclick="saveAllSettings()"><i class="fas fa-save"></i> Save All</button>
            </div>
        </div>

        <div class="settings-hero-grid">
            <div class="card settings-brand-card">
                <span class="profile-kicker">Beta Identity</span>
                <h3><?= htmlspecialchars($school_name) ?></h3>
                <p><?= htmlspecialchars($site_title) ?></p>
                <div class="settings-brand-preview">
                    <img src="<?= htmlspecialchars($site_logo) ?>" alt="Site logo" class="settings-logo-preview" id="settingsLogoPreview">
                    <div>
                        <span>Primary color</span>
                        <strong id="colorValue"><?= htmlspecialchars($primary_color) ?></strong>
                    </div>
                </div>
            </div>
            <div class="stats-grid">
                <div class="card stat-card"><i class="fas fa-users"></i><div><div class="stat-value"><?= number_format($total_students) ?></div><div class="stat-label">Students</div></div></div>
                <div class="card stat-card"><i class="fas fa-question-circle"></i><div><div class="stat-value"><?= number_format($total_questions) ?></div><div class="stat-label">Questions</div></div></div>
                <div class="card stat-card"><i class="fas fa-chart-line"></i><div><div class="stat-value"><?= number_format($avg_score, 1) ?>%</div><div class="stat-label">Average Score</div></div></div>
                <div class="card stat-card"><i class="fas fa-clipboard-check"></i><div><div class="stat-value"><?= number_format($total_results) ?></div><div class="stat-label">Attempts</div></div></div>
            </div>
        </div>

        <div class="card beta-digest-card settings-digest-card">
            <h3><i class="fas fa-robot"></i> AI Settings Digest</h3>
            <div class="beta-digest-list">
                <?php foreach ($settings_digest as $item): ?>
                    <div class="beta-digest-item">
                        <span class="beta-digest-dot"></span>
                        <p><?= htmlspecialchars($item) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="general">Branding</button>
            <button class="tab-btn" data-tab="quiz">Quiz Engine</button>
            <button class="tab-btn" data-tab="notifications">Notifications</button>
            <button class="tab-btn" data-tab="security">Security</button>
            <button class="tab-btn" data-tab="system">Runtime</button>
        </div>

        <div class="tab-content active" id="tab-general">
            <div class="settings-grid">
                <div class="card">
                    <h3><i class="fas fa-school"></i> School Details</h3>
                    <div class="form-group"><label>School Name</label><input type="text" id="school_name" value="<?= htmlspecialchars($school_name) ?>"></div>
                    <div class="form-group"><label>Principal Name</label><input type="text" id="principal_name" value="<?= htmlspecialchars($principal_name) ?>"></div>
                    <div class="form-group"><label>School Phone</label><input type="tel" id="school_phone" value="<?= htmlspecialchars($school_phone) ?>"></div>
                    <div class="form-group"><label>School Email</label><input type="email" id="school_email" value="<?= htmlspecialchars($school_email) ?>"></div>
                    <div class="form-group"><label>School Address</label><textarea id="school_address" rows="4"><?= htmlspecialchars($school_address) ?></textarea></div>
                </div>
                <div class="card">
                    <h3><i class="fas fa-palette"></i> Beta Branding</h3>
                    <div class="form-group"><label>Site Title</label><input type="text" id="site_title" value="<?= htmlspecialchars($site_title) ?>"></div>
                    <div class="form-group"><label>Site Logo URL</label><input type="url" id="site_logo" value="<?= htmlspecialchars($site_logo) ?>"></div>
                    <div class="form-group"><label>Theme</label><select id="theme"><option value="light" <?= $theme === 'light' ? 'selected' : '' ?>>Light</option><option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>Dark</option><option value="auto" <?= $theme === 'auto' ? 'selected' : '' ?>>Auto</option></select></div>
                    <div class="form-group"><label>Primary Color</label><input type="color" id="primary_color" value="<?= htmlspecialchars($primary_color) ?>"></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-quiz">
            <div class="settings-grid">
                <div class="card">
                    <h3><i class="fas fa-sliders"></i> Quiz Defaults</h3>
                    <div class="form-group"><label>Default Language</label><input type="text" id="default_language" value="<?= htmlspecialchars($default_language) ?>"></div>
                    <div class="form-group"><label>Max Questions</label><input type="number" id="max_questions" value="<?= $max_questions ?>" min="5" max="100"></div>
                    <div class="form-group"><label>Time Limit (minutes)</label><input type="number" id="time_limit" value="<?= $time_limit ?>" min="5" max="180"></div>
                    <div class="form-group"><label>Pass Mark (%)</label><input type="number" id="pass_mark" value="<?= $pass_mark ?>" min="0" max="100"></div>
                    <div class="form-group"><label>Question Difficulty</label><select id="question_difficulty"><option value="easy" <?= $question_difficulty === 'easy' ? 'selected' : '' ?>>Easy</option><option value="mixed" <?= $question_difficulty === 'mixed' ? 'selected' : '' ?>>Mixed</option><option value="hard" <?= $question_difficulty === 'hard' ? 'selected' : '' ?>>Hard</option></select></div>
                </div>
                <div class="card">
                    <h3><i class="fas fa-circle-check"></i> Quiz Behaviour</h3>
                    <div class="toggle-card"><label>Shuffle Questions</label><label class="toggle"><input type="checkbox" id="shuffle_questions" <?= $shuffle_questions ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Randomize Options</label><label class="toggle"><input type="checkbox" id="random_options" <?= $random_options ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Show Results Immediately</label><label class="toggle"><input type="checkbox" id="show_results" <?= $show_results ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Show Correct Answers</label><label class="toggle"><input type="checkbox" id="show_answers" <?= $show_answers ? 'checked' : '' ?>><span class="slider"></span></label></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-notifications">
            <div class="settings-grid">
                <div class="card">
                    <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
                    <div class="toggle-card"><label>Enable Email System</label><label class="toggle"><input type="checkbox" id="enable_email" <?= $enable_email ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Email Notifications</label><label class="toggle"><input type="checkbox" id="email_notifications" <?= $email_notifications ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Result Notifications</label><label class="toggle"><input type="checkbox" id="result_notifications" <?= $result_notifications ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Admin Notifications</label><label class="toggle"><input type="checkbox" id="admin_notifications" <?= $admin_notifications ? 'checked' : '' ?>><span class="slider"></span></label></div>
                </div>
                <div class="card">
                    <h3><i class="fas fa-envelope-open-text"></i> SMTP Settings</h3>
                    <div class="form-group"><label>SMTP Host</label><input type="text" id="smtp_host" value="<?= htmlspecialchars($smtp_host) ?>"></div>
                    <div class="form-group"><label>SMTP Port</label><input type="number" id="smtp_port" value="<?= $smtp_port ?>"></div>
                    <div class="form-group"><label>SMTP Username</label><input type="text" id="smtp_username" value="<?= htmlspecialchars($smtp_username) ?>"></div>
                    <div class="form-group"><label>SMTP Password</label><input type="password" id="smtp_password" value="<?= htmlspecialchars($smtp_password) ?>"></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-security">
            <div class="settings-grid">
                <div class="card">
                    <h3><i class="fas fa-lock"></i> Session and Passwords</h3>
                    <div class="form-group"><label>Session Timeout (minutes)</label><input type="number" id="session_timeout" value="<?= $session_timeout ?>" min="5" max="1440"></div>
                    <div class="form-group"><label>Minimum Password Length</label><input type="number" id="min_password_length" value="<?= $min_password_length ?>" min="4" max="32"></div>
                    <div class="toggle-card"><label>Require Complex Passwords</label><label class="toggle"><input type="checkbox" id="password_complexity" <?= $password_complexity ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Enable Two-Factor Authentication</label><label class="toggle"><input type="checkbox" id="two_factor_auth" <?= $two_factor_auth ? 'checked' : '' ?>><span class="slider"></span></label></div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-system">
            <div class="settings-grid">
                <div class="card">
                    <h3><i class="fas fa-server"></i> Runtime Controls</h3>
                    <div class="toggle-card"><label>Auto Backup Database</label><label class="toggle"><input type="checkbox" id="auto_backup" <?= $auto_backup ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Maintenance Mode</label><label class="toggle"><input type="checkbox" id="maintenance_mode" <?= $maintenance_mode ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Allow Student Registration</label><label class="toggle"><input type="checkbox" id="enable_registration" <?= $enable_registration ? 'checked' : '' ?>><span class="slider"></span></label></div>
                    <div class="toggle-card"><label>Allow API Access</label><label class="toggle"><input type="checkbox" id="allow_api_access" <?= $allow_api_access ? 'checked' : '' ?>><span class="slider"></span></label></div>
                </div>
                <div class="card">
                    <h3><i class="fas fa-circle-info"></i> Beta Notes</h3>
                    <div class="info-row"><span>Version</span><strong>Beta V2.1</strong></div>
                    <div class="info-row"><span>Submission State</span><strong>Not final yet</strong></div>
                    <div class="info-row"><span>Current Goal</span><strong>Refinement and consistency</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach((item) => item.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach((item) => item.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab)?.classList.add('active');
    });
});

const colorInput = document.getElementById('primary_color');
const colorValue = document.getElementById('colorValue');
const logoInput = document.getElementById('site_logo');
const logoPreview = document.getElementById('settingsLogoPreview');

colorInput?.addEventListener('input', function() {
    if (colorValue) {
        colorValue.textContent = this.value;
    }
});

logoInput?.addEventListener('input', function() {
    if (logoPreview) {
        logoPreview.src = this.value || 'msis logo.png';
    }
});

document.querySelectorAll('input, select, textarea').forEach((element) => {
    element.addEventListener('change', function() {
        if (!this.id || this.type === 'password') {
            return;
        }
        saveSetting(this.id, this.type === 'checkbox' ? (this.checked ? 1 : 0) : this.value, false);
    });
});

function renderToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 30);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 250);
    }, 2600);
}

function saveSetting(key, value, loud = true) {
    if (loud) {
        renderToast('Saving setting...', 'info');
    }

    return fetch('save_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [key]: value })
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.status === 'success') {
            if (loud) {
                renderToast('Setting saved successfully', 'success');
            }
            return true;
        }
        renderToast(data.message || 'Failed to save setting', 'error');
        return false;
    })
    .catch(() => {
        renderToast('Connection error while saving', 'error');
        return false;
    });
}

function saveAllSettings() {
    const settings = {};
    document.querySelectorAll('input, select, textarea').forEach((element) => {
        if (!element.id) {
            return;
        }
        settings[element.id] = element.type === 'checkbox' ? (element.checked ? 1 : 0) : element.value;
    });

    renderToast('Saving all settings...', 'info');
    fetch('save_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.status === 'success') {
            renderToast('All settings saved for Beta V2.1', 'success');
        } else {
            renderToast(data.message || 'Unable to save settings', 'error');
        }
    })
    .catch(() => renderToast('Connection error while saving all settings', 'error'));
}
</script>

<?php include 'includes/footer.php'; ?>
