<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];

// Handle actions: accept, reject, delete
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['booking_id'];

    if ($action === 'accept' || $action === 'reject') {
        $query = "UPDATE bookings SET status = ? WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $action, $booking_id, $company_id);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $query = "DELETE FROM bookings WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $booking_id, $company_id);
        $stmt->execute();
    }

    header('Location: manage_bookings.php');
    exit();
}

// Update Ongoing/Done via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_status'], $_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $event_status = $_POST['event_status']; // 'Ongoing' or 'Done'

    // Update the booking status to Ongoing or Done only if it's accepted
    $check = $conn->prepare("SELECT status FROM bookings WHERE id = ? AND company_id = ?");
    $check->bind_param('ii', $booking_id, $company_id);
    $check->execute();
    $check_result = $check->get_result()->fetch_assoc();
    $check->close();

    if ($check_result && $check_result['status'] === 'accept') {
        $query = "UPDATE bookings SET status = ? WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $event_status, $booking_id, $company_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: manage_bookings.php");
    exit();
}

// Fetch bookings
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
    <title>Manage Bookings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/manage_booking.css">
    <style>
        .btn-accept, .btn-reject, .btn-delete {
            padding: 6px 10px;
            border-radius: 4px;
            margin: 2px;
            color: white;
            text-decoration: none;
        }
        .btn-accept { background-color: #2ecc71; }
        .btn-reject { background-color: #e67e22; }
        .btn-delete { background-color: #e74c3c; }
        .btn-accept:hover { background-color: #27ae60; }
        .btn-reject:hover { background-color: #d35400; }
        .btn-delete:hover { background-color: #c0392b; }
        select.status-dropdown {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f4f4f4;
        }
        .action-buttons {
    display: flex;
    flex-direction: column;
}

.action-buttons a {
    margin: 2px 0;
}

    </style>
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
                    <th>Event Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $schedule_date = new DateTime($row['schedule_date']);
                    $start_time = new DateTime($row['start_time']);
                    $end_time = new DateTime($row['end_time']);
                    $formatted_schedule = $schedule_date->format('F j, Y');
                    $formatted_start_time = $start_time->format('g:i A');
                    $formatted_end_time = $end_time->format('g:i A');

                    $now = new DateTime();
                    $schedule_end = DateTime::createFromFormat('Y-m-d H:i:s', $row['schedule_date'] . ' ' . $row['end_time']);
                    $event_status = ($now > $schedule_end) ? "Done" : "Ongoing";
                    ?>
                    <tr>
                        <td data-label="User Name"><?= htmlspecialchars($row['user_name']) ?></td>
                        <td data-label="User Email"><?= htmlspecialchars($row['user_email']) ?></td>
                        <td data-label="User Contact #"><?= htmlspecialchars($row['user_contact_number']) ?></td>
                        <td data-label="Package"><?= htmlspecialchars($row['package_name'] ?? 'No Package') ?></td>
                        <td data-label="Schedule"><?= $formatted_schedule . ' ' . $formatted_start_time . ' - ' . $formatted_end_time ?></td>
                        <td data-label="Status"><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                        <td data-label="Event Status">
                            <?php if ($row['status'] === 'accept'): ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <select name="event_status" onchange="this.form.submit()" class="status-dropdown">
                                        <option value="Ongoing" <?= $event_status == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                        <option value="Done" <?= $event_status == 'Done' ? 'selected' : '' ?>>Done</option>
                                    </select>
                                </form>
                            <?php else: ?>
                                <span style="color: gray;">Not Accepted</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <?php if ($row['status'] === 'pending'): ?>
                                <div class="action-buttons">
                                <a href="?action=accept&booking_id=<?= $row['id'] ?>" class="btn-accept"><i class="fas fa-check-circle"></i> Accept</a>
                                <a href="?action=reject&booking_id=<?= $row['id'] ?>" class="btn-reject"><i class="fas fa-times-circle"></i> Reject</a>
                                </div>
                            <?php else: ?>
                              
                            <?php endif; ?>
                            <a href="?action=delete&booking_id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this booking?');"><i class="fas fa-trash-alt"></i> Delete</a>
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
