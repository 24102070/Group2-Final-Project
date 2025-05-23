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
        $profession = $_POST['profession'];
        $description = $_POST['description'];
        $minimum_fee = $_POST['minimum_fee'];
        $resume_file = $_FILES['resume_file'];

        $upload_dir = __DIR__ . "/../uploads/";
        $resume_path = $upload_dir . basename($resume_file["name"]);

        if (move_uploaded_file($resume_file["tmp_name"], $resume_path)) {
            $sql = "INSERT INTO freelancers (name, email, password, profession, description, minimum_fee, resume_file, status, approval) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Available', 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $name, $email, $password, $profession, $description, $minimum_fee, $resume_path);

            if ($stmt->execute()) {
                header("Location: login.php?success=registered");
                exit();
            } else {
                $error = "Error registering freelancer.";
            }
        } else {
            $error = "Failed to upload resume.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Freelancer Registration</title>
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
    <h2>Freelancer Registration</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="register_freelancer.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <div class="password-container">
            <input type="password" id="password-freelancer" name="password" placeholder="Password" required />
            <i class="fa fa-eye toggle-password" onclick="togglePassword('password-freelancer')"></i>
        </div>
        <input type="text" name="profession" placeholder="Profession" required />
        <textarea name="description" placeholder="Describe Your Services" required></textarea>
        <input type="number" name="minimum_fee" placeholder="Minimum Fee" required />
        <label>Upload Resume:</label>
        <input type="file" name="resume_file" accept=".pdf,.doc,.docx" required />
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Sign in now!</a></p>
</div>
</body>
</html>
