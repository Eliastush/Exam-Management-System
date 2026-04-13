<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$msg = '';
if (isset($_POST['add_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['fullname']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    if ($name && $class) {
        if (mysqli_query($conn, "INSERT INTO students (fullname, class) VALUES ('$name','$class')")) {
            $msg = "Success! Student added to system.";
        }
    }
}

$classes = ["YEAR 1","YEAR 2","YEAR 3","YEAR 4","YEAR 5","YEAR 6","YEAR 7","YEAR 8"];
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-plus"></i>
                    Add Student
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Register a new student in the system</p>
            </div>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
        <?php endif; ?>

        <div class="card-section">
            <form method="POST" class="form-grid form-grid-2">
                <div class="form-group">
                    <label for="fullname">Full Name *</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter student name" required autofocus>
                </div>

                <div class="form-group">
                    <label for="class">Class/Grade *</label>
                    <select id="class" name="class" required>
                        <option value="">Select Class</option>
                        <?php foreach($classes as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="add_student" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                    <a href="manage_students.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-grid-2 {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>