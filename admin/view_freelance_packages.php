<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Get the freelancer ID from session
$freelancer_id = $_SESSION['user_id'];

// Fetch all freelance packages from the database for the current freelancer
$sql = "SELECT * FROM packages_freelancers WHERE freelancer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result_packages = $stmt->get_result();

// Check if there are any packages
if ($result_packages->num_rows == 0) {
    $no_packages_message = "No packages available. Please add a package.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Freelance Packages</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/view_freelance_packages.css">

</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h1>My Freelance Packages</h1>

        <div class="packages-container">
            <?php if (isset($no_packages_message)): ?>
                <div class="no-packages">
                    <i class="far fa-calendar-times"></i>
                    <?php echo $no_packages_message; ?>
                    <div style="margin-top: 20px;">
                        <a href="add_freelance_package.php" class="btn-view">Add New Package</a>
                    </div>
                </div>
            <?php else: ?>
                <?php while ($package = $result_packages->fetch_assoc()): ?>
                    <div class="package-card">
                        <h2 class="package-title"><?php echo htmlspecialchars($package['name']); ?></h2>
                        <p class="package-details"><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                        <p class="package-inclusions"><strong>Inclusions:</strong> <?php echo nl2br(htmlspecialchars($package['inclusions'])); ?></p>
                        <div class="package-price">
                            <i class="fas fa-tag"></i>
                            ₱<?php echo number_format($package['price'], 2); ?>
                        </div>

                        <?php if (!empty($package['image'])): ?>
                            <div class="image-container">
                                <img src="../<?php echo htmlspecialchars($package['image']); ?>" alt="Package Image" class="package-image">
                            </div>
                        <?php endif; ?>

                        <div class="package-actions">
                            <a href="edit_freelance_package.php?id=<?php echo $package['id']; ?>" class="btn-view">Edit</a>
                            <a href="delete_freelance_package.php?id=<?php echo $package['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this package?')">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <a href="freelancer_dashboard.php" class="back-btn">
            Back to Dashboard
        </a>
    </div>

</body>
</html>