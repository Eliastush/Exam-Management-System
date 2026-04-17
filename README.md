# School Admin Dashboard

PHP and MySQL school admin system for managing students, teachers, questions, results, reports, attendance, and system settings.

## What It Does

- Admin login and session-based access
- Student registration, editing, listing, and quick profile view
- Teacher management and timetable pages
- ICT question bank and exam results tracking
- Reports and analytics dashboard with charts
- Theme and branding settings stored in the database
- Shared admin UI with sidebar, search, notifications, and loader

## Main Files

- `login.php` - admin login screen
- `dashboard.php` - analytics overview
- `manage_students.php` - student list, filters, and modal quick view
- `manage_teachers.php` - teacher records
- `ict_questions.php` - question bank
- `exams_results.php` - result management
- `reports.php` - reporting and exports
- `settings.php` - system customization
- `includes/header.php` - shared session/auth/layout bootstrap
- `includes/sidebar.php` - main navigation and Today panel
- `includes/footer.php` - global footer and live search
- `includes/style.css` - shared styling

## Requirements

- XAMPP or another PHP + MySQL stack
- PHP 8 recommended
- MySQL database named `quiz_system`

## Setup

1. Place the project in your web root, for example `C:\xampp\htdocs\School`.
2. Create a database named `quiz_system`.
3. Import `quiz_system.sql`.
4. If settings are missing, run/import `init_settings.php` or `init_settings.sql`.
5. Open `http://localhost/School/login.php`.

## Default Login

- Username: `admin`
- Password: `1234`

## Notes

- Branding such as title, school name, logo, and primary color come from the `settings` table.
- The dashboard now uses a shared loader and chart containers to reduce layout jump during load.
- `README.md` describes the current flat PHP structure in this repository.
