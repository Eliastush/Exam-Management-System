# Exam Management System (EMS)

A secure, lightweight, and user-friendly **Examination Management System** built specifically for **Mustard Seed International Schools**.

> **"Rise and Shine"**

---

## ✨ Features

### 👨‍🎓 Student Experience (No Login Required)
- Simple name-based search to find and access student profile
- Personalized dashboard showing available exams by class
- Modern exam interface with **sticky header**
- Real-time countdown timer with automatic submission
- Instant auto-grading and result display with clear feedback

### 👨‍💼 Admin Features (Login Protected)
- Manage students and classes
- Create and manage exams
- Add class-specific questions
- View and monitor student results

---

## 🎨 Design Highlights

- **Sticky Exam Header** displaying:
  - Subject • Student Name • Class • Time Remaining • Questions Answered
- Clean, numbered question cards with smooth hover effects
- Professional radio button styling with green selection
- Consistent green theme (`#198754`) matching school branding
- Fully responsive design (works well on mobile and desktop)
- Simple, uncluttered, and school-appropriate UI

---

## 🛠 Technology Stack

- **PHP 8+** with PDO Prepared Statements
- **MySQL** Database
- **Bootstrap 5** + Font Awesome 6
- MVVM-inspired architecture
- Strong separation between public and private code
- Separate HTML, CSS, and JavaScript files

---

## 📁 Project Structure

```bash
School/
├── public/                    # Only accessible folder for students
│   ├── index.php              # Homepage - Student Search
│   ├── student.php            # Student Dashboard
│   ├── exam.php               # Exam Taking Page
│   └── assets/
│       ├── css/               # CSS files
│       └── js/                # JavaScript files
├── private/
│   └── Core/                  # Core system files
│       ├── App.php            # Autoloader & core functions
│       ├── Config.php
│       └── Database.php
├── quiz_system.sql            # Database schema
├── README.md
└── .htaccess
```
---

## Quick Setup

1. Import `quiz_system.sql` into your MySQL database
2. Update database credentials in `private/Core/Config.php`
3. Place the project in `htdocs/School`
4. Access the system at: `http://localhost/School/public/`

---

## Supported Classes

- YEAR 1 to YEAR 8
- General (questions available to all students)

---

## Features

- Student search-based access (no login)
- Class-based questions
- Sticky exam header with timer, name, class and answered count
- Auto grading and instant results
- Bootstrap 5 + green theme

---

**Developed for Mustard Seed International Schools**

**"Rise and Shine"**

*Secure • Simple • Professional*

