<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

if (isset($_GET['id'])) {
    $package_id = $_GET['id'];

    // Fetch the package details for the given package ID
    $sql = "SELECT * FROM packages WHERE id = ? AND company_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $package_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();

    if (!$package) {
        header("Location: view_packages.php");
        exit();
    }

    // Update the package if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['package_name'];
        $details = $_POST['package_details'];
        $inclusions = $_POST['package_inclusions'];
        $price = $_POST['package_price'];
        $image_path = $package['image']; // Default to existing image

        // Handle file upload if a new image is provided
        if (!empty($_FILES["package_image"]["name"])) {
            // Define the file path and move the uploaded file
            $image_path = "uploads/" . basename($_FILES["package_image"]["name"]);
            $upload_dir = "../" . $image_path;

            // Create the uploads directory if it doesn't exist
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }

            // Move the uploaded image to the specified directory
            if (move_uploaded_file($_FILES["package_image"]["tmp_name"], $upload_dir)) {
                // Delete the old image if it exists
                if ($package['image'] && file_exists('../' . $package['image'])) {
                    unlink('../' . $package['image']);
                }
            } else {
                $error_message = "Failed to upload image.";
            }
        }

        // Update the package details in the database
        $update_sql = "UPDATE packages SET name = ?, details = ?, inclusions = ?, price = ?, image = ? WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssssi", $name, $details, $inclusions, $price, $image_path, $package_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            header("Location: view_packages.php");
            exit();
        } else {
            echo "Error updating package: " . $stmt->error;
        }
    }
} else {
    header("Location: view_packages.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Package</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/edit_package.css">
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h2>Edit Package</h2>
        <form method="POST" action="edit_package.php?id=<?php echo $package['id']; ?>" enctype="multipart/form-data">
            <label for="package_name">Package Name:</label>
            <input type="text" id="package_name" name="package_name" value="<?php echo htmlspecialchars($package['name']); ?>" required>

            <label for="package_details">Package Details:</label>
            <textarea id="package_details" name="package_details" required><?php echo htmlspecialchars($package['details']); ?></textarea>

            <label for="package_inclusions">Package Inclusions:</label>
            <textarea id="package_inclusions" name="package_inclusions" required><?php echo htmlspecialchars($package['inclusions']); ?></textarea>

            <label for="package_price">Package Price (PHP):</label>
            <input type="number" id="package_price" name="package_price" value="<?php echo $package['price']; ?>" required>

            <label for="package_image">Package Image:</label>
            <input type="file" id="package_image" name="package_image" accept="image/*">
            
            <?php if ($package['image']): ?>
                <div class="current-image">
                    <img src="../<?php echo $package['image']; ?>" alt="Current Package Image">
                </div>
            <?php endif; ?>

            <button type="submit">Update Package</button>
            <a href="view_packages.php" class="button">Back to Packages</a>
        </form>
    </div>

</body>
</html>