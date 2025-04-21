<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $type = $_POST['type'];

    if (isset($_POST['approve'])) {
        $approvalStatus = 'Approved';
    } elseif (isset($_POST['reject'])) {
        $approvalStatus = 'Rejected';
    }

    if ($type == "company") {
        $sql = "UPDATE companies SET approval = ? WHERE id = ?";
    } elseif ($type == "freelancer") {
        $sql = "UPDATE freelancers SET approval = ? WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $approvalStatus, $id);
    if ($stmt->execute()) {
        header("Location: adminDash.php?success=" . $approvalStatus);
        exit();
    } else {
        echo "Error updating record.";
    }
}
?>
