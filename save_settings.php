<?php
// save_settings.php - FINAL FIXED VERSION
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "quiz_system");

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'No valid data received']);
    exit;
}

$allowed_keys = [
    // Site Information
    'site_title', 'site_logo', 'school_name', 'school_address', 'school_phone', 'school_email', 'principal_name',
    // Theme & UI
    'theme', 'primary_color',
    // Quiz Settings
    'default_language', 'max_questions', 'time_limit', 'shuffle_questions', 'random_options', 
    'pass_mark', 'show_results', 'show_answers', 'question_difficulty',
    // Notifications
    'enable_email', 'email_notifications', 'result_notifications', 'admin_notifications',
    'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
    // Security
    'session_timeout', 'min_password_length', 'password_complexity', 'two_factor_auth',
    // System
    'enable_registration', 'auto_backup', 'maintenance_mode', 'allow_api_access'
];

$updated = 0;

foreach ($data as $key => $value) {
    if (!in_array($key, $allowed_keys)) continue;

    $key   = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, trim($value));

    $check = mysqli_query($conn, "SELECT setting_key FROM settings WHERE setting_key = '$key'");

    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'";
    } else {
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')";
    }

    if (mysqli_query($conn, $sql)) {
        $updated++;
    }
}

if ($updated > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No valid settings were updated']);
}

mysqli_close($conn);
exit;