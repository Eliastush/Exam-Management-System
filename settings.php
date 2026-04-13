<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// Fetch system settings
$settings_res = mysqli_query($conn,"SELECT * FROM settings");
$system_settings = [];
while($row = mysqli_fetch_assoc($settings_res)){
    $system_settings[$row['setting_key']] = $row['setting_value'];
}

function getSetting($key, $default = '') {
    global $system_settings;
    return $system_settings[$key] ?? $default;
}

// Site Information
$site_title          = getSetting('site_title', 'Mustard Seed - ICT');
$site_logo           = getSetting('site_logo', '');
$school_name         = getSetting('school_name', '');
$school_address      = getSetting('school_address', '');
$school_phone        = getSetting('school_phone', '');
$school_email        = getSetting('school_email', '');
$principal_name      = getSetting('principal_name', '');

// Theme & UI
$theme               = getSetting('theme', 'light');
$primary_color       = getSetting('primary_color', '#3b82f6');

// Quiz Settings
$default_language    = getSetting('default_language', 'en');
$max_questions       = intval(getSetting('max_questions', '10'));
$time_limit          = intval(getSetting('time_limit', '30'));
$shuffle_questions   = getSetting('shuffle_questions', '1') == '1';
$random_options      = getSetting('random_options', '1') == '1';
$pass_mark           = intval(getSetting('pass_mark', '50'));
$show_results        = getSetting('show_results', '1') == '1';
$show_answers        = getSetting('show_answers', '0') == '1';
$question_difficulty = getSetting('question_difficulty', 'mixed');

// Notifications
$enable_email        = getSetting('enable_email', '0') == '1';
$email_notifications = getSetting('email_notifications', '0') == '1';
$result_notifications = getSetting('result_notifications', '0') == '1';
$admin_notifications = getSetting('admin_notifications', '1') == '1';
$smtp_host           = getSetting('smtp_host', '');
$smtp_port           = intval(getSetting('smtp_port', '587'));
$smtp_username       = getSetting('smtp_username', '');
$smtp_password       = getSetting('smtp_password', '');

// Security
$session_timeout     = intval(getSetting('session_timeout', '30'));
$min_password_length = intval(getSetting('min_password_length', '6'));
$password_complexity = getSetting('password_complexity', '0') == '1';
$two_factor_auth     = getSetting('two_factor_auth', '0') == '1';

// System
$enable_registration = getSetting('enable_registration', '0') == '1';
$auto_backup         = getSetting('auto_backup', '1') == '1';
$maintenance_mode    = getSetting('maintenance_mode', '0') == '1';
$allow_api_access    = getSetting('allow_api_access', '0') == '1';
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-cog"></i>
                    System Settings
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Manage core system settings and preferences</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="general"><i class="fas fa-building"></i> General</button>
            <button class="tab-btn" data-tab="quiz"><i class="fas fa-question-circle"></i> Quiz</button>
            <button class="tab-btn" data-tab="notifications"><i class="fas fa-bell"></i> Notifications</button>
            <button class="tab-btn" data-tab="security"><i class="fas fa-lock"></i> Security</button>
            <button class="tab-btn" data-tab="system"><i class="fas fa-server"></i> System</button>
        </div>

        <!-- GENERAL TAB -->
        <div class="tab-content active" id="tab-general">
            <div class="card-section">
                <h3>School Information</h3>
                <div class="settings-grid">
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" id="school_name" value="<?= htmlspecialchars($school_name) ?>">
                    </div>
                    <div class="form-group">
                        <label>Principal Name</label>
                        <input type="text" id="principal_name" value="<?= htmlspecialchars($principal_name) ?>">
                    </div>
                    <div class="form-group">
                        <label>School Phone</label>
                        <input type="tel" id="school_phone" value="<?= htmlspecialchars($school_phone) ?>">
                    </div>
                    <div class="form-group">
                        <label>School Email</label>
                        <input type="email" id="school_email" value="<?= htmlspecialchars($school_email) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label>School Address</label>
                        <textarea id="school_address" rows="3"><?= htmlspecialchars($school_address) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <h3>Site Branding</h3>
                <div class="settings-grid">
                    <div class="form-group">
                        <label>Site Title</label>
                        <input type="text" id="site_title" value="<?= htmlspecialchars($site_title) ?>">
                    </div>
                    <div class="form-group">
                        <label>Site Logo URL</label>
                        <input type="url" id="site_logo" value="<?= htmlspecialchars($site_logo) ?>">
                    </div>
                    <div class="form-group">
                        <label>Primary Color</label>
                        <div class="color-picker">
                            <input type="color" id="primary_color" value="<?= htmlspecialchars($primary_color) ?>">
                            <span id="colorValue"><?= htmlspecialchars($primary_color) ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Theme Mode</label>
                        <select id="theme">
                            <option value="light" <?= $theme === 'light' ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>Dark</option>
                            <option value="auto" <?= $theme === 'auto' ? 'selected' : '' ?>>Auto</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUIZ TAB -->
        <div class="tab-content" id="tab-quiz">
            <div class="card-section">
                <h3>Quiz Configuration</h3>
                <div class="settings-grid">
                    <div class="form-group">
                        <label>Max Questions per Quiz</label>
                        <input type="number" id="max_questions" value="<?= $max_questions ?>" min="5" max="100">
                    </div>
                    <div class="form-group">
                        <label>Time Limit (minutes)</label>
                        <input type="number" id="time_limit" value="<?= $time_limit ?>" min="5" max="180">
                    </div>
                    <div class="form-group">
                        <label>Pass Mark (%)</label>
                        <input type="number" id="pass_mark" value="<?= $pass_mark ?>" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>Default Difficulty</label>
                        <select id="question_difficulty">
                            <option value="easy" <?= $question_difficulty === 'easy' ? 'selected' : '' ?>>Easy</option>
                            <option value="mixed" <?= $question_difficulty === 'mixed' ? 'selected' : '' ?>>Mixed</option>
                            <option value="hard" <?= $question_difficulty === 'hard' ? 'selected' : '' ?>>Hard</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <h3>Quiz Behavior</h3>
                <div class="settings-grid">
                    <div class="toggle-card">
                        <label>Shuffle Questions</label>
                        <label class="toggle">
                            <input type="checkbox" id="shuffle_questions" <?= $shuffle_questions ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Randomize Options</label>
                        <label class="toggle">
                            <input type="checkbox" id="random_options" <?= $random_options ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Show Results Immediately</label>
                        <label class="toggle">
                            <input type="checkbox" id="show_results" <?= $show_results ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Show Correct Answers</label>
                        <label class="toggle">
                            <input type="checkbox" id="show_answers" <?= $show_answers ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- NOTIFICATIONS TAB -->
        <div class="tab-content" id="tab-notifications">
            <div class="card-section">
                <h3>Notification Preferences</h3>
                <div class="settings-grid">
                    <div class="toggle-card">
                        <label>Enable Email System</label>
                        <label class="toggle">
                            <input type="checkbox" id="enable_email" <?= $enable_email ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Email Notifications</label>
                        <label class="toggle">
                            <input type="checkbox" id="email_notifications" <?= $email_notifications ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Result Notifications</label>
                        <label class="toggle">
                            <input type="checkbox" id="result_notifications" <?= $result_notifications ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Admin Notifications</label>
                        <label class="toggle">
                            <input type="checkbox" id="admin_notifications" <?= $admin_notifications ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <h3>SMTP Email Settings</h3>
                <div class="settings-grid">
                    <div class="form-group">
                        <label>SMTP Host</label>
                        <input type="text" id="smtp_host" value="<?= htmlspecialchars($smtp_host) ?>">
                    </div>
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="number" id="smtp_port" value="<?= $smtp_port ?>">
                    </div>
                    <div class="form-group">
                        <label>SMTP Username</label>
                        <input type="text" id="smtp_username" value="<?= htmlspecialchars($smtp_username) ?>">
                    </div>
                    <div class="form-group">
                        <label>SMTP Password</label>
                        <input type="password" id="smtp_password" value="<?= htmlspecialchars($smtp_password) ?>" placeholder="Leave blank to keep current">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECURITY TAB -->
        <div class="tab-content" id="tab-security">
            <div class="card-section">
                <h3>Security Settings</h3>
                <div class="settings-grid">
                    <div class="form-group">
                        <label>Session Timeout (minutes)</label>
                        <input type="number" id="session_timeout" value="<?= $session_timeout ?>" min="5" max="1440">
                    </div>
                    <div class="form-group">
                        <label>Minimum Password Length</label>
                        <input type="number" id="min_password_length" value="<?= $min_password_length ?>" min="4" max="32">
                    </div>
                    <div class="toggle-card">
                        <label>Require Complex Passwords</label>
                        <label class="toggle">
                            <input type="checkbox" id="password_complexity" <?= $password_complexity ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Enable Two-Factor Authentication</label>
                        <label class="toggle">
                            <input type="checkbox" id="two_factor_auth" <?= $two_factor_auth ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- SYSTEM TAB -->
        <div class="tab-content" id="tab-system">
            <div class="card-section">
                <h3>System Maintenance</h3>
                <div class="settings-grid">
                    <div class="toggle-card">
                        <label>Auto Backup Database</label>
                        <label class="toggle">
                            <input type="checkbox" id="auto_backup" <?= $auto_backup ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Maintenance Mode</label>
                        <label class="toggle">
                            <input type="checkbox" id="maintenance_mode" <?= $maintenance_mode ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Allow Student Registration</label>
                        <label class="toggle">
                            <input type="checkbox" id="enable_registration" <?= $enable_registration ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-card">
                        <label>Allow API Access</label>
                        <label class="toggle">
                            <input type="checkbox" id="allow_api_access" <?= $allow_api_access ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Bar -->
        <div class="save-bar">
            <button class="btn btn-primary" onclick="saveAllSettings()">
                <i class="fas fa-save"></i> Save All Changes
            </button>
            <button class="btn btn-secondary" onclick="location.reload()">
                <i class="fas fa-undo"></i> Discard Changes
            </button>
        </div>
    </div>
</div>



<script>
// Tab Switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.getAttribute('data-tab')).classList.add('active');
    });
});

// Color Picker Live Update
document.getElementById('primary_color').addEventListener('input', function() {
    document.getElementById('colorValue').textContent = this.value;
});

// Auto Save
document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('change', function() {
        if (this.type !== 'password') {
            saveSetting(this.id, this.type === 'checkbox' ? (this.checked ? 1 : 0) : this.value);
        }
    });
});

function saveSetting(key, value) {
    showToast('Saving...', 'info');
    fetch('save_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [key]: value })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('✓ Saved', 'success');
        } else {
            showToast('Failed', 'error');
        }
    })
    .catch(() => showToast('Connection error', 'error'));
}

function saveAllSettings() {
    const settings = {};
    document.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.id) {
            settings[el.id] = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;
        }
    });

    showToast('Saving all settings...', 'info');
    fetch('save_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') showToast('✓ All settings saved', 'success');
        else showToast('Failed to save', 'error');
    })
    .catch(() => showToast('Connection error', 'error'));
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.style.cssText = `position:fixed;bottom:30px;right:30px;padding:14px 24px;border-radius:10px;color:white;z-index:10000;`;
    toast.style.background = type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#64748b';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Placeholder functions (as requested)
function openPasswordModal() {
    alert("🔐 Password change modal would open here.");
}

function enableTwoFactor() {
    alert("🛡️ Two-Factor Authentication setup would start here.");
}
</script>

<?php include 'Includes/footer.php'; ?>