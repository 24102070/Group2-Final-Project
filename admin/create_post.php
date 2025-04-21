<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if caption is set and not empty
    if (isset($_POST['caption']) && !empty($_POST['caption'])) {
        $caption = $_POST['caption'];
    } else {
        echo "Caption is required.";
        exit();
    }

    $media_path = null;
    $media_type = null;

    // Check if a file has been uploaded
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $media_name = $_FILES['media']['name'];
        $media_tmp = $_FILES['media']['tmp_name'];
        $media_type = null;

        // Determine file type (image or video)
        $media_ext = pathinfo($media_name, PATHINFO_EXTENSION);
        $target_dir = "../uploads/";

        // Check if it's an image or video
        if (in_array(strtolower($media_ext), ['jpg', 'jpeg', 'png', 'gif'])) {
            $media_type = 'image';
        } elseif (in_array(strtolower($media_ext), ['mp4', 'avi', 'mov'])) {
            $media_type = 'video';
        } else {
            echo "Invalid file type.";
            exit();
        }

        // Generate unique file name to avoid conflicts
        $media_path = "uploads/" . uniqid() . "." . $media_ext;

        // Try to move the uploaded file
        if (move_uploaded_file($media_tmp, $target_dir . basename($media_path))) {
            // Successfully uploaded
        } else {
            echo "Error uploading file.";
            exit();
        }
    }

    // Insert the post into the database
    $stmt = $conn->prepare("INSERT INTO company_posts (company_id, caption, media_path, media_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $company_id, $caption, $media_path, $media_type);
    if ($stmt->execute()) {
        header("Location: dashboard.php");
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
    <title>Create Post</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">
    <h1>Create Post</h1>
    <form method="POST" enctype="multipart/form-data">
        <textarea name="caption" rows="4" required placeholder="Enter caption..."></textarea><br><br>
        <input type="file" name="media" accept="image/*, video/*"><br><br>
        <button type="submit">Create Post</button>
    </form>
</div>

</body>
</html>
