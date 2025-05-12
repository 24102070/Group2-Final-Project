<?php
include '../config/db.php';

$error = ""; // To hold error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $password_raw = $_POST['password'];

    // Password validation
    if (
        strlen($password_raw) < 8 ||
        !preg_match('/[A-Za-z]/', $password_raw) ||
        !preg_match('/\d/', $password_raw)
    ) {
        $error = "Password must be at least 8 characters long and contain both letters and numbers.";
    } else {
        // If valid, hash the password
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        if ($role == "user") {
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
                }
            }
        } elseif ($role == "company") {
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
                }
            }
        } elseif ($role == "freelancer") {
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
                }
            }
        } elseif ($role == "admin") {
            $name = $_POST['name'];
            $email = $_POST['email'];

            $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $password);
            if ($stmt->execute()) {
                header("Location: login.php?success=registered");
                exit();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="assets/hompage.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/register.css">

  
    <script>
        function showForm(role) {
            document.getElementById('user-form').style.display = (role === 'user') ? 'block' : 'none';
            document.getElementById('company-form').style.display = (role === 'company') ? 'block' : 'none';
            document.getElementById('freelancer-form').style.display = (role === 'freelancer') ? 'block' : 'none';
            document.getElementById('admin-form').style.display = (role === 'admin') ? 'block' : 'none';
        }

        function togglePassword(id) {
            var field = document.getElementById(id);
            field.type = (field.type === "password") ? "text" : "password";
        }

    </script>
</head>
<body>

<div class="register-container">
<img src="../assets/IMAGES/SYMBOL.png" alt="Logo" style="width: 250px; height: 100px;">
    <h2>REGISTER</h2>
    <label>Select Role:</label>
    <select onchange="showForm(this.value)">
        <option value="">-- Select --</option>
        <option value="user">User</option>
        <option value="company">Company</option>
        <option value="freelancer">Freelancer</option>
        <option value="admin">Admin</option>
    </select>

    <?php if (!empty($error)): ?>
    <div class="error">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- User Registration -->
    <form id="user-form" action="register.php" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="role" value="user">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="password-container">
            <input type="password" id="password-user" name="password" placeholder="Password" required>
            <i class="fa fa-eye toggle-password" onclick="togglePassword('password-user')"></i>
        </div>
        <input type="text" name="contact_number" placeholder="Contact Number" required>
        <label>Upload Valid ID:</label>
        <input type="file" name="valid_id_file" accept=".pdf,.jpg,.png" required>
        <button type="submit">Register</button>
    </form>

    <!-- Admin Registration -->
    <form id="admin-form" action="register.php" method="POST" style="display:none;">
        <input type="hidden" name="role" value="admin">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="password-container">
            <input type="password" id="password-admin" name="password" placeholder="Password" required>
            <i class="fa fa-eye toggle-password" onclick="togglePassword('password-admin')"></i>
        </div>
        <button type="submit">Register</button>
    </form>

    <!-- Company Registration -->
    <form id="company-form" action="register.php" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="role" value="company">
        <input type="text" name="name" placeholder="Company Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="password-container">
            <input type="password" id="password-company" name="password" placeholder="Password" required>
            <i class="fa fa-eye toggle-password" onclick="togglePassword('password-company')"></i>
        </div>
        <textarea name="description" placeholder="Business Description" required></textarea>
        <input type="number" name="minimum_fee" placeholder="Minimum Fee" required>
        <label>Upload Business Certificate:</label>
        <input type="file" name="cert_file" accept=".pdf,.jpg,.png" required>
        <button type="submit">Register</button>
    </form>

    <!-- Freelancer Registration -->
    <form id="freelancer-form" action="register.php" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="role" value="freelancer">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="password-container">
            <input type="password" id="password-freelancer" name="password" placeholder="Password" required>
             <i class="fa fa-eye toggle-password" onclick="togglePassword('password-freelancer')"></i>
        </div>
        <input type="text" name="profession" placeholder="Profession" required>
        <textarea name="description" placeholder="Describe Your Services" required></textarea>
        <input type="number" name="minimum_fee" placeholder="Minimum Fee" required>
        <label>Upload Resume:</label>
        <input type="file" name="resume_file" accept=".pdf,.jpg,.png" required>
        <button type="submit">Register</button>
    </form>

</body>
</html>