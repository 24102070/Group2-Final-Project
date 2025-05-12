<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];

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
        if (!move_uploaded_file($media_tmp, $target_dir . basename($media_path))) {
            echo "Error uploading file.";
            exit();
        }
    }

    // Insert the post into the database
    $stmt = $conn->prepare("INSERT INTO freelancer_posts (freelancer_id, caption, media_path, media_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $freelancer_id, $caption, $media_path, $media_type);
    if ($stmt->execute()) {
        header("Location: freelancer_dashboard.php");
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
    <title>Create Post</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/create_freelancer_post.css">
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h1>Create Post</h1>
        
        <form method="POST" enctype="multipart/form-data">
            <textarea name="caption" required placeholder="Share your thoughts..."></textarea>
            
            <div class="file-input-container">
                <label class="file-input-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Click to upload media (images or videos)</span>
                    <input type="file" name="media" accept="image/*, video/*">
                </label>
            </div>
            
            <button type="submit" class="btn btn-submit"><i class="fas fa-plus-circle"></i> Create Post</button>
        </form>
    </div>

</body>
</html>