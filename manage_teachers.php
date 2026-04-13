<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM teachers WHERE id = $id");
    header("Location: manage_teachers.php?status=deleted");
    exit;
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Manage Teachers
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Search, edit, and manage teacher records</p>
            </div>
        </div>

        <!-- Controls -->
        <div class="card">
            <div class="controls">
                <div class="form-group">
                    <label for="searchInput">Search Teachers:</label>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search by name or email...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="filterSubject">Filter by Subject:</label>
                    <select id="filterSubject">
                        <option value="">All Subjects</option>
                        <?php
                        $subjects = mysqli_query($conn, "SELECT DISTINCT subject FROM teachers WHERE subject != '' ORDER BY subject");
                        while ($subject = mysqli_fetch_assoc($subjects)) {
                            echo '<option value="' . htmlspecialchars($subject['subject']) . '">' . htmlspecialchars($subject['subject']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <a href="add_teacher.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Teacher
                    </a>
                </div>
            </div>
        </div>

        <!-- Teachers Table -->
        <div class="card">
            <h3><i class="fas fa-users"></i> Teacher Records</h3>
            <div class="table-container">
                <table class="table" id="teachersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $teachers_res = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id DESC");
                        while($row = mysqli_fetch_assoc($teachers_res)):
                        ?>
                        <tr data-subject="<?= htmlspecialchars($row['subject']) ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><span class="subject-badge"><?= htmlspecialchars($row['subject'] ?: 'Not specified') ?></span></td>
                            <td><?= htmlspecialchars($row['phone'] ?: 'Not provided') ?></td>
                            <td class="action-col">
                                <button class="btn-icon" title="View Details" onclick="viewTeacher(<?= $row['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Edit" onclick="editTeacher(<?= $row['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete_id=<?= $row['id'] ?>"
                                   class="btn-icon btn-danger"
                                   onclick="return confirm('Delete this teacher permanently?')"
                                   title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="pagination mt-3"></div>
        </div>
    </div>
</div>

<!-- Teacher Details Modal -->
<div id="teacherModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Teacher Details</h3>
        <div id="teacherDetails">
            <div class="loading">Loading...</div>
        </div>
    </div>
</div>

<script>
// Search and Filter
document.getElementById('searchInput').addEventListener('input', filterTeachers);
document.getElementById('filterSubject').addEventListener('change', filterTeachers);

function filterTeachers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const subjectFilter = document.getElementById('filterSubject').value.toLowerCase();
    const rows = document.querySelectorAll('#teachersTable tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const subject = row.getAttribute('data-subject').toLowerCase();

        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesSubject = subjectFilter === '' || subject === subjectFilter;

        row.style.display = matchesSearch && matchesSubject ? '' : 'none';
    });
}

function viewTeacher(id) {
    // Implement teacher details view
    alert('Teacher details view - ID: ' + id);
}

function editTeacher(id) {
    // Implement teacher edit
    alert('Teacher edit - ID: ' + id);
}

function closeModal() {
    document.getElementById('teacherModal').style.display = 'none';
}
</script>

<style>
.subject-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #64748b;
}
</style>