<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];

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
    $sql = "INSERT INTO packages (company_id, name, details, inclusions, price, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssds", $company_id, $package_name, $package_details, $package_inclusions, $package_price, $image_path);
    
    if ($stmt->execute()) {
        echo "Package added successfully!";
        // Redirect to the company packages page after successful addition
        header("Location: view_packages.php"); 
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
    <title>Add Wedding Package</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #e4816c;
            --primary-dark: #d56e59;
            --accent-color: #f6c361;
            --light-bg: #fff9f6;
            --text-color: #5c3a2e;
            --light-text: #a08679;
            --border-color: #f3c9b5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('https://images.unsplash.com/photo-1550005809-91ad75fb315f?q=80&w=2938&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed;
            background-size: cover;
            color: var(--text-color);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: rgba(255, 249, 246, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2.2rem;
            position: relative;
            padding-bottom: 15px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 1rem;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 16px;
            color: var(--text-color);
            transition: all 0.3s;
            background-color: rgba(255, 255, 255, 0.8);
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(228, 129, 108, 0.2);
            outline: none;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 16px;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(228, 129, 108, 0.4);
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(228, 129, 108, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn i {
            margin-right: 10px;
        }

        .btn-view {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            background-color: #5c6bc0;
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 20px;
            width: 100%;
            justify-content: center;
        }

        .btn-view:hover {
            background-color: #3f51b5;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .file-upload-wrapper {
            position: relative;
            margin-bottom: 25px;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.5);
        }

        .file-upload-label:hover {
            border-color: var(--primary-color);
            background-color: rgba(255, 255, 255, 0.8);
        }

        .file-upload-label i {
            font-size: 24px;
            margin-right: 10px;
            color: var(--primary-color);
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 10px;
            }
            
            .container {
                padding: 25px 15px;
            }
            
            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-gift"></i> Create New Package</h1>
    
    <form method="POST" action="add_package.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="package_name">Package Name</label>
            <input type="text" id="package_name" name="package_name" placeholder="Enter package name" required>
        </div>
        
        <div class="form-group">
            <label for="package_details">Package Details</label>
            <textarea id="package_details" name="package_details" placeholder="Describe what this package includes" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="package_inclusions">Package Inclusions</label>
            <textarea id="package_inclusions" name="package_inclusions" placeholder="List all inclusions (separate with commas)" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="package_price">Package Price (₱)</label>
            <input type="number" id="package_price" name="package_price" placeholder="Enter price in PHP" step="0.01" min="0" required>
        </div>
        
        <div class="form-group">
            <label>Package Image</label>
            <div class="file-upload-wrapper">
                <label class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Click to upload image or drag and drop</span>
                    <input type="file" id="package_image" name="package_image" class="file-upload-input" accept="image/*">
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Package
        </button>
    </form>

    <a href="view_packages.php" class="btn-view">
        <i class="fas fa-eye"></i> View All Packages
    </a>
</div>

</body>
</html>