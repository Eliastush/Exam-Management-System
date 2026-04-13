<?php
// 404.php - Professional Page Not Found
include 'Includes/header.php';
?>

<div class="main-content">
    <div class="error-card">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Page Not Found</h2>
            
            <p class="error-message">
                Sorry, the page you are looking for doesn't exist or has been moved.
            </p>

            <div class="error-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go to Dashboard
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.error-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
    padding: 90px 40px;
    max-width: 680px;
    margin: 80px auto;
    text-align: center;
}

.error-icon {
    font-size: 6rem;
    color: #ef4444;
    margin-bottom: 20px;
}

.error-code {
    font-size: 7.5rem;
    font-weight: 800;
    color: #1e2937;
    margin: 0 0 10px 0;
    line-height: 1;
}

.error-title {
    font-size: 1.9rem;
    color: #334155;
    margin-bottom: 20px;
}

.error-message {
    font-size: 1.15rem;
    color: #64748b;
    max-width: 420px;
    margin: 0 auto 40px;
    line-height: 1.65;
}

.error-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.btn-primary {
    background: #159d10;
    color: white;
}

.btn-primary:hover {
    background: #0f7a0d;
    transform: translateY(-3px);
}

.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-secondary:hover {
    background: #475569;
}

/* Responsive */
@media (max-width: 768px) {
    .error-card {
        margin: 40px 15px;
        padding: 60px 25px;
    }
    .error-code {
        font-size: 5.5rem;
    }
}
</style>

<?php include 'Includes/footer.php'; ?>