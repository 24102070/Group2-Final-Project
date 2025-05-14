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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Company Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/update_profile.css">

</head>
<body>

    <div class="container">
        <h1><i class="fas fa-building"></i> Update Company Profile</h1>

        <form method="POST" enctype="multipart/form-data">
            <div class="image-preview-container">
                <div class="image-preview-wrapper">
                    <label>Profile Photo</label>
                    <img src="<?php echo $profile_photo; ?>" class="image-preview">
                    <span class="image-preview-label">Current Profile</span>
                    <input type="file" name="profile_photo" accept="image/*">
                </div>
                
                <div class="image-preview-wrapper">
                    <label>Cover Photo</label>
                    <img src="<?php echo $cover_photo; ?>" class="image-preview">
                    <span class="image-preview-label">Current Cover</span>
                    <input type="file" name="cover_photo" accept="image/*">
                </div>
            </div>

            <label>About Us</label>
            <textarea name="about" placeholder="Tell clients about your company..." required><?php echo $company['about'] ?? ''; ?></textarea>

            <label>Contact Information and Address</label>
            <input type="text" name="contact" placeholder="Email, phone, or other contact details" value="<?php echo $company['contact'] ?? ''; ?>" required>

            <label>Minimum Fee ($)</label>
            <input type="number" step="0.01" name="minimum_fee" placeholder="Minimum service fee" value="<?php echo $company['minimum_fee'] ?? ''; ?>" required>

            <button type="submit">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>

        <div class="back-link">
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

</body>
</html>