<?php
session_start();
include '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // USERS
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            if ($user['role'] == 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role'] = "admin";
                header("Location: ../approvals/adminDash.php");
                exit();
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = "user";
                header("Location: ../dashboard/dashboard.php");
                exit();
            }
        } else {
            $error = "Incorrect password. Please try again.";
        }
    }

    // COMPANIES
    $sql = "SELECT * FROM companies WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $company = $result->fetch_assoc();

    if ($company) {
        if (password_verify($password, $company['password'])) {
            if ($company['approval'] !== 'Approved') {
                $error = "Your company account is pending approval.";
            } else {
                $_SESSION['user_id'] = $company['id'];
                $_SESSION['role'] = "company";
                header("Location: ../admin/dashboard.php");
                exit();
            }
        } else {
            $error = "Incorrect password. Please try again.";
        }
    }

    // FREELANCERS
    $sql = "SELECT * FROM freelancers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $freelancer = $result->fetch_assoc();

    if ($freelancer) {
        if (password_verify($password, $freelancer['password'])) {
            if ($freelancer['approval'] !== 'Approved') {
                $error = "Your freelancer account is pending approval.";
            } else {
                $_SESSION['user_id'] = $freelancer['id'];
                $_SESSION['role'] = "freelancer";
                header("Location: ../admin/freelancer_dashboard.php");
                exit();
            }
        } else {
            $error = "Incorrect password. Please try again.";
        }
    }

    // If not found in any table
    if (!$user && !$company && !$freelancer) {
        $error = "Invalid email or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        .error-box {
            background-color: #ffdddd;
            color: #d8000c;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }

        .login-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .login-container input,
        .login-container button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input[type="password"],
        .password-container input[type="text"] {
            width: 80%;
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: -100px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .login-container button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #218838;
        }
    </style>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
    </script>
</head>
<body>

    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>

            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <input type="checkbox" class="toggle-password" onclick="togglePassword()" title="Show Password">
            </div>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>

</body>
</html>
