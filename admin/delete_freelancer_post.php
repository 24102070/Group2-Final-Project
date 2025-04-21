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
$sql = "SELECT media_path FROM freelancer_posts WHERE id = ? AND freelancer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Post not found or access denied.";
    exit();
}

// Delete media file
if (!empty($post['media_path']) && file_exists("../" . $post['media_path'])) {
    unlink("../" . $post['media_path']);
}

// Delete post
$sql_delete = "DELETE FROM freelancer_posts WHERE id = ? AND freelancer_id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("ii", $post_id, $freelancer_id);

if ($stmt->execute()) {
    header("Location: freelancer_dashboard.php");
    exit();
} else {
    echo "Error deleting post.";
}
?>
