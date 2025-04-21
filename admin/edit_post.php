<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];
$post_id = $_GET['id'];

// Fetch the original post
$sql = "SELECT * FROM company_posts WHERE id = ? AND company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $company_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}

// Only proceed if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = $_POST['caption'];
    $media_type = $_POST['media_type'];
    $media_path = $post['media_path']; // default to original media path

    // Check if a new media file is uploaded
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $fileTmpPath = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid() . '.' . $fileExtension;
        $uploadPath = '../uploads/' . $newFileName;

        // Move uploaded file to upload directory
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Delete the old media file (if exists)
            if (!empty($post['media_path']) && file_exists('../' . $post['media_path'])) {
                unlink('../' . $post['media_path']);
            }
            // Update media path for DB
            $media_path = 'uploads/' . $newFileName;
        }
    }

    // Update post
    $sqlUpdate = "UPDATE company_posts SET caption = ?, media_path = ?, media_type = ? WHERE id = ? AND company_id = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("sssii", $caption, $media_path, $media_type, $post_id, $company_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!-- HTML FORM -->
<h2>Edit Post</h2>
<form method="POST" enctype="multipart/form-data">
    <label for="caption">Caption:</label><br>
    <textarea name="caption" rows="4" required><?php echo htmlspecialchars($post['caption']); ?></textarea><br><br>

    <?php if (!empty($post['media_path']) && $post['media_type'] == 'image'): ?>
        <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" alt="Current Image" style="max-width: 200px;"><br><br>
    <?php elseif (!empty($post['media_path']) && $post['media_type'] == 'video'): ?>
        <video controls style="max-width: 200px;">
            <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
        </video><br><br>
    <?php endif; ?>

    <label for="media">Replace Media (optional):</label><br>
    <input type="file" name="media" accept="image/*, video/*"><br><br>

    <label for="media_type">Media Type:</label>
    <select name="media_type" required>
        <option value="image" <?php if ($post['media_type'] == 'image') echo 'selected'; ?>>Image</option>
        <option value="video" <?php if ($post['media_type'] == 'video') echo 'selected'; ?>>Video</option>
    </select><br><br>

    <button type="submit">Update Post</button>
</form>
