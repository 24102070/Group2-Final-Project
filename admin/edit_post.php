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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/edit_post.css">
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h2>Edit Post</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="caption">Caption:</label>
            <textarea name="caption" required><?php echo htmlspecialchars($post['caption']); ?></textarea>

            <?php if (!empty($post['media_path'])): ?>
                <div class="current-media">
                    <?php if ($post['media_type'] == 'image'): ?>
                        <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" alt="Current Image">
                    <?php elseif ($post['media_type'] == 'video'): ?>
                        <video controls>
                            <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <label for="media">Replace Media (optional):</label>
            <input type="file" name="media" accept="image/*, video/*">

            <label for="media_type">Media Type:</label>
            <select name="media_type" required>
                <option value="image" <?php if ($post['media_type'] == 'image') echo 'selected'; ?>>Image</option>
                <option value="video" <?php if ($post['media_type'] == 'video') echo 'selected'; ?>>Video</option>
            </select>

            <button type="submit">Update Post</button>
        </form>
    </div>

</body>
</html>
