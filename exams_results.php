<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$classes = ["Year 1","Year 2","Year 3","Year 4","Year 5","Year 6","Year 7","Year 8"];

$class_scores = [];

foreach($classes as $cls){
    $res = mysqli_query($conn, "
        SELECT s.fullname, r.score, r.total, r.date_taken 
        FROM students s
        LEFT JOIN results r ON s.fullname = r.student_name 
        WHERE s.class = '$cls' AND r.score IS NOT NULL
        ORDER BY r.date_taken DESC
    ");

    $students = [];
    $all_scores = [];
    
    while($row = mysqli_fetch_assoc($res)){
        $name = $row['fullname'];
        if (!isset($students[$name])) {
            $students[$name] = [
                'scores' => [],
                'dates' => []
            ];
        }
        $students[$name]['scores'][] = [
            'score' => $row['score'],
            'total' => $row['total'],
            'percentage' => $row['total'] > 0 ? round(($row['score'] / $row['total']) * 100, 1) : 0,
            'date' => $row['date_taken']
        ];
        $students[$name]['dates'][] = $row['date_taken'];
        $all_scores[] = $row['total'] > 0 ? round(($row['score'] / $row['total']) * 100, 1) : 0;
    }

    // Calculate stats for each student
    foreach($students as $name => $data){
        $percentages = array_map(fn($s) => $s['percentage'], $data['scores']);
        $students[$name]['average'] = count($percentages) > 0 ? round(array_sum($percentages) / count($percentages), 1) : 0;
        $students[$name]['recent'] = $data['scores'][0] ?? null;
        $students[$name]['attempts'] = count($data['scores']);
    }

    // Sort by average descending
    uasort($students, function($a, $b) {
        return $b['average'] <=> $a['average'];
    });

    // Calculate class statistics
    $class_avg = count($all_scores) > 0 ? round(array_sum($all_scores) / count($all_scores), 1) : 0;
    $class_median = count($all_scores) > 0 ? $all_scores[intval(count($all_scores)/2)] : 0;
    $class_highest = count($all_scores) > 0 ? max($all_scores) : 0;
    $class_lowest = count($all_scores) > 0 ? min($all_scores) : 0;
    $pass_count = count(array_filter($all_scores, fn($s) => $s >= 50));
    $pass_rate = count($all_scores) > 0 ? round(($pass_count / count($all_scores)) * 100, 1) : 0;

    $class_scores[$cls] = [
        'students' => $students,
        'top10' => array_slice($students, 0, 10, true),
        'stats' => [
            'average' => $class_avg,
            'median' => $class_median,
            'highest' => $class_highest,
            'lowest' => $class_lowest,
            'pass_rate' => $pass_rate,
            'total_students' => count($students),
            'total_attempts' => count($all_scores)
        ]
    ];
}
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-chart-bar"></i>
                    Exams Results
                    <span class="beta-badge">Results tracker</span>
                </h1>
                <p class="page-subtitle">View performance analytics by class and learner</p>
            </div>
        </div>

        <div class="cards-container">
            <?php foreach($classes as $cls): 
                $top = $class_scores[$cls]['top10'];
                $stats = $class_scores[$cls]['stats'];
            ?>
            <div class="class-card" data-class="<?= htmlspecialchars($cls) ?>" role="button" tabindex="0">
                <div class="class-header">
                    <div>
                        <h3><?= htmlspecialchars($cls) ?></h3>
                        <p class="card-meta"><?= $stats['total_students'] ?> students · <?= $stats['total_attempts'] ?> tests</p>
                    </div>
                    <div class="class-stats-mini">
                        <div class="stat-item">
                            <span class="stat-value"><?= $stats['average'] ?>%</span>
                            <span class="stat-label">Avg</span>
                        </div>
                    </div>
                </div>
                
                <div class="top-list">
                    <h4>Top Performers</h4>
                    <ol>
                        <?php $i = 1; foreach($top as $name => $s): 
                            if($s['recent']): ?>
                            <li data-student="<?= htmlspecialchars($name) ?>" class="student-clickable">
                                <span class="rank-medal">
                                    <?php if($i === 1): ?>
                                        <i class="fas fa-crown"></i>
                                    <?php elseif($i === 2): ?>
                                        🥈
                                    <?php elseif($i === 3): ?>
                                        🥉
                                    <?php else: ?>
                                        <span class="rank"><?= $i ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="student-name"><?= htmlspecialchars($name) ?></span>
                                <span class="score"><?= $s['average'] ?>%</span>
                            </li>
                            <?php $i++; endif; endforeach; ?>
                    </ol>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- CLASS STATISTICS MODAL -->
<div id="classModal" class="modal" role="dialog" aria-labelledby="classModalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="classModalTitle">Class Details</h2>
            <button class="modal-close" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Class Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <span class="stat-label">Class Average</span>
                    <span class="stat-value" id="statAvg">0%</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <span class="stat-label">Median Score</span>
                    <span class="stat-value" id="statMedian">0%</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-arrow-up"></i>
                    <span class="stat-label">Highest Score</span>
                    <span class="stat-value highest" id="statHighest">0%</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-arrow-down"></i>
                    <span class="stat-label">Lowest Score</span>
                    <span class="stat-value lowest" id="statLowest">0%</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <span class="stat-label">Pass Rate</span>
                    <span class="stat-value pass" id="statPassRate">0%</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <span class="stat-label">Total Attempts</span>
                    <span class="stat-value" id="statAttempts">0</span>
                </div>
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">

            <!-- Students Table -->
            <h3 style="margin: 0 0 20px 0; font-size: 1.1rem;">All Students Performance</h3>
            <div class="table-wrapper">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Average Score</th>
                            <th>Recent Score</th>
                            <th>Attempts</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="classStudentsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- LEARNER DETAILS MODAL -->
<div id="learnerModal" class="modal" role="dialog" aria-labelledby="learnerModalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="learnerModalTitle">Learner Performance</h2>
            <button class="modal-close" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="learnerContent"></div>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: #1e293b;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #64748b;
    margin: 0 0 30px 0;
}

.page-header {
    margin-bottom: 35px;
}

/* Cards Container */
.cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

/* Class Card */
.class-card {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-radius: 12px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.class-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(59, 130, 246, 0.3);
}

.class-card:focus {
    outline: 2px solid rgba(255, 255, 255, 0.5);
    outline-offset: 2px;
}

.class-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
}

.class-header h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.card-meta {
    margin: 6px 0 0 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.class-stats-mini {
    display: flex;
    gap: 12px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    padding: 10px 16px;
    border-radius: 8px;
    min-width: 70px;
}

.stat-value {
    font-size: 1.3rem;
    font-weight: 700;
}

.stat-label {
    font-size: 0.75rem;
    opacity: 0.9;
}

/* Top List */
.top-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.top-list h4 {
    margin: 0;
    font-size: 0.95rem;
    opacity: 0.95;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.top-list ol {
    margin: 0;
    padding-left: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.top-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    cursor: pointer;
}

.top-list li:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(4px);
}

.top-list li.student-clickable {
    cursor: pointer;
}

.rank-medal {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    font-size: 1.1rem;
}

.rank {
    font-weight: 700;
    font-size: 0.85rem;
}

.student-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.score {
    font-weight: 700;
    min-width: 45px;
    text-align: right;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal.active {
    display: flex;
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 900px;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #1e293b;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
    transition: color 0.2s ease;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.modal-close:hover {
    color: #1e293b;
    background: #e2e8f0;
}

.modal-body {
    overflow-y: auto;
    padding: 30px;
    flex: 1;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    text-align: center;
}

.stat-card i {
    font-size: 1.3rem;
    color: #3b82f6;
}

.stat-card .stat-label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 500;
}

.stat-card .stat-value {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-card .stat-value.highest {
    color: #16a34a;
}

.stat-card .stat-value.lowest {
    color: #dc2626;
}

.stat-card .stat-value.pass {
    color: #3b82f6;
}

/* Table */
.table-wrapper {
    overflow-x: auto;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.results-table thead {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.results-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.results-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
}

.results-table tbody tr:hover {
    background: #f8fafc;
}

.results-table tr.student-row {
    cursor: pointer;
}

.results-table tr.student-row .student-name-cell {
    color: #3b82f6;
    font-weight: 500;
}

.results-table tr.student-row:hover .student-name-cell {
    text-decoration: underline;
}

.view-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.view-btn:hover {
    background: linear-gradient(135deg, #bfdbfe, #93c5fd);
    transform: translateY(-2px);
}

/* Learner Details */
.learner-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.learner-stat {
    text-align: center;
    padding: 15px;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: 10px;
    border-left: 4px solid #3b82f6;
}

.learner-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0284c7;
}

.learner-stat-label {
    font-size: 0.85rem;
    color: #0369a1;
    margin-top: 4px;
}

.attempts-table {
    width: 100%;
    border-collapse: collapse;
}

.attempts-table thead {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.attempts-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
}

.attempts-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.score-badge {
    display: inline-block;
    padding: 4px 10px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #166534;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .cards-container {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .modal-content {
        max-height: 90vh;
    }

    .results-table {
        font-size: 12px;
    }

    .results-table th,
    .results-table td {
        padding: 8px;
    }
}
</style>

<script>
const classScoresData = <?= json_encode($class_scores) ?>;

// Handle class card clicks
document.querySelectorAll('.class-card').forEach(card => {
    card.addEventListener('click', showClassModal);
    card.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            showClassModal.call(card);
        }
    });
});

// Handle learner name clicks
document.addEventListener('click', (e) => {
    if (e.target.closest('.student-clickable')) {
        const li = e.target.closest('.student-clickable');
        const cls = li.closest('.class-card').getAttribute('data-class');
        const student = li.getAttribute('data-student');
        showLearnerModal(cls, student);
    }
});

function showClassModal() {
    const cls = this.getAttribute('data-class');
    const stats = classScoresData[cls].stats;
    const students = classScoresData[cls].students;

    // Update stats
    document.getElementById('statAvg').textContent = stats.average + '%';
    document.getElementById('statMedian').textContent = stats.median + '%';
    document.getElementById('statHighest').textContent = stats.highest + '%';
    document.getElementById('statLowest').textContent = stats.lowest + '%';
    document.getElementById('statPassRate').textContent = stats.pass_rate + '%';
    document.getElementById('statAttempts').textContent = stats.total_attempts;

    // Build students table
    let html = '';
    for (let name in students) {
        const s = students[name];
        if (s.recent) {
            html += `
                <tr class="student-row" data-student="${name}" data-class="${cls}">
                    <td class="student-name-cell">${name}</td>
                    <td><strong>${s.average}%</strong></td>
                    <td>${s.recent.percentage}%</td>
                    <td>${s.attempts}</td>
                    <td>
                        <button class="view-btn" onclick="showLearnerModal('${cls}', '${name}')">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
        }
    }

    document.getElementById('classStudentsBody').innerHTML = html;
    document.getElementById('classModalTitle').textContent = cls + ' - Full Performance';
    document.getElementById('classModal').classList.add('active');
}

function showLearnerModal(cls, learner) {
    const students = classScoresData[cls].students;
    const learnerData = students[learner];

    if (!learnerData) return;

    let avgScore = learnerData.average;
    let bestScore = Math.max(...learnerData.scores.map(s => s.percentage));
    let worstScore = Math.min(...learnerData.scores.map(s => s.percentage));

    let html = `
        <div class="learner-stats">
            <div class="learner-stat">
                <div class="learner-stat-value">${avgScore}%</div>
                <div class="learner-stat-label">Average</div>
            </div>
            <div class="learner-stat">
                <div class="learner-stat-value">${bestScore}%</div>
                <div class="learner-stat-label">Best Score</div>
            </div>
            <div class="learner-stat">
                <div class="learner-stat-value">${worstScore}%</div>
                <div class="learner-stat-label">Worst Score</div>
            </div>
            <div class="learner-stat">
                <div class="learner-stat-value">${learnerData.attempts}</div>
                <div class="learner-stat-label">Attempts</div>
            </div>
        </div>

        <h3 style="margin: 25px 0 15px 0; font-size: 1.1rem;">Test History</h3>
        <div class="table-wrapper">
            <table class="attempts-table">
                <thead>
                    <tr>
                        <th>Test Date</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    `;

    learnerData.scores.forEach(score => {
        const status = score.percentage >= 50 ? '✓ Pass' : '✗ Fail';
        const statusColor = score.percentage >= 50 ? '#16a34a' : '#dc2626';
        html += `
            <tr>
                <td>${new Date(score.date).toLocaleDateString()}</td>
                <td><strong>${score.score} / ${score.total}</strong></td>
                <td><span class="score-badge" style="background: ${score.percentage >= 50 ? 'linear-gradient(135deg, #dcfce7, #bbf7d0)' : 'linear-gradient(135deg, #fee2e2, #fecaca)'}; color: ${statusColor};">${score.percentage}%</span></td>
                <td style="color: ${statusColor}; font-weight: 600;">${status}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('learnerContent').innerHTML = html;
    document.getElementById('learnerModalTitle').textContent = learner + ' (' + cls + ')';
    
    // Close class modal if open
    document.getElementById('classModal').classList.remove('active');
    document.getElementById('learnerModal').classList.add('active');
}

// Close Modal Functions
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const modal = e.target.closest('.modal');
        modal.classList.remove('active');
    });
});

// Close on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// Close on backdrop click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});
</script>


<?php include 'includes/footer.php'; ?>
