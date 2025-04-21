<?php
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Process cancellation request for company appointments
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    $sql = "SELECT s.date FROM bookings b JOIN company_schedules s ON b.schedule_id = s.id WHERE b.id = ? AND b.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $schedule_date = strtotime($row['date']);
        $current_date = time();
        $days_left = ($schedule_date - $current_date) / (60 * 60 * 24); // Days left for the appointment

        if ($days_left >= 3) {
            // Cancel the appointment
            $cancel_sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
            $cancel_stmt = $conn->prepare($cancel_sql);
            $cancel_stmt->bind_param("i", $cancel_id);
            if ($cancel_stmt->execute()) {
                $message = "Your appointment has been successfully canceled.";
            } else {
                $message = "There was an error while canceling your appointment. Please try again.";
            }
        } else {
            $message = "You can only cancel an appointment if it's at least 3 days before the scheduled date.";
        }
    } else {
        $message = "Appointment not found.";
    }
}

// Fetch user's booked appointments from the bookings table (companies)
$sql = "
    SELECT b.id, b.schedule_id, b.package_id, b.status, b.created_at, 
           s.date AS schedule_date, s.start_time, s.end_time, c.name AS company_name, p.name AS package_name
    FROM bookings b
    JOIN company_schedules s ON b.schedule_id = s.id
    JOIN companies c ON b.company_id = c.id
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$company_result = $stmt->get_result();

// Fetch user's booked appointments from freelancer_bookings table (freelancers)
$sql = "
    SELECT b.id, b.schedule_id, b.package_id, b.status, b.created_at, 
           s.date AS schedule_date, s.start_time, s.end_time, f.name AS freelancer_name, p.name AS package_name
    FROM freelancer_bookings b
    JOIN freelancer_schedules s ON b.schedule_id = s.id
    JOIN freelancers f ON b.freelancer_id = f.id
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$freelancer_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booked Appointments</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        /* Global Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            font-size: 2em;
            margin-bottom: 30px;
        }

        h3 {
            margin-bottom: 15px;
            font-size: 1.5em;
            color: #333;
        }

        /* Appointments Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status-pending {
            color: orange;
        }

        .status-completed {
            color: green;
        }

        .status-cancelled {
            color: red;
        }

        .message {
            color: #FF5733;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        .cancel-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>My Booked Appointments</h1>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Company Appointments -->
        <h3>Company Appointments</h3>
        <?php if ($company_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Schedule</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Booked On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $company_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td><?php echo date('l, F j, Y', strtotime($row['schedule_date'])) . ' ' . date('g:i A', strtotime($row['start_time'])) . ' - ' . date('g:i A', strtotime($row['end_time'])); ?></td>
                            <td><?php echo $row['package_name'] ? htmlspecialchars($row['package_name']) : 'No package'; ?></td>
                            <td class="<?php echo 'status-' . strtolower($row['status']); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </td>
                            <td><?php echo date('l, F j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="?cancel_id=<?php echo $row['id']; ?>" class="cancel-btn" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="message">You have no booked appointments with companies yet.</p>
        <?php endif; ?>

        <!-- Freelancer Appointments -->
        <h3>Freelancer Appointments</h3>
        <?php if ($freelancer_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Freelancer</th>
                        <th>Schedule</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Booked On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $freelancer_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['freelancer_name']); ?></td>
                            <td><?php echo date('l, F j, Y', strtotime($row['schedule_date'])) . ' ' . date('g:i A', strtotime($row['start_time'])) . ' - ' . date('g:i A', strtotime($row['end_time'])); ?></td>
                            <td><?php echo $row['package_name'] ? htmlspecialchars($row['package_name']) : 'No package'; ?></td>
                            <td class="<?php echo 'status-' . strtolower($row['status']); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </td>
                            <td><?php echo date('l, F j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="?cancel_id=<?php echo $row['id']; ?>" class="cancel-btn" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="message">You have no booked appointments with freelancers yet.</p>
        <?php endif; ?>

    </div>
    <a href="dashboard.php" class="back-btn">Back to Dashboard</a>

</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
