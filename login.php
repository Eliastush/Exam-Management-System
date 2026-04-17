<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['username']) || isset($_SESSION['admin_name'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === '1234') {
        $_SESSION['username'] = $username;
        $_SESSION['admin_name'] = 'Mr. Elias';
        $_SESSION['admin_email'] = 'info@school.info';
        $_SESSION['admin_role'] = 'System Administrator';
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Mustard Seed ICT</title>
    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            margin: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .login-shell {
            width: min(100%, 450px);
            padding: 32px;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .login-shell h1 {
            margin: 0 0 12px;
            font-size: 28px;
            color: #0f172a;
        }

        .login-shell p {
            margin: 0 0 28px;
            color: #64748b;
            line-height: 1.6;
        }

        .login-shell .brand-mark {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            font-size: 28px;
        }

        .login-card {
            display: grid;
            gap: 18px;
            text-align: left;
        }

        .login-card label {
            font-size: 13px;
            font-weight: 700;
            color: #334151;
        }

        .login-card input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            background: #f8fafc;
        }

        .login-card input:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
        }

        .login-actions {
            display: grid;
            gap: 12px;
        }

        .login-actions button {
            width: 100%;
        }

        .login-footer {
            font-size: 13px;
            color: #64748b;
            margin-top: 20px;
        }

        .alert-box {
            padding: 14px 18px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 14px;
            margin-bottom: 8px;
        }

        @media (max-width: 520px) {
            .login-shell {
                padding: 24px;
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="brand-mark"><i class="fas fa-seedling"></i></div>
        <h1>Welcome Back</h1>
        <p>Login to your Mustard Seed ICT admin dashboard.</p>

        <?php if ($error): ?>
            <div class="alert-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-card" method="POST" autocomplete="off">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="login-actions">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

        <div class="login-footer">Use admin / 1234 to access the dashboard.</div>
    </div>
</body>
</html>

