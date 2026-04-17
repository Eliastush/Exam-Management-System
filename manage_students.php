<?php
include 'includes/header.php';

if (!$conn) {
    die('Database connection failed');
}

$classes = ['YEAR 1', 'YEAR 2', 'YEAR 3', 'YEAR 4', 'YEAR 5', 'YEAR 6', 'YEAR 7', 'YEAR 8'];
$message = '';
$message_type = 'success';

if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM students WHERE id = {$id}");
    header('Location: manage_students.php?status=deleted');
    exit;
}

if (isset($_GET['status']) && $_GET['status'] === 'deleted') {
    $message = 'Student record deleted successfully.';
}

$students_result = mysqli_query($conn, 'SELECT * FROM students ORDER BY id DESC');
$students = $students_result ? mysqli_fetch_all($students_result, MYSQLI_ASSOC) : [];
$total_students = count($students);
$class_total = count(array_unique(array_filter(array_map(fn($student) => $student['class'] ?? '', $students))));
$latest_student = $students[0]['fullname'] ?? 'No student yet';
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-users"></i> Students <span class="beta-badge">Student desk</span></h1>
                <p class="page-subtitle">Search, filter, review, and clean up student records from one place.</p>
            </div>
            <div class="header-actions">
                <a href="add_student.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Student</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="card stat-card">
                <i class="fas fa-users fa-2x"></i>
                <div class="stat-value"><?= number_format($total_students) ?></div>
                <div class="stat-label">Students</div>
            </div>
            <div class="card stat-card">
                <i class="fas fa-layer-group fa-2x"></i>
                <div class="stat-value"><?= number_format($class_total) ?></div>
                <div class="stat-label">Active Classes</div>
            </div>
            <div class="card stat-card">
                <i class="fas fa-star fa-2x"></i>
                <div class="stat-value" style="font-size:1.2rem"><?= htmlspecialchars($latest_student) ?></div>
                <div class="stat-label">Most Recent Student</div>
            </div>
        </div>

        <div class="card-section">
            <div class="controls">
                <div class="form-group" style="flex: 2 1 280px;">
                    <label for="studentSearch">Search</label>
                    <input type="text" id="studentSearch" placeholder="Search by name, ID, or class...">
                </div>
                <div class="form-group" style="flex: 1 1 220px;">
                    <label for="classFilter">Filter by class</label>
                    <select id="classFilter">
                        <option value="">All classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= htmlspecialchars($class) ?>"><?= htmlspecialchars($class) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 0 0 170px;">
                    <label for="rowsPerPage">Rows per page</label>
                    <select id="rowsPerPage">
                        <option value="8">8</option>
                        <option value="12" selected>12</option>
                        <option value="20">20</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-section">
            <?php if ($students): ?>
                <div class="table-container">
                    <table class="table" id="studentsTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $student): ?>
                                <tr data-search="<?= htmlspecialchars(strtolower(($student['fullname'] ?? '') . ' ' . ($student['class'] ?? '') . ' ' . ($student['id'] ?? ''))) ?>" data-class="<?= htmlspecialchars($student['class'] ?? '') ?>">
                                    <td class="table-row-number" data-row-number="<?= $index + 1 ?>"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($student['fullname']) ?></td>
                                    <td><span class="class-badge"><?= htmlspecialchars($student['class'] ?? 'Not set') ?></span></td>
                                    <td>
                                        <div class="row-actions">
                                            <button type="button" class="row-action-btn" data-view-id="<?= (int) $student['id'] ?>" title="Quick view">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit_student.php?id=<?= (int) $student['id'] ?>" class="row-action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_students.php?delete_id=<?= (int) $student['id'] ?>" class="row-action-btn danger" onclick="return confirm('Delete this student record?');" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="controls mt-3"></div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox fa-2x"></i>
                    <h3>No students yet</h3>
                    <p>Add your first learner to start tracking attendance and results.</p>
                    <a href="add_student.php" class="btn btn-primary mt-3"><i class="fas fa-plus"></i> Add Student</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="studentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeStudentModal()">&times;</span>
        <div id="studentDetails">Loading...</div>
    </div>
</div>

<script>
const studentSearch = document.getElementById('studentSearch');
const classFilter = document.getElementById('classFilter');
const rowsPerPageSelect = document.getElementById('rowsPerPage');
const tableRows = Array.from(document.querySelectorAll('#studentsTable tbody tr'));
const pagination = document.getElementById('pagination');
let currentPage = 1;

function filteredRows() {
    const query = (studentSearch?.value || '').trim().toLowerCase();
    const classValue = classFilter?.value || '';
    return tableRows.filter((row) => {
        const matchesSearch = row.dataset.search.includes(query);
        const matchesClass = !classValue || row.dataset.class === classValue;
        return matchesSearch && matchesClass;
    });
}

function renderPagination(totalPages) {
    if (!pagination) return;
    pagination.innerHTML = '';
    if (totalPages <= 1) return;

    const createButton = (label, page, disabled = false, active = false) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `btn ${active ? 'btn-primary' : 'btn-secondary'} btn-sm`;
        button.textContent = label;
        button.disabled = disabled;
        button.addEventListener('click', () => {
            currentPage = page;
            renderTable();
        });
        pagination.appendChild(button);
    };

    createButton('Previous', currentPage - 1, currentPage === 1);
    for (let page = 1; page <= totalPages; page++) {
        createButton(String(page), page, false, page === currentPage);
    }
    createButton('Next', currentPage + 1, currentPage === totalPages);
}

function renderTable() {
    const pageSize = Number(rowsPerPageSelect?.value || 12);
    const rows = filteredRows();
    const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
    currentPage = Math.min(currentPage, totalPages);
    const startIndex = (currentPage - 1) * pageSize;

    tableRows.forEach((row) => { row.style.display = 'none'; });
    rows.slice(startIndex, currentPage * pageSize).forEach((row, visibleIndex) => {
        const numberCell = row.querySelector('[data-row-number]');
        if (numberCell) {
            numberCell.textContent = startIndex + visibleIndex + 1;
        }
        row.style.display = '';
    });

    renderPagination(totalPages);
}

studentSearch?.addEventListener('input', () => { currentPage = 1; renderTable(); });
classFilter?.addEventListener('change', () => { currentPage = 1; renderTable(); });
rowsPerPageSelect?.addEventListener('change', () => { currentPage = 1; renderTable(); });

async function openStudentModal(id) {
    const modal = document.getElementById('studentModal');
    const details = document.getElementById('studentDetails');
    modal.style.display = 'flex';
    details.innerHTML = 'Loading...';

    try {
        const response = await fetch(`student_details.php?id=${id}`);
        details.innerHTML = await response.text();
    } catch (error) {
        details.innerHTML = '<p class="text-danger">Unable to load student details right now.</p>';
    }
}

function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}

document.querySelectorAll('[data-view-id]').forEach((button) => {
    button.addEventListener('click', () => openStudentModal(button.dataset.viewId));
});

window.addEventListener('click', (event) => {
    if (event.target.id === 'studentModal') {
        closeStudentModal();
    }
});

if (tableRows.length) {
    renderTable();
}
</script>

<?php include 'includes/footer.php'; ?>
