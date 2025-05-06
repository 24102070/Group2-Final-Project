<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];

// Handle accept or reject booking
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['booking_id'];

    if ($action === 'accept' || $action === 'reject') {
        $query = "UPDATE freelancer_bookings SET status = ? WHERE id = ? AND freelancer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $action, $booking_id, $freelancer_id);
        $stmt->execute();

        header("Location: manage_freelancer_bookings.php");
        exit();
    }
}

// Fetch bookings related to this freelancer
$sql = "
    SELECT fb.id, fb.user_id, fb.freelancer_id, fb.schedule_id, fb.status, fb.created_at, fb.package_id,
           u.name AS user_name, u.email AS user_email, u.contact_number AS user_contact_number,
           fs.date AS schedule_date, fs.start_time, fs.end_time,
           pf.name AS package_name
    FROM freelancer_bookings fb
    JOIN users u ON fb.user_id = u.id
    JOIN freelancer_schedules fs ON fb.schedule_id = fs.id
    LEFT JOIN packages_freelancers pf ON fb.package_id = pf.id
    WHERE fb.freelancer_id = ?
    ORDER BY fb.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Freelancer Bookings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/manage_booking.css">
    
</head>
<body>
    <div class="container">
        <h1>Freelancer Bookings</h1>
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Package</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $date = new DateTime($row['schedule_date']);
                    $start = new DateTime($row['start_time']);
                    $end = new DateTime($row['end_time']);
                    ?>
                    <tr>
                        <td data-label="User Name"><?= htmlspecialchars($row['user_name']) ?></td>
                        <td data-label="User Email"><?= htmlspecialchars($row['user_email']) ?></td>
                        <td data-label="User Contact #"><?= htmlspecialchars($row['user_contact_number']) ?></td>
                        <td data-label="Package Name"><?= htmlspecialchars($row['package_name'] ?? 'No Package') ?></td>
                        <td data-label="Schedule"><?= $date->format('F j, Y') . ' ' . $start->format('g:i A') . ' - ' . $end->format('g:i A') ?></td>
                        <td data-label="Status"><?= ucfirst($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="?action=accept&booking_id=<?= $row['id'] ?>" class="btn btn-accept"> <i class="fas fa-check-circle"></i>Accept</a>
                                <a href="?action=reject&booking_id=<?= $row['id'] ?>" class="btn btn-reject"> <i class="fas fa-times-circle"></i>Reject</a>
                            <?php else: ?>
                                No Action
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <a href="freelancer_dashboard.php"><button>Back to Dashboard</button></a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
