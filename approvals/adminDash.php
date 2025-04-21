<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle file download request
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']); // Sanitize input
    $filepath = "../uploads/" . $filename;

    if (file_exists($filepath)) {
        // Set headers to force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush();
        readfile($filepath);
        exit();
    } else {
        echo "File not found.";
        exit();
    }
}

// Fetch all pending approvals
$sql = "SELECT 'company' AS type, id, name, email, description AS details, cert_file AS file FROM companies WHERE approval = 'Pending'
        UNION ALL
        SELECT 'freelancer' AS type, id, name, email, profession AS details, resume_file AS file FROM freelancers WHERE approval = 'Pending'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approval Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <h1>Pending Approvals</h1>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="approval-box">
            <h2><?php echo ucfirst($row['type']); ?>: <?php echo $row['name']; ?></h2>
            <p>Email: <?php echo $row['email']; ?></p>
            <p><strong><?php echo ($row['type'] == 'company') ? 'Business description' : 'Profession'; ?>:</strong> <?php echo $row['details']; ?></p>
            <p><a href="adminDash.php?download=<?php echo urlencode($row['file']); ?>">Download Document</a></p>

            <form action="approve.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="type" value="<?php echo $row['type']; ?>">
                <button type="submit" name="approve">Approve</button>
                <button type="submit" name="reject" style="background-color:red;">Reject</button>
            </form>
        </div>
    <?php endwhile; ?>

    <a href="../auth/login.php">Logout</a>
</body>
</html>
