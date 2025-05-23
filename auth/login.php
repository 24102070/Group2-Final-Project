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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/login.css">

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
    </script>
</head>
<body>

<div class="login-container">
    <div class="login-symbol">
        <!-- You can replace this with an image or any icon you prefer -->
        <img src="../assets/IMAGES/SYMBOL.png" alt="Logo" style="width: 250px; height: 100px;">
    </div>
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
    <label for="email" style="float: center; margin-top: 10px;">EMAIL ADDRESS</label>
<input type="email" id="email" name="email" style = "background-color:rgba(234, 234, 234, 0.96);"required>

<label for="password" style="float: Center; margin-top: 10px;">PASSWORD</label>
<div class="password-container">
    <input type="password" id="password" name="password" required>
    <i class="fa fa-eye toggle-password" onclick="togglePassword()"></i>
</div>


        <button type="submit">Login</button>
    </form>
        <p><a href="forgot_password.php">Forgot your password?</a></p>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>


</body>
</html>