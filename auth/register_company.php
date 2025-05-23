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
        $description = $_POST['description'];
        $minimum_fee = $_POST['minimum_fee'];
        $cert_file = $_FILES['cert_file'];

        $upload_dir = __DIR__ . "/../uploads/";
        $cert_path = $upload_dir . basename($cert_file["name"]);

        if (move_uploaded_file($cert_file["tmp_name"], $cert_path)) {
            $sql = "INSERT INTO companies (name, email, password, description, minimum_fee, cert_file, status, approval) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Available', 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $email, $password, $description, $minimum_fee, $cert_path);

            if ($stmt->execute()) {
                header("Location: login.php?success=registered");
                exit();
            } else {
                $error = "Error registering company.";
            }
        } else {
            $error = "Failed to upload business certificate.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Company Registration</title>
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
    <h2>Company Registration</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="register_company.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Company Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <div class="password-container">
            <input type="password" id="password-company" name="password" placeholder="Password" required />
            <i class="fa fa-eye toggle-password" onclick="togglePassword('password-company')"></i>
        </div>
        <textarea name="description" placeholder="Business Description" required></textarea>
        <input type="number" name="minimum_fee" placeholder="Minimum Fee" required />
        <label>Upload Business Certificate:</label>
        <input type="file" name="cert_file" accept=".pdf,.jpg,.png" required />
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Sign in now!</a></p>
</div>
</body>
</html>
