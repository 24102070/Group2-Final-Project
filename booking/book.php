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
                     echo "<script>window.location.href = '../dashboard/booked.php';</script>";
                        exit();
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
$sql_schedules = "
    SELECT id, date, start_time, end_time 
    FROM company_schedules 
    WHERE company_id = ? 
      AND DATE(date) >= CURDATE() + INTERVAL 3 DAY 
    ORDER BY date ASC, start_time ASC
";

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
    <link rel="stylesheet" href="../assets/booked.css">
    <style>
        /* book.css */

/* Global Styling */
* {
    color: #E67B7B !important;
}
body {

    font-family: 'Poppins', sans-serif;
    font-weight: 300;
    margin: 0;
    padding: 0;
    color: #5A4A42;
    line-height: 1.6;
    background: url('https://images.unsplash.com/photo-1589243853654-393fcf7c870b?q=80&w=2940&auto=format&fit=crop') no-repeat center center fixed;
    background-size: 200% 200%;
    animation: gradient 55s ease infinite;
    min-height: 100vh;
    overflow-y: auto;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.container {
    max-width: 900px;
    margin: 60px auto;
    background: rgba(255, 255, 255, 0.85);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(255, 173, 153, 0.3);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

h1, h3 {
    font-weight: 600;
    color: #3B2C27;
    margin-bottom: 20px;
}

.note {
    font-style: italic;
    color: #9c6e60;
    margin-bottom: 25px;
    text-align: center;
}

.message {
    background-color: #ffe6e1;
    color: #8a2f1c;
    padding: 10px 15px;
    border-left: 5px solid #ff7a57;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Schedule Card Styling */
.schedule-card {
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.schedule-info p {
    margin: 5px 0;
    font-weight: 500;
}

button {
    padding: 10px 20px;
    background-color: #ffbfa3;
    border: none;
    border-radius: 10px;
    color: white !important;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #ff9c76;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
 .back-btn {
            font-family: 'Poppins', 'Serif';
            display: block;
            width: 200px;
            margin: 30px auto 0;
            text-align: center;
            padding: 12px 0;
            background-color: #E67B7B;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 300;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 183, 161, 0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background-color: rgba(255, 163, 138, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 183, 161, 0.4);
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
 <a href="../dashboard/dashboard.php" class="back-btn" style = "color:white !important;">Back to Dashboard</a>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        
            <h3>Available Schedules</h3>
            <p class="note">Note: You can only book a schedule 3 days or more in advance.</p>
            <?php while ($schedule = $schedules_result->fetch_assoc()): ?>
    <form method="POST" onsubmit="return confirm('Are you sure you want to book?');" class="schedule-card">
        <div class="schedule-info">
            <p><strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($schedule['date'])); ?></p>
            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time'])); ?></p>
        </div>
        <?php
        $check_sql = "SELECT * FROM bookings WHERE schedule_id = ? AND status = 'accept'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $schedule['id']);
        $stmt->execute();
        $check_result = $stmt->get_result();

        $is_disabled = $check_result->num_rows > 0 || (strtotime($schedule['date']) - strtotime(date('Y-m-d'))) < 3 * 24 * 60 * 60;
        ?>
        <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
        <?php if ($is_disabled): ?>
            <button type="submit" disabled>Book Now</button>
        <?php else: ?>
            <button type="submit">Book Now</button>
        <?php endif; ?>
    </form>
<?php endwhile; ?>

          <p class="note">Can't find a suitable schedule? Reach out to us via chat for assistance.</p>
          <a href="../messaging/messaging.php" class="back-btn" style = "color:white !important;">Message Now!</a>
    </div>


</body>
</html>
