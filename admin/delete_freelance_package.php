<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

if (isset($_GET['id'])) {
    $package_id = $_GET['id'];

    // Delete the freelance package
    $sql = "DELETE FROM packages_freelancers WHERE id = ? AND freelancer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $package_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        header("Location: view_freelance_packages.php");
        exit();
    } else {
        echo "Error deleting package: " . $stmt->error;
    }
} else {
    header("Location: view_freelance_packages.php");
    exit();
}
