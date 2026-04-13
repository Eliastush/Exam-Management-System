# Exam Management System (EMS)

A secure, lightweight, and user-friendly **Exam Management System** built for **Mustard Seed International Schools**.

### "Rise and Shine"

## Features

### 👨‍🎓 Student Side (No Login Required)
- Search by student name
- View available exams filtered by class (YEAR 1 – YEAR 8 + General)
- Clean exam interface with sticky header
- Real-time timer with auto-submit
- Instant auto-grading and results

### 👨‍💼 Admin Side (Login Required)
- Manage students
- Create exams and class-specific questions
- View results and analytics

## Tech Stack
- **PHP 8+** with PDO (Prepared Statements)
- **MySQL** Database
- **Bootstrap 5** + Font Awesome
- MVVM-inspired architecture
- Separate HTML / CSS / JS files
- Strong security (CSRF, Session protection, One attempt per exam)

## Project Structure

School/
├── public/              # Student accessible files
├── private/Core/        # Core files (Config, Database, App)
├── assets/css/          # Separate CSS files
├── assets/js/           # Separate JS files
├── .htaccess
├── README.md
└── quiz_system.sql      # Database structure


## Setup Instructions
1. Import `quiz_system.sql` into your MySQL database
2. Update database credentials in `private/Core/Config.php`
3. Place the project in `htdocs/School`
4. Access via `http://localhost/School/public/`

## School Branding
- **School Name**: Mustard Seed International Schools
- **Motto**: Rise and Shine
- **Primary Color**: `#198754` (Green)

---

**Made with ❤️ for Mustard Seed International Schools**
