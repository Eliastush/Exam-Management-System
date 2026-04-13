<?php
include 'Includes/header.php';

$conn = mysqli_connect("localhost", "root", "", "quiz_system");
if (!$conn) die("Connection failed");

// Mock user data - in a real app, this would come from session/user table
$user = [
    'name' => 'Mr. Elias',
    'email' => 'elias@school.edu',
    'role' => 'ICT Teacher',
    'join_date' => '2023-01-15',
    'last_login' => date('Y-m-d H:i:s'),
    'profile_pic' => 'https://via.placeholder.com/150'
];
?>

<div class="main-content">
    <div class="view-card">
        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your account settings and preferences.</p>
        </div>

        <div class="profile-grid">
            <!-- Profile Info -->
            <div class="card">
                <div class="profile-header">
                    <img src="<?= $user['profile_pic'] ?>" alt="Profile Picture" class="profile-avatar">
                    <div class="profile-details">
                        <h2><?= htmlspecialchars($user['name']) ?></h2>
                        <p class="role-badge"><?= htmlspecialchars($user['role']) ?></p>
                        <p class="join-date">Joined: <?= date('F Y', strtotime($user['join_date'])) ?></p>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php
                            $total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'];
                            echo $total_students;
                            ?>
                        </span>
                        <span class="stat-label">Students Managed</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php
                            $total_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM results"))['c'];
                            echo $total_quizzes;
                            ?>
                        </span>
                        <span class="stat-label">Quizzes Conducted</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php
                            $avg_score = round(mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score/total*100) as a FROM results WHERE total>0"))['a'] ?? 0, 1);
                            echo $avg_score . '%';
                            ?>
                        </span>
                        <span class="stat-label">Average Score</span>
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="card">
                <h3><i class="fas fa-cog"></i> Account Settings</h3>
                <form class="settings-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" value="<?= htmlspecialchars($user['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" placeholder="Confirm new password">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Activity Log -->
            <div class="card">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <div class="activity-list">
                    <?php
                    // Get recent activities
                    $activities = [];

                    // Recent logins
                    $activities[] = [
                        'icon' => 'fas fa-sign-in-alt',
                        'title' => 'Logged in',
                        'time' => '2 hours ago',
                        'color' => 'success'
                    ];

                    // Recent student additions
                    $recent_students = mysqli_query($conn, "SELECT fullname, created_at FROM students ORDER BY id DESC LIMIT 3");
                    while ($student = mysqli_fetch_assoc($recent_students)) {
                        $activities[] = [
                            'icon' => 'fas fa-user-plus',
                            'title' => 'Added student: ' . htmlspecialchars($student['fullname']),
                            'time' => timeAgo(strtotime($student['created_at'] ?? 'now')),
                            'color' => 'primary'
                        ];
                    }

                    // Recent quiz results
                    $recent_results = mysqli_query($conn, "SELECT r.*, s.fullname FROM results r LEFT JOIN students s ON r.student_name = s.fullname ORDER BY r.date_taken DESC LIMIT 3");
                    while ($result = mysqli_fetch_assoc($recent_results)) {
                        $percentage = round(($result['score'] / $result['total']) * 100, 1);
                        $activities[] = [
                            'icon' => 'fas fa-chart-bar',
                            'title' => htmlspecialchars($result['fullname']) . ' scored ' . $percentage . '%',
                            'time' => timeAgo(strtotime($result['date_taken'])),
                            'color' => 'info'
                        ];
                    }

                    // Display activities
                    foreach (array_slice($activities, 0, 10) as $activity) {
                        echo '<div class="activity-item">';
                        echo '<i class="' . $activity['icon'] . ' activity-icon activity-' . $activity['color'] . '"></i>';
                        echo '<div class="activity-content">';
                        echo '<div class="activity-title">' . $activity['title'] . '</div>';
                        echo '<div class="activity-time">' . $activity['time'] . '</div>';
                        echo '</div>';
                        echo '</div>';
                    }

                    function timeAgo($timestamp) {
                        $time = time() - $timestamp;
                        if ($time < 60) return $time . ' seconds ago';
                        if ($time < 3600) return floor($time / 60) . ' minutes ago';
                        if ($time < 86400) return floor($time / 3600) . ' hours ago';
                        if ($time < 604800) return floor($time / 86400) . ' days ago';
                        return date('M j', $timestamp);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
}

@media (min-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #22c55e;
}

.profile-details h2 {
    margin: 0 0 5px 0;
    color: #0f172a;
    font-size: 1.8rem;
}

.role-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-block;
    margin-bottom: 8px;
}

.join-date {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8fafc;
    border-radius: 12px;
}

.stat-number {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: #22c55e;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: #64748b;
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    font-size: 16px;
    min-width: 20px;
    padding: 8px;
    border-radius: 50%;
}

.activity-primary { background: #dcfce7; color: #166534; }
.activity-success { background: #dcfce7; color: #166534; }
.activity-info { background: #dbeafe; color: #1e40af; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: #0f172a;
    margin-bottom: 2px;
}

.activity-time {
    font-size: 0.8rem;
    color: #64748b;
}
</style>