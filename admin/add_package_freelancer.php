<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $package_name = $_POST['package_name'];
    $package_details = $_POST['package_details'];
    $package_inclusions = $_POST['package_inclusions'];
    $package_price = $_POST['package_price'];

    // Handle file upload
    if (isset($_FILES['package_image']) && $_FILES['package_image']['error'] == 0) {
        $file_name = $_FILES['package_image']['name'];
        $file_tmp = $_FILES['package_image']['tmp_name'];
        $file_size = $_FILES['package_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Set allowed file types
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        // Check file extension
        if (in_array($file_ext, $allowed_ext)) {
            // Check file size (limit to 5MB)
            if ($file_size <= 5 * 1024 * 1024) {
                // Generate a unique name for the file
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_dir = '../uploads/';
                $upload_path = $upload_dir . $new_file_name;

                // Move the uploaded file to the server
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_path = "uploads/" . $new_file_name;
                } else {
                    $error_message = "Failed to upload image.";
                }
            } else {
                $error_message = "File size is too large. Maximum allowed size is 5MB.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } else {
        // If no image is uploaded, set image path to NULL (as string)
        $image_path = NULL;
    }

    // Insert package into the database
    $sql = "INSERT INTO packages_freelancers (freelancer_id, name, details, inclusions, price, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssds", $freelancer_id, $package_name, $package_details, $package_inclusions, $package_price, $image_path);
    
    if ($stmt->execute()) {
        echo "Package added successfully!";
        // Redirect to the freelancer packages page after successful addition
        header("Location: view_freelance_packages.php"); 
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Freelancer Package</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4cae4c;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 16px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Add a New Freelancer Package</h1>
    <form method="POST" action="add_package_freelancer.php" enctype="multipart/form-data">
        <label for="package_name">Package Name:</label>
        <input type="text" id="package_name" name="package_name" required><br><br>

        <label for="package_details">Package Details:</label>
        <textarea id="package_details" name="package_details" required></textarea><br><br>

        <label for="package_inclusions">Package Inclusions:</label>
        <textarea id="package_inclusions" name="package_inclusions" required></textarea><br><br>

        <label for="package_price">Package Price (PHP):</label>
        <input type="number" id="package_price" name="package_price" required><br><br>

        <label for="package_image">Package Image:</label>
        <input type="file" id="package_image" name="package_image" accept="image/*"><br><br>

        <button type="submit">Add Package</button>
    </form>

    <br><br>

    <!-- View More Button -->
    <a href="view_freelance_packages.php">View All Packages</a>

</div>

</body>
</html>
