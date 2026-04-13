<?php
/**
 * Settings Table Initialization Script
 * This script creates the settings table if it doesn't exist
 * Run once on first setup or if the settings table is missing
 */

$conn = mysqli_connect('localhost', 'root', '', 'quiz_system');

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` longtext,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $sql)) {
    echo "✓ Settings table created successfully.<br>";
} else {
    echo "✗ Error creating settings table: " . mysqli_error($conn) . "<br>";
    mysqli_close($conn);
    exit;
}

// Default settings
$default_settings = [
    // Site Information
    'site_title' => 'Mustard Seed - ICT Quiz System',
    'site_logo' => '',
    'school_name' => 'Mustard Seed International Schools',
    'school_address' => '',
    'school_phone' => '',
    'school_email' => '',
    'principal_name' => '',
    
    // Theme & UI
    'theme' => 'light',
    'primary_color' => '#3b82f6',
    
    // Quiz Settings
    'default_language' => 'en',
    'max_questions' => '10',
    'time_limit' => '30',
    'shuffle_questions' => '1',
    'random_options' => '1',
    'pass_mark' => '50',
    'show_results' => '1',
    'show_answers' => '0',
    'question_difficulty' => 'mixed',
    
    // Notifications
    'enable_email' => '0',
    'email_notifications' => '0',
    'result_notifications' => '0',
    'admin_notifications' => '1',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_password' => '',
    
    // Security
    'session_timeout' => '30',
    'min_password_length' => '6',
    'password_complexity' => '0',
    'two_factor_auth' => '0',
    
    // System
    'enable_registration' => '0',
    'auto_backup' => '1',
    'maintenance_mode' => '0',
    'allow_api_access' => '0'
];

// Insert default settings
$inserted = 0;
$skipped = 0;

foreach ($default_settings as $key => $value) {
    $key_escaped = mysqli_real_escape_string($conn, $key);
    $value_escaped = mysqli_real_escape_string($conn, $value);
    
    $check_sql = "SELECT id FROM settings WHERE setting_key = '$key_escaped'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('$key_escaped', '$value_escaped')";
        if (mysqli_query($conn, $insert_sql)) {
            $inserted++;
        } else {
            echo "✗ Error inserting setting '$key': " . mysqli_error($conn) . "<br>";
        }
    } else {
        $skipped++;
    }
}

echo "✓ Inserted $inserted new settings (skipped $skipped existing).<br>";
echo "✓ Settings table initialization complete!<br>";

// Check if all tables exist
$tables_to_check = ['questions', 'students', 'results', 'settings'];
echo "<br><strong>Database Status:</strong><br>";

foreach ($tables_to_check as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($check) > 0 ? '✓' : '✗';
    echo "$exists $table<br>";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings Initialization</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #16a34a; }
        .error { color: #ef4444; }
        a { color: #3b82f6; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Settings Initialization Complete</h1>
        <p>The settings table has been successfully created and initialized with default values.</p>
        <p>You can now safely delete this file and proceed to use the system.</p>
        <p><a href="settings.php">Go to Settings Page →</a></p>
    </div>
</body>
</html>
