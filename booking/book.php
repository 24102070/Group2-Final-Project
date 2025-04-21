<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$company_id = $_GET['company_id'] ?? null;
$package_id = $_GET['package_id'] ?? null;
$message = "";

// Book schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];

    // Fetch the schedule details to get the date
    $sql_schedule = "SELECT date FROM company_schedules WHERE id = ?";
    $stmt_schedule = $conn->prepare($sql_schedule);
    $stmt_schedule->bind_param("i", $schedule_id);
    $stmt_schedule->execute();
    $schedule_result = $stmt_schedule->get_result();
    $schedule = $schedule_result->fetch_assoc();
    
    $schedule_date = $schedule['date'];
    $current_date = date('Y-m-d');
    $days_diff = (strtotime($schedule_date) - strtotime($current_date)) / (60 * 60 * 24); // Difference in days

    // Check if the schedule is at least 3 days ahead
    if ($days_diff < 3) {
        $message = "You can only book a schedule 3 days or more prior to the date.";
    } else {
        // Check if already booked
        $check_sql = "SELECT * FROM bookings WHERE schedule_id = ? AND status = 'accept'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "This time slot has already been accepted by another user.";
        } else {
            // Check if user already has an existing booking for the same schedule
            $check_sql = "SELECT * FROM bookings WHERE user_id = ? AND schedule_id = ? AND status NOT IN ('cancelled', 'accept', 'reject')";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ii", $user_id, $schedule_id);
            $stmt->execute();
            $check_result = $stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message = "You have already booked this time slot.";
            } else {
                // Insert into bookings table
                $insert_sql = "INSERT INTO bookings (user_id, company_id, schedule_id, package_id, status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("iiii", $user_id, $company_id, $schedule_id, $package_id);
                if ($stmt->execute()) {
                    $message = "Booking successful!";
                } else {
                    $message = "Failed to book the schedule. Try again.";
                }
            }
        }
    }
}

// Fetch company details
$sql = "SELECT name FROM companies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

// Fetch schedules for the company
$sql_schedules = "SELECT id, date, start_time, end_time FROM company_schedules WHERE company_id = ? ORDER BY date, start_time";
$stmt_schedules = $conn->prepare($sql_schedules);
$stmt_schedules->bind_param("i", $company_id);
$stmt_schedules->execute();
$schedules_result = $stmt_schedules->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Schedule</title>
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

        .message {
            color: #FF5733;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        h3 {
            margin-bottom: 15px;
            font-size: 1.5em;
            color: #333;
        }

        /* Schedule Cards */
        .schedule-card {
            background: linear-gradient(145deg, #ffffff, #f4f6fa);
            border-radius: 12px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .schedule-info {
            flex-grow: 1;
        }

        .schedule-info strong {
            color: #4CAF50;
            font-size: 1.2em;
        }

        .schedule-card p {
            font-size: 1em;
            margin: 5px 0;
            color: #666;
        }

        /* Book Now Button */
        .schedule-card button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 1em;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .schedule-card button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        /* Small Touches */
        .container p {
            text-align: center;
        }

        .schedule-card button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .note {
            margin-top: 20px;
            font-size: 1em;
            color: #f44336;
            text-align: center;
        }
    </style>
    <script>
        function confirmBooking(button) {
            const confirmed = confirm("Are you sure you want to book?");
            if (confirmed) {
                button.form.submit();
            }
        }
    </script>
</head>
<body>

    <div class="container">
        <h1>Book a Schedule with <?php echo htmlspecialchars($company['name']); ?></h1>

        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" id="bookingForm">
            <h3>Available Schedules</h3>
            <p class="note">Note: You can only book a schedule 3 days or more in advance.</p>
            <?php while ($schedule = $schedules_result->fetch_assoc()): ?>
                <div class="schedule-card">
                    <div class="schedule-info">
                        <p><strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($schedule['date'])); ?></p>
                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time'])); ?></p>
                    </div>
                    <?php
                    // Check if the schedule has been accepted by any user
                    $check_sql = "SELECT * FROM bookings WHERE schedule_id = ? AND status = 'accept'";
                    $stmt = $conn->prepare($check_sql);
                    $stmt->bind_param("i", $schedule['id']);
                    $stmt->execute();
                    $check_result = $stmt->get_result();

                    $is_disabled = $check_result->num_rows > 0 || (strtotime($schedule['date']) - strtotime(date('Y-m-d'))) < 3 * 24 * 60 * 60;
                    ?>
                    <?php if ($is_disabled): ?>
                        <button disabled>Book Now</button>
                    <?php else: ?>
                        <button type="button" onclick="confirmBooking(this)">
                            Book Now
                        </button>
                        <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </form>

    </div>
    <a href="../dashboard/dashboard.php" class="back-btn">Back to Dashboard</a>

</body>
</html>
