<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$classes = ["YEAR 1","YEAR 2","YEAR 3","YEAR 4","YEAR 5","YEAR 6","YEAR 7","YEAR 8"];

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM students WHERE id = $id");
    header("Location: manage_students.php?status=deleted");
    exit;
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-users"></i>
                    Students
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Manage and track all student records</p>
            </div>
        </div>

    <!-- Controls -->
    <div class="card-section">
        <div class="controls">
            <div class="form-group">
                <label for="ms_searchInput">🔍 Search</label>
                <input type="text" id="ms_searchInput" placeholder="Search by name or ID..." >
            </div>

            <div class="form-group">
                <label for="ms_filterClass">📂 Filter</label>
                <select id="ms_filterClass">
                    <option value="">All Classes</option>
                    <?php foreach($classes as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <a href="edit_student.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card-section space-md">
        <div class="table-container">
            <table class="table" id="studentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $students_res = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
                    while($row = mysqli_fetch_assoc($students_res)): 
                    ?>
                    <tr data-class="<?= htmlspecialchars($row['class']) ?>">
                        <td><strong><?= $row['id'] ?></strong></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><span class="col-status active"><?= htmlspecialchars($row['class']) ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a href="edit_student.php?id=<?= $row['id'] ?>" class="row-action-btn" title="Edit" data-id="<?= $row['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete_id=<?= $row['id'] ?>" 
                                   class="row-action-btn danger" 
                                   onclick="return confirm('Delete this student?')" 
                                   title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State Check -->
    <?php if (mysqli_num_rows($students_res) == 0): ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No students yet</h3>
        <p>Start by adding your first student to the system</p>
        <a href="edit_student.php" class="btn btn-primary" style="margin-top: 20px;">
            <i class="fas fa-plus"></i> Add Student
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Student Details Modal -->
<div id="studentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h3>Student Details</h3>
        <div id="studentDetails"></div>
    </div>
</div>

<style>
/* Main wrapper with consistent styling */
.view-card {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid var(--input-border);
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-color);
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.beta-badge {
    display: inline-block;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.page-subtitle {
    font-size: 14px;
    color: var(--text-color);
    opacity: 0.8;
    margin: 8px 0 0 0;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--input-border);
}

/* Controls */
.controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
    align-items: flex-end;
}

.form-group {
    flex: 1;
    min-width: 200px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    font-size: 13px;
    color: var(--text-color);
}

.form-group input,
.form-group select {
    padding: 10px 12px;
    border: 1px solid var(--input-border);
    border-radius: 8px;
    background: var(--input-bg);
    color: var(--text-color);
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
}

.d-flex { display: flex; }
.gap-2 { gap: 8px; }

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    background: var(--primary-color);
    color: white;
    box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
}

/* Table */
.table-container {
    overflow-x: auto;
    background: var(--card-bg);
    margin: 20px 0;
    border-radius: 12px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead {
    background: var(--input-bg);
    color: var(--text-color);
}

.table th {
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-color);
    border-bottom: 2px solid var(--input-border);
}

.table td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--input-border);
    font-size: 13px;
    vertical-align: middle;
}

.table tbody tr {
    transition: background 0.15s ease;
}

.table tbody tr:hover {
    background: var(--input-bg);
}

.col-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    background: rgba(34, 197, 94, 0.2);
    color: var(--primary-color);
}

/* Action Buttons */
.row-actions {
    display: flex;
    gap: 6px;
    white-space: nowrap;
}

.row-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    color: var(--primary-color);
    background: transparent;
    border: 1px solid var(--input-border);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.row-action-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.row-action-btn.danger {
    color: #ef4444;
}

.row-action-btn.danger:hover {
    background: #ef4444;
    border-color: #ef4444;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 2000;
}

.modal-content {
    background: var(--card-bg);
    border-radius: 12px;
    width: 520px;
    max-width: 95%;
    padding: 30px;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    color: var(--text-color);
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    cursor: pointer;
    color: var(--text-color);
    opacity: 0.6;
    transition: opacity 0.2s;
}

.close:hover {
    opacity: 1;
    color: #ef4444;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-color);
}

.empty-state i {
    font-size: 48px;
    color: var(--text-color);
    opacity: 0.4;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 12px 0;
}

.empty-state p {
    color: var(--text-color);
    opacity: 0.7;
}

/* Pagination */
.pagination {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}

.pagination button {
    padding: 8px 14px;
    border: 1px solid var(--input-border);
    border-radius: 6px;
    background: var(--input-bg);
    color: var(--text-color);
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
}

.pagination button:hover,
.pagination button.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.space-md { margin: 20px 0; }
</style>

<script>
// Unique variable names to prevent conflict with global header search
let ms_searchInput = document.getElementById('ms_searchInput');
let ms_filterClass = document.getElementById('ms_filterClass');
let ms_tableBody = document.querySelector('#studentsTable tbody');
let ms_allRows = Array.from(ms_tableBody.getElementsByTagName('tr'));

const ms_rowsPerPage = 12;
let ms_currentPage = 1;

function ms_renderTable() {
    const searchTerm = ms_searchInput.value.toLowerCase().trim();
    const selectedClass = ms_filterClass.value;

    const filteredRows = ms_allRows.filter(row => {
        const name = row.children[1].textContent.toLowerCase();
        const cls = row.getAttribute('data-class');
        return name.includes(searchTerm) && (!selectedClass || cls === selectedClass);
    });

    const totalPages = Math.ceil(filteredRows.length / ms_rowsPerPage);
    if (ms_currentPage > totalPages) ms_currentPage = totalPages || 1;

    ms_allRows.forEach(r => r.style.display = 'none');

    const start = (ms_currentPage - 1) * ms_rowsPerPage;
    const end = start + ms_rowsPerPage;
    filteredRows.slice(start, end).forEach(r => r.style.display = '');

    ms_renderPagination(totalPages);
}

// Improved Pagination with Prev / Next buttons
function ms_renderPagination(totalPages) {
    const container = document.getElementById('pagination');
    container.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous Button
    const prevBtn = document.createElement('button');
    prevBtn.textContent = '← Previous';
    prevBtn.className = 'prev';
    prevBtn.disabled = (ms_currentPage === 1);
    prevBtn.addEventListener('click', () => {
        if (ms_currentPage > 1) {
            ms_currentPage--;
            ms_renderTable();
        }
    });
    container.appendChild(prevBtn);

    // Numbered Buttons
    const maxVisible = 5;
    let startPage = Math.max(1, ms_currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);

    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = (i === ms_currentPage) ? 'active' : '';
        btn.addEventListener('click', () => {
            ms_currentPage = i;
            ms_renderTable();
        });
        container.appendChild(btn);
    }

    // Next Button
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next →';
    nextBtn.className = 'next';
    nextBtn.disabled = (ms_currentPage === totalPages);
    nextBtn.addEventListener('click', () => {
        if (ms_currentPage < totalPages) {
            ms_currentPage++;
            ms_renderTable();
        }
    });
    container.appendChild(nextBtn);
}

// Event Listeners
ms_searchInput.addEventListener('input', () => { ms_currentPage = 1; ms_renderTable(); });
ms_filterClass.addEventListener('change', () => { ms_currentPage = 1; ms_renderTable(); });

// Modal
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        fetch(`student_details.php?id=${id}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('studentDetails').innerHTML = html;
                document.getElementById('studentModal').style.display = 'flex';
            })
            .catch(() => {
                document.getElementById('studentDetails').innerHTML = "<p style='color:red;'>Failed to load details.</p>";
            });
    });
});

function closeModal() {
    document.getElementById('studentModal').style.display = 'none';
}

window.onclick = function(e) {
    if (e.target.id === 'studentModal') closeModal();
};

// Initial render
ms_renderTable();
</script>

<?php include 'Includes/footer.php'; ?>