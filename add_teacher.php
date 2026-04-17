<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// Handle form submission
if (isset($_POST['add_teacher'])) {
    $name = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    if ($name && $email) {
        mysqli_query($conn, "INSERT INTO teachers (fullname, email, subject, phone) VALUES ('$name','$email','$subject','$phone')");
        $msg = "Teacher added successfully!";
    }
}

// Handle CSV upload
if (isset($_POST['upload_csv']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $name = mysqli_real_escape_string($conn, $data[0]);
            $email = mysqli_real_escape_string($conn, $data[1]);
            $subject = mysqli_real_escape_string($conn, $data[2] ?? '');
            $phone = mysqli_real_escape_string($conn, $data[3] ?? '');
            if ($name && $email) {
                mysqli_query($conn, "INSERT INTO teachers (fullname, email, subject, phone) VALUES ('$name','$email','$subject','$phone')");
            }
        }
        fclose($handle);
        $msg = "CSV uploaded successfully!";
    }
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-tie"></i>
                    Add Teacher
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Register a new teacher in the system</p>
            </div>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="form-grid">
            <!-- Single Teacher Form -->
            <div class="card">
                <h3><i class="fas fa-user-plus"></i> Add Single Teacher</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="e.g., Mathematics, English">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <button type="submit" name="add_teacher" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Teacher
                    </button>
                </form>
            </div>

            <!-- CSV Upload -->
            <div class="card">
                <h3><i class="fas fa-file-upload"></i> Bulk Upload via CSV</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="csv_file">CSV File *</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <small class="text-muted">
                        CSV format: Name,Email,Subject,Phone<br>
                        Example: John Doe,john@school.edu,Mathematics,123-456-7890
                    </small>
                    <br><br>
                    <button type="submit" name="upload_csv" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Upload CSV
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Teachers -->
        <div class="card" style="margin-top: 30px;">
            <h3><i class="fas fa-users"></i> Recently Added Teachers</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Phone</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id DESC LIMIT 5");
                        while ($teacher = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($teacher['fullname']) ?></td>
                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                            <td><?= htmlspecialchars($teacher['subject'] ?: 'Not specified') ?></td>
                            <td><?= htmlspecialchars($teacher['phone'] ?: 'Not provided') ?></td>
                            <td><?= date('M j, Y', strtotime($teacher['created_at'] ?? 'now')) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.text-muted {
    color: #64748b;
    font-size: 0.9rem;
}
</style>
<?php include 'includes/footer.php'; ?>
