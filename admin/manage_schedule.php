<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];
$message = "";

// Handle ADD, EDIT, and DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle DELETE
    if (isset($_POST['delete'])) {
        $id = $_POST['delete'];
        $stmt = $conn->prepare("DELETE FROM company_schedules WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ii", $id, $company_id);
        $stmt->execute();
    } 
    // Handle EDIT
    elseif (isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $date = $_POST['edit_date'];
        $start_time = $_POST['edit_start_time'];
        $end_time = $_POST['edit_end_time'];

        $stmt = $conn->prepare("UPDATE company_schedules SET date = ?, start_time = ?, end_time = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("sssii", $date, $start_time, $end_time, $id, $company_id);
        $stmt->execute();

        header("Location: manage_schedule.php");
        exit();
    } 
    // Handle ADD
    else {
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Check for overlap
        $check_overlap_sql = "SELECT * FROM company_schedules WHERE company_id = ? AND date = ? 
                               AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))";
        $stmt_check_overlap = $conn->prepare($check_overlap_sql);
        $stmt_check_overlap->bind_param("isssss", $company_id, $date, $start_time, $start_time, $end_time, $end_time);
        $stmt_check_overlap->execute();
        $overlap_result = $stmt_check_overlap->get_result();

        if ($overlap_result->num_rows > 0) {
            // Overlap detected
            $_SESSION['message'] = "The schedule overlaps with an existing one. Please select a different time.";
        } else {
            // No overlap, proceed to insert
            $insert_sql = "INSERT INTO company_schedules (company_id, date, start_time, end_time) 
                           VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("isss", $company_id, $date, $start_time, $end_time);
            $stmt_insert->execute();
            $_SESSION['message'] = "Schedule added successfully!";
        }

        header("Location: manage_schedule.php");
        exit();
    }
}

// Fetch schedules sorted by date and start time (from earliest to latest)
$sql = "SELECT * FROM company_schedules WHERE company_id = ? ORDER BY date ASC, start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$schedules = $result->fetch_all(MYSQLI_ASSOC);

// Display the error or success message if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);  // Clear the message after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedule</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        h2, h3 {
            color: #1a73e8;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input[type="date"],
        input[type="time"],
        button {
            padding: 8px;
            width: 100%;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 15px;
            background-color: #1a73e8;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #125ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #e0e0e0;
            text-align: center;
        }

        th {
            background-color: #e8f0fe;
        }

        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 5px;
            margin: 2px;
        }

        .edit-btn {
            background-color: #fbbc04;
            color: white;
            border: none;
        }

        .delete-btn {
            margin-top: 50px;
            background-color: #ea4335;
            color: white;
            border: none;
        }

        .save-btn {
            background-color: #34a853;
            color: white;
            border: none;
        }

        .message {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Your Availability</h2>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="manage_schedule.php">
        <label for="date">Date:</label>
        <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" required>

        <button type="submit">Add Time Slot</button>
    </form>

    <h3>Your Upcoming Schedules</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Start - End</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($schedules as $schedule): ?>
            <tr>
                <?php if (isset($_GET['edit']) && $_GET['edit'] == $schedule['id']): ?>
                    <form method="POST">
                        <td>
                            <input type="date" name="edit_date" value="<?= htmlspecialchars($schedule['date']) ?>" min="<?= date('Y-m-d') ?>" required>
                        </td>
                        <td>
                            <input type="time" name="edit_start_time" value="<?= htmlspecialchars($schedule['start_time']) ?>" required>
                            to
                            <input type="time" name="edit_end_time" value="<?= htmlspecialchars($schedule['end_time']) ?>" required>
                        </td>
                        <td>
                            <input type="hidden" name="edit_id" value="<?= $schedule['id'] ?>">
                            <button type="submit" class="action-btn save-btn">Save</button>
                            <a href="manage_schedule.php" class="action-btn delete-btn">Cancel</a>
                        </td>
                    </form>
                <?php else: ?>
                    <td><?= date("F j, Y", strtotime($schedule['date'])) ?></td>
                    <td><?= date("g:i A", strtotime($schedule['start_time'])) ?> - <?= date("g:i A", strtotime($schedule['end_time'])) ?></td>
                    <td>
                        <a href="manage_schedule.php?edit=<?= $schedule['id'] ?>" class="action-btn edit-btn">Edit</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= $schedule['id'] ?>">
                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="dashboard.php">Back to Dashboard</a>
</div>


</body>
</html>
