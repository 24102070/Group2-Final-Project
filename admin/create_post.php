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
    <title>Create Wedding Post | LoveStory</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/create_post.css">
</head>
<body>
    <div class="post-creator">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Share Your Story</h1>
            <p>Create a beautiful post for your wedding portfolio</p>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="post-form">
            <div class="caption-box">
                <label for="caption">Your Love Story</label>
                <textarea id="caption" name="caption" placeholder="Share your thoughts, describe this special moment, or tell your love story..."></textarea>
            </div>
            
            <div class="media-upload">
                <label class="upload-label">Add Media</label>
                <div class="upload-area" id="upload-area">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">Click to upload or drag & drop</div>
                    <div class="upload-hint">Supports JPG, PNG, GIF, MP4 (Max 10MB)</div>
                    <input type="file" name="media" id="media-input" accept="image/*, video/*" hidden>
                </div>
                
                <div id="media-preview">
                    <div id="preview-content"></div>
                    <div class="remove-media" id="remove-media">
                        <i class="fas fa-times"></i> Remove Media
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <a href="../dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Publish Post
                </button>
            </div>
        </form>
    </div>

    <script>
        // Media upload preview functionality
        const uploadArea = document.getElementById('upload-area');
        const mediaInput = document.getElementById('media-input');
        const mediaPreview = document.getElementById('media-preview');
        const previewContent = document.getElementById('preview-content');
        const removeMedia = document.getElementById('remove-media');

        uploadArea.addEventListener('click', () => {
            mediaInput.click();
        });

        // Handle drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#e4816c';
            uploadArea.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#f3c9b5';
            uploadArea.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#f3c9b5';
            uploadArea.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
            
            if (e.dataTransfer.files.length) {
                mediaInput.files = e.dataTransfer.files;
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        mediaInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const validVideoTypes = ['video/mp4', 'video/quicktime'];
            
            if (validImageTypes.includes(file.type)) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContent.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    mediaPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else if (validVideoTypes.includes(file.type)) {
                previewContent.innerHTML = `
                    <video controls>
                        <source src="${URL.createObjectURL(file)}" type="${file.type}">
                        Your browser does not support the video tag.
                    </video>
                `;
                mediaPreview.style.display = 'block';
            }
        }

        removeMedia.addEventListener('click', () => {
            mediaInput.value = '';
            mediaPreview.style.display = 'none';
            previewContent.innerHTML = '';
        });
    </script>
</body>
</html>