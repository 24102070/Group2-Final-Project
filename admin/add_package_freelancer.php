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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/add_package_freelancer.css">
</head>
<body>
    <!-- Floating decorative elements -->
    <i class="floating-icon heart fas fa-heart"></i>
    <i class="floating-icon star fas fa-star"></i>

    <div class="main-container">
        <h1>Create New Package</h1>
        
        <form method="POST" action="add_package_freelancer.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="package_name">Package Name</label>
                <input type="text" id="package_name" name="package_name" required placeholder="e.g., Premium Wedding Package">
            </div>

            <div class="form-group">
                <label for="package_details">Package Details</label>
                <textarea id="package_details" name="package_details" required placeholder="Describe what this package includes..."></textarea>
            </div>

            <div class="form-group">
                <label for="package_inclusions">Package Inclusions</label>
                <textarea id="package_inclusions" name="package_inclusions" required placeholder="List all inclusions (separate with commas or bullets)..."></textarea>
            </div>

            <div class="form-group">
                <label for="package_price">Package Price (₱)</label>
                <input type="number" id="package_price" name="package_price" required placeholder="Enter price in PHP">
            </div>

            <div class="form-group">
                <label for="package_image">Package Image</label>
                <div class="file-input-wrapper">
                    <div class="file-input-button">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                         
        <input type="file" id="package_image" name="package_image" accept="image/*"><br><br>
                    </div>
                    <div class="file-name" id="file-name">No file chosen</div>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-plus-circle"></i> Create Package
            </button>
        </form>

        <a href="view_freelance_packages.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to My Packages
        </a>
    </div>

    <script>
        // Display selected file name
        document.getElementById('package_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>