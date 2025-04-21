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
</head>
<body>

<h2>Edit Post</h2>

<form method="POST" enctype="multipart/form-data">
    <label>Caption:</label><br>
    <textarea name="caption" rows="4" cols="50"><?php echo htmlspecialchars($post['caption']); ?></textarea><br><br>

    <label>Change Media (optional):</label><br>
    <input type="file" name="media"><br><br>

    <button type="submit">Update Post</button>
</form>

<br>
<a href="freelancer_dashboard.php">Back to Dashboard</a>

</body>
</html>
