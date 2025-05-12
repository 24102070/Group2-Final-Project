<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Get the company ID from session
$company_id = $_SESSION['user_id'];

// Fetch all packages from the database for the current company
$sql = "SELECT * FROM packages WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result_packages = $stmt->get_result();

// Initialize the variable to avoid undefined warnings
$no_packages_message = null;


if ($result_packages->num_rows == 0) {
    $no_packages_message = "No packages available. Please add a package.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Packages</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/view_packages.css">

</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <div class="glass-header">
            <h1><img src="../assets/IMAGES/LOGO.png" alt="Logo" class="logo"> My Packages</h1>
        </div>

        <?php if ($no_packages_message): ?>
            <p class="no-packages"><?php echo $no_packages_message; ?></p>
            <a href="add_package.php" class="btn-add"><i class="fas fa-plus"></i> Add New Package</a>
        <?php else: ?>
            <?php while ($package = $result_packages->fetch_assoc()): ?>
                <div class="package-card">
                    <div class="package-title"><?php echo htmlspecialchars($package['name']); ?></div>
                    <p class="package-details"><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                    <p class="package-details"><strong>Inclusions:</strong> <?php echo nl2br(htmlspecialchars($package['inclusions'])); ?></p>
                    <p class="package-price">₱ <?php echo number_format($package['price'], 2); ?></p>

                    <?php if (!empty($package['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($package['image']); ?>" alt="Package Image" class="package-image">
                    <?php endif; ?>

                    <div class="btn-group">
                        <a href="edit_package.php?id=<?php echo $package['id']; ?>" class="btn btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_package.php?id=<?php echo $package['id']; ?>" class="btn btn-delete"
                           onclick="return confirm('Are you sure you want to delete this package?')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <div class="btn-back">
            <a href="dashboard.php"><button><i class="fas fa-arrow-left"></i> Back to Dashboard</button></a>
        </div>
    </div>

    <script>
        // Add event listeners for each package title
        document.querySelectorAll('.package-title').forEach(function(title) {
            title.addEventListener('click', function() {
                title.classList.toggle('expanded');
            });
        });
    </script>

</body>
</html>