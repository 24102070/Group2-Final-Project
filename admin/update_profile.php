<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];

// Fetch company data
$sql = "SELECT p.profile_photo, p.cover_photo, p.about, p.contact, c.minimum_fee 
        FROM company_profiles p 
        JOIN companies c ON p.company_id = c.id 
        WHERE p.company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

// If no profile entry exists, create a default one
if (!$company) {
    $insert_sql = "INSERT INTO company_profiles (company_id) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("i", $company_id);
    $insert_stmt->execute();

    // Refresh data
    $stmt->execute();
    $result = $stmt->get_result();
    $company = $result->fetch_assoc();
}

// Default images
$profile_photo = !empty($company['profile_photo']) ? "../" . $company['profile_photo'] : "../assets/default-profile.png";
$cover_photo = !empty($company['cover_photo']) ? "../" . $company['cover_photo'] : "../assets/default-cover.png";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $about = $_POST['about'];
    $contact = $_POST['contact'];
    $minimum_fee = $_POST['minimum_fee'];  // Get the minimum fee from the form

    // Handle profile photo upload
    if (!empty($_FILES["profile_photo"]["name"])) {
        $profile_photo_path = "uploads/" . basename($_FILES["profile_photo"]["name"]);
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], "../" . $profile_photo_path);
    } else {
        $profile_photo_path = $company['profile_photo'];
    }

    // Handle cover photo upload
    if (!empty($_FILES["cover_photo"]["name"])) {
        $cover_photo_path = "uploads/" . basename($_FILES["cover_photo"]["name"]);
        move_uploaded_file($_FILES["cover_photo"]["tmp_name"], "../" . $cover_photo_path);
    } else {
        $cover_photo_path = $company['cover_photo'];
    }

    // Update company profile
    $update_sql = "UPDATE company_profiles 
                   SET profile_photo = ?, cover_photo = ?, about = ?, contact = ? 
                   WHERE company_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $profile_photo_path, $cover_photo_path, $about, $contact, $company_id);
    $stmt->execute();

    // Update the minimum fee in companies table
    $update_fee_sql = "UPDATE companies SET minimum_fee = ? WHERE id = ?";
    $stmt_fee = $conn->prepare($update_fee_sql);
    $stmt_fee->bind_param("di", $minimum_fee, $company_id);
    $stmt_fee->execute();

    header("Location: dashboard.php?success=updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Company Profile</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <h1>Update Company Profile</h1>

    <form method="POST" enctype="multipart/form-data">
        <label>Profile Photo:</label><br>
        <img src="<?php echo $profile_photo; ?>" width="100"><br>
        <input type="file" name="profile_photo"><br>

        <label>Cover Photo:</label><br>
        <img src="<?php echo $cover_photo; ?>" width="300"><br>
        <input type="file" name="cover_photo"><br>

        <label>About Us:</label><br>
        <textarea name="about" required><?php echo $company['about'] ?? ''; ?></textarea><br>

        <label>Contact:</label><br>
        <input type="text" name="contact" value="<?php echo $company['contact'] ?? ''; ?>" required><br>

        <label>Minimum Fee:</label><br>
        <input type="number" step="0.01" name="minimum_fee" value="<?php echo $company['minimum_fee'] ?? ''; ?>" required><br>

        <button type="submit">Update Profile</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
