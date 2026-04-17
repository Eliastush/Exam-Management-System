<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='main-content'><div class='view-card'><h2>Error</h2><p>Invalid Question ID.</p></div></div>";
    include 'Includes/footer.php';
    exit;
}

// Fetch question details
$query = "SELECT * FROM questions WHERE id = $id";
$result = mysqli_query($conn, $query);
$question = mysqli_fetch_assoc($result);

if (!$question) {
    echo "<div class='main-content'><div class='view-card'><h2>Not Found</h2><p>Question not found.</p></div></div>";
    include 'Includes/footer.php';
    exit;
}
?>

<div class="main-content">
    <div class="view-card">
        
        <h1 class="page-title">Question #<?= $question['id'] ?></h1>
        <p class="page-subtitle">Class Level: <strong><?= htmlspecialchars($question['class_level']) ?></strong></p>

        <div class="question-box">
            <h3>Question:</h3>
            <p class="question-text"><?= nl2br(htmlspecialchars($question['question'])) ?></p>
        </div>

        <div class="options-grid">
            <div class="option">
                <span class="label">A</span>
                <span class="text"><?= htmlspecialchars($question['option_a']) ?></span>
            </div>
            <div class="option">
                <span class="label">B</span>
                <span class="text"><?= htmlspecialchars($question['option_b']) ?></span>
            </div>
            <div class="option">
                <span class="label">C</span>
                <span class="text"><?= htmlspecialchars($question['option_c']) ?></span>
            </div>
        </div>

        <div class="correct-answer">
            <strong>Correct Answer:</strong> 
            <span class="answer"><?= strtoupper($question['correct_answer']) ?></span>
        </div>

        <div class="actions">
            <a href="question_edit.php?id=<?= $question['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Question
            </a>
            <a href="questions.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to All Questions
            </a>
        </div>

    </div>
</div>

<style>
.view-card {
    background: white;
    border-radius: 18px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
    padding: 40px;
    max-width: 900px;
    margin: 0 auto;
}

.page-title {
    font-size: 2.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
}

.page-subtitle {
    color: #64748b;
    font-size: 1.1rem;
    margin-bottom: 35px;
}

.question-box {
    background: #f8fafc;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    border-left: 5px solid #159d10;
}

.question-text {
    font-size: 1.15rem;
    line-height: 1.7;
    color: #1e293b;
}

.options-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    margin-bottom: 30px;
}

.option {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.option .label {
    font-weight: 700;
    color: #159d10;
    font-size: 1.1rem;
    min-width: 28px;
}

.option .text {
    flex: 1;
    font-size: 1.05rem;
}

.correct-answer {
    background: #ecfdf5;
    padding: 18px 22px;
    border-radius: 12px;
    border: 2px solid #159d10;
    font-size: 1.15rem;
    margin-bottom: 35px;
}

.correct-answer .answer {
    color: #159d10;
    font-weight: 700;
    margin-left: 8px;
}

.actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #159d10;
    color: white;
}

.btn-primary:hover {
    background: #0f7a0d;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-secondary:hover {
    background: #475569;
}

@media (max-width: 768px) {
    .view-card {
        padding: 25px 20px;
    }
    .actions {
        flex-direction: column;
    }
}
</style>
