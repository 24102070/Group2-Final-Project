<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];

// Fetch freelancer data
$sql = "SELECT p.profile_photo, p.cover_photo, p.about, p.contact 
        FROM freelancer_profiles p 
        WHERE p.freelancer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();

// If no profile entry exists, create a default one
if (!$freelancer) {
    $insert_sql = "INSERT INTO freelancer_profiles (freelancer_id) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("i", $freelancer_id);
    $insert_stmt->execute();

    // Refresh data
    $stmt->execute();
    $result = $stmt->get_result();
    $freelancer = $result->fetch_assoc();
}

// Default images
$profile_photo = !empty($freelancer['profile_photo']) ? "../" . $freelancer['profile_photo'] : "../assets/default-profile.png";
$cover_photo = !empty($freelancer['cover_photo']) ? "../" . $freelancer['cover_photo'] : "../assets/default-cover.png";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $about = $_POST['about'];
    $contact = $_POST['contact'];

    // Profile photo
    if (!empty($_FILES["profile_photo"]["name"])) {
        $profile_photo_path = "uploads/" . basename($_FILES["profile_photo"]["name"]);
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], "../" . $profile_photo_path);
    } else {
        $profile_photo_path = $freelancer['profile_photo'];
    }

    // Cover photo
    if (!empty($_FILES["cover_photo"]["name"])) {
        $cover_photo_path = "uploads/" . basename($_FILES["cover_photo"]["name"]);
        move_uploaded_file($_FILES["cover_photo"]["tmp_name"], "../" . $cover_photo_path);
    } else {
        $cover_photo_path = $freelancer['cover_photo'];
    }

    // Update freelancer profile
    $update_sql = "UPDATE freelancer_profiles 
                   SET profile_photo = ?, cover_photo = ?, about = ?, contact = ? 
                   WHERE freelancer_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $profile_photo_path, $cover_photo_path, $about, $contact, $freelancer_id);
    $stmt->execute();

    header("Location: freelancer_dashboard.php?success=updated");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Freelancer Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/update_freelancer_profile.css">

</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h1>Update Freelancer Profile</h1>

        <form method="POST" enctype="multipart/form-data">
            <label>Profile Photo:</label><br>
            <img src="<?php echo $profile_photo; ?>" width="100"><br>
            <input type="file" name="profile_photo"><br>

            <label>Cover Photo:</label><br>
            <img src="<?php echo $cover_photo; ?>" width="300"><br>
            <input type="file" name="cover_photo"><br>

            <label>About Me:</label><br>
            <textarea name="about" required><?php echo $freelancer['about'] ?? ''; ?></textarea><br>

            <label>Contact Information and Address:</label><br>
            <input type="text" name="contact" value="<?php echo $freelancer['contact'] ?? ''; ?>" required><br>

            <button type="submit" class="btn btn-submit"><i class="fas fa-save"></i> Update Profile</button>
        </form>

        <div class="btn-back">
            <a href="freelancer_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

</body>
</html>