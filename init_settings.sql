-- Settings Table for Quiz System
-- This table stores all system configuration settings

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` longtext,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
-- Site Information
('site_title', 'Mustard Seed - ICT Quiz System'),
('site_logo', ''),
('school_name', 'Mustard Seed International Schools'),
('school_address', ''),
('school_phone', ''),
('school_email', ''),
('principal_name', ''),

-- Theme & UI
('theme', 'light'),
('primary_color', '#3b82f6'),

-- Quiz Settings
('default_language', 'en'),
('max_questions', '10'),
('time_limit', '30'),
('shuffle_questions', '1'),
('random_options', '1'),
('pass_mark', '50'),
('show_results', '1'),
('show_answers', '0'),
('question_difficulty', 'mixed'),

-- Notifications
('enable_email', '0'),
('email_notifications', '0'),
('result_notifications', '0'),
('admin_notifications', '1'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),

-- Security
('session_timeout', '30'),
('min_password_length', '6'),
('password_complexity', '0'),
('two_factor_auth', '0'),

-- System
('enable_registration', '0'),
('auto_backup', '1'),
('maintenance_mode', '0'),
('allow_api_access', '0')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
