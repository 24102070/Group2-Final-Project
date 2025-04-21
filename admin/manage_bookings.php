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
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #3498db;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        /* Button Styles */
        .btn {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .btn-accept {
            background-color: #2ecc71;
            color: white;
            margin-right: 10px;
        }

        .btn-accept:hover {
            background-color: #27ae60;
        }

        .btn-reject {
            background-color: #e74c3c;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c0392b;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .container {
                width: 90%;
            }

            table th, table td {
                font-size: 14px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Bookings</h1>
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
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_contact_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['package_name'] ? $row['package_name'] : 'No Package'); ?></td>
                        <td><?php echo $formatted_schedule . ' ' . $formatted_start_time . ' - ' . $formatted_end_time; ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?action=accept&booking_id=<?php echo $row['id']; ?>" class="btn btn-accept">Accept</a> 
                                <a href="?action=reject&booking_id=<?php echo $row['id']; ?>" class="btn btn-reject">Reject</a>
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
