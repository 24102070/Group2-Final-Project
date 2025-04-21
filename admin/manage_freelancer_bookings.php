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
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #3498db;
            color: white;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .btn-accept {
            background-color: #2ecc71;
        }

        .btn-reject {
            background-color: #e74c3c;
        }

        .btn-accept:hover {
            background-color: #27ae60;
        }

        .btn-reject:hover {
            background-color: #c0392b;
        }

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
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['user_email']) ?></td>
                        <td><?= htmlspecialchars($row['user_contact_number']) ?></td>
                        <td><?= htmlspecialchars($row['package_name'] ?? 'No Package') ?></td>
                        <td><?= $date->format('F j, Y') . ' ' . $start->format('g:i A') . ' - ' . $end->format('g:i A') ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="?action=accept&booking_id=<?= $row['id'] ?>" class="btn btn-accept">Accept</a>
                                <a href="?action=reject&booking_id=<?= $row['id'] ?>" class="btn btn-reject">Reject</a>
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
