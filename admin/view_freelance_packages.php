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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Freelance Packages</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        /* Similar CSS styling as your original one, with minor adjustments */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .package-card {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-bottom: 30px;
            padding: 20px;
            transition: transform 0.2s ease-in-out;
        }

        .package-card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .package-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .package-details {
            font-size: 16px;
            color: #666;
            margin: 10px 0;
        }

        .package-price {
            font-size: 18px;
            color: #5cb85c;
            margin-bottom: 10px;
        }

        .package-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .btn-view {
            display: inline-block;
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }

        .no-packages {
            text-align: center;
            font-size: 18px;
            color: #999;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>My Freelance Packages</h1>

    <?php if (isset($no_packages_message)): ?>
        <p class="no-packages"><?php echo $no_packages_message; ?></p>
        <a href="add_freelance_package.php" class="btn-view">Add New Package</a>
    <?php else: ?>
        <!-- Loop through each freelance package and display its details -->
        <?php while ($package = $result_packages->fetch_assoc()): ?>
            <div class="package-card">
                <h2><?php echo htmlspecialchars($package['name']); ?></h2>
                <p><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                <p><strong>Inclusions:</strong> <?php echo nl2br(htmlspecialchars($package['inclusions'])); ?></p>
                <p class="package-price">PHP <?php echo number_format($package['price'], 2); ?></p>

                <?php if (!empty($package['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($package['image']); ?>" alt="Package Image" class="package-image">
                <?php endif; ?>

                <div class="buttons">
                    <!-- Edit button -->
                    <a href="edit_freelance_package.php?id=<?php echo $package['id']; ?>" class="btn-view">Edit</a>

                    <!-- Delete button -->
                    <a href="delete_freelance_package.php?id=<?php echo $package['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this package?')">Delete</a>
                </div>

                
            </div>
            
        <?php endwhile; ?>
    <?php endif; ?>

    <br><br>
            <a href="freelancer_dashboard.php"><button>Back to Dashboard</button></a>
</div>

</body>
</html>
