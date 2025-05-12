<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    echo "Post ID is required.";
    exit();
}

// Fetch the post
$sql = "SELECT * FROM freelancer_posts WHERE id = ? AND freelancer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Post not found or you don't have permission to edit this post.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = $_POST['caption'];

    // Optional: Upload new media
    if (!empty($_FILES['media']['name'])) {
        $upload_dir = 'uploads/';
        $media_name = basename($_FILES['media']['name']);
        $target_file = $upload_dir . time() . "_" . $media_name;
        $media_type = explode('/', $_FILES['media']['type'])[0];

        if (move_uploaded_file($_FILES['media']['tmp_name'], "../" . $target_file)) {
            // Delete old file
            if (!empty($post['media_path']) && file_exists("../" . $post['media_path'])) {
                unlink("../" . $post['media_path']);
            }

            $sql_update = "UPDATE freelancer_posts SET caption = ?, media_path = ?, media_type = ? WHERE id = ? AND freelancer_id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("sssii", $caption, $target_file, $media_type, $post_id, $freelancer_id);
        } else {
            echo "Failed to upload file.";
            exit();
        }
    } else {
        // Update only the caption
        $sql_update = "UPDATE freelancer_posts SET caption = ? WHERE id = ? AND freelancer_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sii", $caption, $post_id, $freelancer_id);
    }

    if ($stmt->execute()) {
        header("Location: freelancer_dashboard.php");
        exit();
    } else {
        echo "Error updating post.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/edit_freelancer_post.css">
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h1>Edit Post</h1>
        
        <form method="POST" enctype="multipart/form-data">
            <textarea name="caption" required placeholder="Share your thoughts..."><?php echo htmlspecialchars($post['caption']); ?></textarea>
            
            <div class="file-input-container">
                <label class="file-input-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Click to change media (images or videos)</span>
                    <input type="file" name="media" accept="image/*, video/*">
                </label>
            </div>
            
            <?php if ($post['media_path']): ?>
                <p style="text-align: center; margin-top: 10px;">
                    <small>Current media: <?php echo basename($post['media_path']); ?></small>
                </p>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-submit"><i class="fas fa-save"></i> Update Post</button>
        </form>
        
        <a href="freelancer_dashboard.php" style="display: block; text-align: center; margin-top: 20px; color: #E67B7B; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

</body>
</html>