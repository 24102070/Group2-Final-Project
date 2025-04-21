<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];
$message = "";

// Handle ADD, EDIT, and DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DELETE
    if (isset($_POST['delete'])) {
        $id = $_POST['delete'];
        $stmt = $conn->prepare("DELETE FROM freelancer_schedules WHERE id = ? AND freelancer_id = ?");
        $stmt->bind_param("ii", $id, $freelancer_id);
        $stmt->execute();
    }

    // EDIT
    elseif (isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $date = $_POST['edit_date'];
        $start_time = $_POST['edit_start_time'];
        $end_time = $_POST['edit_end_time'];

        // Prevent past dates
        if (strtotime($date) < strtotime(date("Y-m-d"))) {
            $_SESSION['message'] = "Cannot update schedule to a past date.";
        } else {
            $stmt = $conn->prepare("UPDATE freelancer_schedules SET date = ?, start_time = ?, end_time = ? WHERE id = ? AND freelancer_id = ?");
            $stmt->bind_param("sssii", $date, $start_time, $end_time, $id, $freelancer_id);
            $stmt->execute();
            $_SESSION['message'] = "Schedule updated successfully!";
        }

        // Redirect after edit
        header("Location: manage_freelancer_schedules.php");
        exit();
    }

    // ADD
    else {
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Prevent past dates
        if (strtotime($date) < strtotime(date("Y-m-d"))) {
            $_SESSION['message'] = "Cannot select a past date.";
        } else {
            // Check for overlap
            $check_sql = "SELECT * FROM freelancer_schedules WHERE freelancer_id = ? AND date = ? 
                        AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))";
            $stmt_check = $conn->prepare($check_sql);
            $stmt_check->bind_param("isssss", $freelancer_id, $date, $start_time, $start_time, $end_time, $end_time);
            $stmt_check->execute();
            $overlap = $stmt_check->get_result();

            if ($overlap->num_rows > 0) {
                $_SESSION['message'] = "The schedule overlaps with an existing one. Please choose a different time.";
            } else {
                $insert_sql = "INSERT INTO freelancer_schedules (freelancer_id, date, start_time, end_time) 
                            VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($insert_sql);
                $stmt_insert->bind_param("isss", $freelancer_id, $date, $start_time, $end_time);
                $stmt_insert->execute();
                $_SESSION['message'] = "Schedule added successfully!";
            }
        }

        header("Location: manage_freelancer_schedules.php");
        exit();
    }
}

// Fetch all schedules
$sql = "SELECT * FROM freelancer_schedules WHERE freelancer_id = ? ORDER BY date ASC, start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$schedules = $result->fetch_all(MYSQLI_ASSOC);

// Message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Freelancer Schedule</title>
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
    <a href="../admin/freelancer_dashboard.php">← Back to Dashboard</a>
    <h2>Manage Your Freelancer Schedule</h2>

    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <label>Date:</label>
        <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
        <label>Start Time:</label>
        <input type="time" name="start_time" required>
        <label>End Time:</label>
        <input type="time" name="end_time" required>
        <button type="submit">Add Schedule</button>
    </form>

    <table>
        <tr>
            <th>Date</th>
            <th>Start-End</th>
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
                            <a href="manage_freelancer_schedules.php" class="action-btn delete-btn">Cancel</a>
                        </td>
                    </form>
                <?php else: ?>
                    <td><?= date("F j, Y", strtotime($schedule['date'])) ?></td>
                    <td><?= date("g:i A", strtotime($schedule['start_time'])) ?> - <?= date("g:i A", strtotime($schedule['end_time'])) ?></td>
                    <td>
                        <a href="manage_freelancer_schedules.php?edit=<?= $schedule['id'] ?>" class="action-btn edit-btn">Edit</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= $schedule['id'] ?>">
                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
