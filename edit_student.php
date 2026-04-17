<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$student = null;
$student_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$student_id) {
    header("Location: manage_students.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM students WHERE id = $student_id");
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: manage_students.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_student'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);

    $query = "UPDATE students SET fullname='$fullname', class='$class' WHERE id=$student_id";

    if (mysqli_query($conn, $query)) {
        header("Location: manage_students.php?status=updated");
        exit;
    }
}

$classes = ["YEAR 1", "YEAR 2", "YEAR 3", "YEAR 4", "YEAR 5", "YEAR 6", "YEAR 7", "YEAR 8"];
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-edit"></i>
                    Edit Student
                    <span class="beta-badge">Student desk</span>
                </h1>
                <p class="page-subtitle">Update student information</p>
            </div>
        </div>

        <div class="card-section">
            <form method="POST" class="form-grid form-grid-2">
                <div class="form-group">
                    <label for="fullname">Full Name *</label>
                    <input type="text" id="fullname" name="fullname" 
                           value="<?= htmlspecialchars($student['fullname']) ?>" 
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="class">Class/Grade *</label>
                    <select id="class" name="class" required>
                        <option value="">Select Class</option>
                        <?php foreach($classes as $c): ?>
                        <option value="<?= $c ?>" <?= ($student['class'] === $c) ? 'selected' : '' ?>>
                            <?= $c ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="save_student" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="manage_students.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
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

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-edit"></i>
                    <?= $edit_mode ? 'Edit Student' : 'Add New Student' ?>
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle"><?= $edit_mode ? 'Update student information' : 'Register a new student in the system' ?></p>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <input type="hidden" name="student_id" value="<?= $edit_mode ? $student['id'] : '' ?>">

            <!-- Personal Information Section -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" 
                               value="<?= $edit_mode ? htmlspecialchars($student['fullname']) : '' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?= $edit_mode ? htmlspecialchars($student['email']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?= $edit_mode ? htmlspecialchars($student['phone']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" 
                               value="<?= $edit_mode ? htmlspecialchars($student['dob']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <?php foreach($genders as $g): ?>
                            <option value="<?= $g ?>" <?= ($edit_mode && $student['gender'] === $g) ? 'selected' : '' ?>>
                                <?= $g ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class">Class/Grade *</label>
                        <select id="class" name="class" required>
                            <option value="">Select Class</option>
                            <?php foreach($classes as $c): ?>
                            <option value="<?= $c ?>" <?= ($edit_mode && $student['class'] === $c) ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Address Section -->
            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Address</h3>
                <div class="form-group">
                    <label for="address">Residential Address</label>
                    <textarea id="address" name="address" placeholder="Enter complete address"><?= $edit_mode ? htmlspecialchars($student['address']) : '' ?></textarea>
                </div>
            </div>

            <!-- Guardian Information Section -->
            <div class="form-section">
                <h3><i class="fas fa-heart"></i> Guardian Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="guardian_name">Guardian Name</label>
                        <input type="text" id="guardian_name" name="guardian_name" 
                               value="<?= $edit_mode ? htmlspecialchars($student['guardian_name']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="guardian_phone">Guardian Phone</label>
                        <input type="tel" id="guardian_phone" name="guardian_phone" 
                               value="<?= $edit_mode ? htmlspecialchars($student['guardian_phone']) : '' ?>">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="submit" name="save_student" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Student' : 'Add Student' ?>
                </button>
                <a href="manage_students.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.form-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.form-section h3 {
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #1e293b;
    font-size: 16px;
}

.form-section h3 i {
    color: #22c55e;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 14px;
    border-radius: 8px;
}

body.dark .form-section {
    background: #1e293b;
    border-color: #334155;
}

body.dark .form-section h3 {
    color: #f1f5f9;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
<?php include 'includes/footer.php'; ?>
