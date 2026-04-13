<?php
include 'Includes/header.php';
$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$classes = ["Year 1","Year 2","Year 3","Year 4","Year 5","Year 6","Year 7","Year 8"];

// Handle CSV upload
if(isset($_POST['upload_csv']) && isset($_FILES['csv_file'])){
    $file = $_FILES['csv_file']['tmp_name'];
    if(($handle = fopen($file, "r")) !== FALSE){
        $row = 0;
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
            if($row > 0){ // skip header
                $q = mysqli_real_escape_string($conn, $data[0]);
                $a = mysqli_real_escape_string($conn, $data[1]);
                $b = mysqli_real_escape_string($conn, $data[2]);
                $c = mysqli_real_escape_string($conn, $data[3]);
                $correct = mysqli_real_escape_string($conn, $data[4]);
                $class = mysqli_real_escape_string($conn, $data[5]);
                mysqli_query($conn, "INSERT INTO questions (question, option_a, option_b, option_c, correct_answer, class_level) VALUES ('$q','$a','$b','$c','$correct','$class')");
            }
            $row++;
        }
        fclose($handle);
        echo "<script>alert('CSV uploaded successfully');window.location='ict_questions.php';</script>";
    }
}

// Handle Delete
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM questions WHERE id=$id");
    echo "<script>alert('Question deleted');window.location='ict_questions.php';</script>";
}

// Fetch all questions
$questions_res = mysqli_query($conn, "SELECT * FROM questions ORDER BY class_level, id");
$questions = mysqli_fetch_all($questions_res, MYSQLI_ASSOC);
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-question-circle"></i>
                    ICT Questions
                    <span class="beta-badge">BETA</span>
                </h1>
                <p class="page-subtitle">Manage and organize ICT quiz questions</p>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="card-section">
            <div class="controls-wrapper">
                <!-- Search Bar -->
                <div class="search-bar-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        type="text" 
                        id="searchBox" 
                        placeholder="Search questions, options..."
                        class="search-input"
                    >
                </div>

                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="filterClass">
                            <i class="fas fa-filter"></i>
                            Filter by Class
                        </label>
                        <select id="filterClass" class="filter-select">
                            <option value="">All Classes</option>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="button" id="clearFilters" class="btn btn-secondary" title="Clear all filters">
                        <i class="fas fa-redo"></i>
                        Reset
                    </button>
                </div>

                <!-- Upload CSV -->
                <form method="POST" enctype="multipart/form-data" class="csv-upload-form">
                    <div class="file-input-wrapper">
                        <input type="file" name="csv_file" accept=".csv" id="csvFile" required>
                        <label for="csvFile" class="btn btn-success">
                            <i class="fas fa-upload"></i> Upload CSV
                        </label>
                    </div>
                    <button type="submit" name="upload_csv" class="btn btn-success" style="display:none;" id="uploadBtn"></button>
                </form>
            </div>
        </div>

        <!-- Questions Table -->
        <div class="table-card">
            <h3 class="table-title">
                <i class="fas fa-list"></i> 
                Questions Database
                <span class="question-count">(<span id="qCount"><?= count($questions) ?></span>)</span>
            </h3>
            
            <div class="table-container">
                <table class="table table-striped" id="questionsTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%">Question</th>
                            <th width="15%">Options</th>
                            <th width="10%">Answer</th>
                            <th width="12%">Class</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($questions as $q): ?>
                        <tr data-class="<?= htmlspecialchars($q['class_level']) ?>" data-question="<?= htmlspecialchars(strtolower($q['question'])) ?>">
                            <td class="cell-id"><?= $q['id'] ?></td>
                            <td class="cell-question" title="<?= htmlspecialchars($q['question']) ?>">
                                <?= htmlspecialchars(substr($q['question'], 0, 50)) . (strlen($q['question']) > 50 ? '...' : '') ?>
                            </td>
                            <td class="cell-options">
                                <div class="option-badge">A: <?= htmlspecialchars(substr($q['option_a'], 0, 12)) ?>...</div>
                                <div class="option-badge">B: <?= htmlspecialchars(substr($q['option_b'], 0, 12)) ?>...</div>
                                <div class="option-badge">C: <?= htmlspecialchars(substr($q['option_c'], 0, 12)) ?>...</div>
                            </td>
                            <td class="cell-answer">
                                <span class="answer-badge"><?= htmlspecialchars($q['correct_answer']) ?></span>
                            </td>
                            <td class="cell-class">
                                <span class="class-badge"><?= htmlspecialchars($q['class_level']) ?></span>
                            </td>
                            <td class="cell-actions">
                                <div class="row-actions">
                                    <button class="row-action-btn primary" onclick="editQuestion(<?= $q['id'] ?>)" title="Edit Question">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="row-action-btn danger" onclick="deleteQuestion(<?= $q['id'] ?>, '<?= htmlspecialchars(substr($q['question'], 0, 30)) ?>')" title="Delete Question">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div id="emptyState" class="empty-state" style="display: none;">
                    <i class="fas fa-search"></i>
                    <h3>No Questions Found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="pagination-wrapper"></div>
        </div>
    </div>
</div>

<style>
/* Controls Wrapper */
.controls-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

/* Search Bar */
.search-bar-wrapper {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}

.search-input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.1);
}

/* Filter Controls */
.filter-controls {
    display: flex;
    gap: 12px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-select {
    padding: 10px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 130px;
}

.filter-select:hover {
    border-color: #cbd5e1;
}

.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.1);
}

/* CSV Upload Form */
.csv-upload-form {
    display: flex;
    gap: 8px;
}

.file-input-wrapper {
    position: relative;
}

.file-input-wrapper input[type="file"] {
    display: none;
}

.file-input-wrapper label {
    display: inline-block;
    cursor: pointer;
}

/* Table Card */
.table-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
}

.table-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.question-count {
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 400;
}

/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.table thead {
    background: #f1f5f9;
    border-bottom: 2px solid #cbd5e1;
}

.table thead th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.table tbody tr {
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.table tbody td {
    padding: 12px 15px;
    color: #334155;
}

.cell-id {
    font-weight: 600;
    color: #3b82f6;
}

.cell-question {
    font-weight: 500;
    color: #1e293b;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cell-options {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.option-badge {
    font-size: 11px;
    padding: 3px 6px;
    background: #f1f5f9;
    border-radius: 4px;
    color: #475569;
    display: inline-block;
    width: fit-content;
}

.cell-answer {
    text-align: center;
}

.answer-badge {
    display: inline-block;
    padding: 6px 12px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #166534;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
}

.class-badge {
    display: inline-block;
    padding: 6px 12px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 6px;
    font-weight: 500;
    font-size: 12px;
}

.cell-actions {
    text-align: right;
}

.row-actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.row-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #f1f5f9;
    color: #475569;
}

.row-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.row-action-btn.primary {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}

.row-action-btn.primary:hover {
    background: linear-gradient(135deg, #bfdbfe, #93c5fd);
}

.row-action-btn.danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
}

.row-action-btn.danger:hover {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
}

/* Empty State */
.empty-state {
    display: none;
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 15px;
}

.empty-state h3 {
    font-size: 1.2rem;
    margin: 10px 0;
    color: #475569;
}

.empty-state p {
    margin: 5px 0;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    transition: all 0.2s ease;
}

.page-btn:hover:not(:disabled) {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #3b82f6;
}

.page-btn.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #ffffff;
    border-color: #3b82f6;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .controls-wrapper {
        flex-direction: column;
    }

    .search-bar-wrapper {
        width: 100%;
    }

    .filter-controls {
        width: 100%;
        align-items: flex-start;
    }

    .table {
        font-size: 12px;
    }

    .table thead th {
        padding: 8px 10px;
    }

    .table tbody td {
        padding: 8px 10px;
    }

    .cell-question {
        max-width: 100px;
    }

    .row-actions {
        flex-direction: column;
    }

    .row-action-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// ====================== FILTER + SEARCH + PAGINATION ======================

let q_searchBox = document.getElementById('searchBox');
let q_filterClass = document.getElementById('filterClass');
let q_clearFilters = document.getElementById('clearFilters');
let q_tableBody = document.getElementById('questionsTable').getElementsByTagName('tbody')[0];
let q_allRows = Array.from(q_tableBody.getElementsByTagName('tr'));
let q_emptyState = document.getElementById('emptyState');

const q_rowsPerPage = 10;
let q_currentPage = 1;

function q_renderTable() {
    const searchTerm = q_searchBox.value.toLowerCase().trim();
    const selectedClass = q_filterClass.value.toLowerCase().trim();

    // Filter rows
    const filteredRows = q_allRows.filter(row => {
        const rowClass = row.getAttribute('data-class') ? row.getAttribute('data-class').toLowerCase() : '';
        const rowQuestion = row.getAttribute('data-question') ? row.getAttribute('data-question') : '';
        
        const matchesClass = selectedClass === "" || rowClass === selectedClass;
        const matchesSearch = searchTerm === "" || rowQuestion.includes(searchTerm) || 
                            row.textContent.toLowerCase().includes(searchTerm);
        
        return matchesClass && matchesSearch;
    });

    // Show/hide empty state
    if (filteredRows.length === 0) {
        q_emptyState.style.display = 'block';
        document.getElementById('questionsTable').style.display = 'none';
        document.getElementById('pagination').style.display = 'none';
        return;
    } else {
        q_emptyState.style.display = 'none';
        document.getElementById('questionsTable').style.display = 'table';
        document.getElementById('pagination').style.display = 'flex';
    }

    // Calculate pages
    const totalPages = Math.ceil(filteredRows.length / q_rowsPerPage);
    if (q_currentPage > totalPages) q_currentPage = totalPages || 1;

    // Hide all rows
    q_allRows.forEach(row => row.style.display = 'none');

    // Show current page
    const start = (q_currentPage - 1) * q_rowsPerPage;
    const end = start + q_rowsPerPage;
    filteredRows.slice(start, end).forEach(row => row.style.display = '');

    // Update count
    document.getElementById('qCount').innerText = filteredRows.length;

    q_renderPagination(totalPages);
}

function q_renderPagination(totalPages) {
    const container = document.getElementById('pagination');
    container.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous
    const prevBtn = document.createElement('button');
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Previous';
    prevBtn.className = 'page-btn';
    prevBtn.disabled = (q_currentPage === 1);
    prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (q_currentPage > 1) {
            q_currentPage--;
            q_renderTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    container.appendChild(prevBtn);

    // Numbers
    const maxVisible = 5;
    let startPage = Math.max(1, q_currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.innerText = i;
        btn.className = (i === q_currentPage) ? 'page-btn active' : 'page-btn';
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            q_currentPage = i;
            q_renderTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        container.appendChild(btn);
    }

    // Next
    const nextBtn = document.createElement('button');
    nextBtn.innerHTML = 'Next <i class="fas fa-chevron-right"></i>';
    nextBtn.className = 'page-btn';
    nextBtn.disabled = (q_currentPage === totalPages);
    nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (q_currentPage < totalPages) {
            q_currentPage++;
            q_renderTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    container.appendChild(nextBtn);
}

// Event Listeners
q_searchBox.addEventListener('keyup', () => {
    q_currentPage = 1;
    q_renderTable();
});

q_filterClass.addEventListener('change', () => {
    q_currentPage = 1;
    q_renderTable();
});

q_clearFilters.addEventListener('click', () => {
    q_searchBox.value = '';
    q_filterClass.value = '';
    q_currentPage = 1;
    q_renderTable();
});

// CSV File Upload Handler
document.getElementById('csvFile').addEventListener('change', function() {
    if (this.files.length > 0) {
        document.getElementById('uploadBtn').click();
    }
});

// Initialize
q_renderTable();

// Question Actions
function editQuestion(id) {
    // Placeholder - you'll need to create an edit_question.php page
    alert('Edit functionality coming soon for question #' + id);
}

function deleteQuestion(id, question) {
    if (confirm('Delete this question? "' + question + '"')) {
        window.location = '?delete_id=' + id;
    }
}
</script>



