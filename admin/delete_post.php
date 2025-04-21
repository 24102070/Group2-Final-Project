<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];
$post_id = $_GET['id'];

// Delete the post
$sql_delete = "DELETE FROM company_posts WHERE company_id = ? AND id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("ii", $company_id, $post_id);
$stmt_delete->execute();

header("Location: dashboard.php");
exit();
?>
