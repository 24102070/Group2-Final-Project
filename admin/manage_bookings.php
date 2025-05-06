<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];

// Handle accept or reject booking status change
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['booking_id'];

    if ($action == 'accept' || $action == 'reject') {
        // Update the booking status
        $query = "UPDATE bookings SET status = ? WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $action, $booking_id, $company_id);
        $stmt->execute();

        // Redirect back to the manage bookings page
        header('Location: manage_bookings.php');
        exit();
    }
}

// Fetch bookings for the logged-in company
$sql = "
    SELECT b.id, b.user_id, b.company_id, b.schedule_id, b.status, b.created_at, b.package_id,
           u.name AS user_name, u.email AS user_email, u.contact_number AS user_contact_number,
           s.date AS schedule_date, s.start_time, s.end_time,
           p.name AS package_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN company_schedules s ON b.schedule_id = s.id
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.company_id = ? 
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
   
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/manage_booking.css">
   
</head>
<body>
    <div class="container">
        <h1>MANAGE BOOKINGS</h1>
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Contact Number</th>
                    <th>Package</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Format the schedule date and time
                    $schedule_date = new DateTime($row['schedule_date']);
                    $start_time = new DateTime($row['start_time']);
                    $end_time = new DateTime($row['end_time']);
                    
                    $formatted_schedule = $schedule_date->format('F j, Y');
                    $formatted_start_time = $start_time->format('g:i A');
                    $formatted_end_time = $end_time->format('g:i A');
                    ?>
                    <tr>
                        <td data-label="User Name"><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td data-label="User Email"><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td data-label="User Contact #"><?php echo htmlspecialchars($row['user_contact_number']); ?></td>
                        <td data-label="Package Name"><?php echo htmlspecialchars($row['package_name'] ? $row['package_name'] : 'No Package'); ?></td>
                        <td data-label="Start-End TIme"><?php echo $formatted_schedule . ' ' . $formatted_start_time . ' - ' . $formatted_end_time; ?></td>
                        <td data-label="Status"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?action=accept&booking_id=<?php echo $row['id']; ?>" class="btn btn-accept">  <i class="fas fa-check-circle"></i>Accept</a> 
                                <a href="?action=reject&booking_id=<?php echo $row['id']; ?>" class="btn btn-reject"> <i class="fas fa-times-circle"></i>Reject</a>
                            <?php else: ?>
                                No Action
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <a href="dashboard.php"><button>Back to Dashboard</button></a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
