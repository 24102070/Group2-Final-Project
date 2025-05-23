<?php
session_start();
include '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $roles = ['users' => 'User', 'companies' => 'Company', 'freelancers' => 'Freelancer'];
        $foundRole = null;

        foreach ($roles as $table => $roleName) {
            $sql = "SELECT * FROM $table WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $foundRole = $roleName;
                break;
            }
        }

        if (!$foundRole) {
            $error = "No account found with that email address.";
        } else {
            // Save data in session and redirect to manual send page
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role'] = $foundRole;
            $_SESSION['reset_message'] = $message;
            header("Location: manual_send.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/styles.css" />
    <link rel="stylesheet" href="../assets/login.css" />
</head>
<body>

<div class="login-container">
    <h2>Forgot Password</h2>

    <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
        <label for="email">Registered Email Address</label>
        <input type="email" id="email" name="email" style = "background-color:rgba(234, 234, 234, 0.96);" required />

        <label for="message">Additional Message (Optional)</label>
        <textarea id="message" name="message" rows="4" placeholder="You can add any details here to help the admin verify your identity."></textarea>

        <button type="submit">Send Reset Request</button>
    </form>

    <p><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>
