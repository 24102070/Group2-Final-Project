<?php
include '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_raw = $_POST['password'];

    if (
        strlen($password_raw) < 8 ||
        !preg_match('/[A-Za-z]/', $password_raw) ||
        !preg_match('/\d/', $password_raw)
    ) {
        $error = "Password must be at least 8 characters long and contain both letters and numbers.";
    } else {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        $name = $_POST['name'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact_number'];
        $valid_id_file = $_FILES['valid_id_file'];

        $upload_dir = __DIR__ . "/../uploads/";
        $valid_id_path = $upload_dir . basename($valid_id_file["name"]);

        if (move_uploaded_file($valid_id_file["tmp_name"], $valid_id_path)) {
            $sql = "INSERT INTO users (name, email, password, role, contact_number, valid_id_file) 
                    VALUES (?, ?, ?, 'user', ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $password, $contact_number, $valid_id_path);

            if ($stmt->execute()) {
                header("Location: login.php?success=registered");
                exit();
            } else {
                $error = "Error registering user.";
            }
        } else {
            $error = "Failed to upload valid ID.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Registration</title>
    <link rel="stylesheet" href="../assets/register.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script>
        function togglePassword(id) {
            var field = document.getElementById(id);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</head>
<body>
<div class="register-container">
    <h2>User Registration</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <div class="password-container">
            <input type="password" id="password-user" name="password" placeholder="Password" required />
            <i class="fa fa-eye toggle-password" onclick="togglePassword('password-user')"></i>
        </div>
        <input type="text" name="contact_number" placeholder="Contact Number" required />
        <label>Upload Valid ID:</label>
        <input type="file" name="valid_id_file" accept=".pdf,.jpg,.png" required />
        <button type="submit">Register</button>
    </form>
       <p>Already have an account? <a href="login.php">Sign in now!</a></p>
</div>
</body>
</html>
