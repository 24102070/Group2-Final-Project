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
<html lang="en">
<head>
    <title>Manage Freelancer Schedule</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/manage_freelancer_schedules.css">

</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <div class="glass-header">
            <h2><i class="far fa-calendar-alt"></i> Manage Your Schedule</h2>
        </div>

        <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

        <form method="POST">
            <label>Date:</label>
            <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
            <label>Start Time:</label>
            <input type="time" name="start_time" required>
            <label>End Time:</label>
            <input type="time" name="end_time" required>
            <button type="submit" class="btn btn-add"><i class="fas fa-plus"></i> Add Schedule</button>
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
                                <span>to</span>
                                <input type="time" name="edit_end_time" value="<?= htmlspecialchars($schedule['end_time']) ?>" required>
                            </td>
                            <td>
                                <input type="hidden" name="edit_id" value="<?= $schedule['id'] ?>">
                                <button type="submit" class="btn btn-save"><i class="fas fa-save"></i> Save</button>
                                <a href="manage_freelancer_schedules.php" class="btn btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                            </td>
                        </form>
                    <?php else: ?>
                        <td><?= date("F j, Y", strtotime($schedule['date'])) ?></td>
                        <td><?= date("g:i A", strtotime($schedule['start_time'])) ?> - <?= date("g:i A", strtotime($schedule['end_time'])) ?></td>
                        <td>
                            <a href="manage_freelancer_schedules.php?edit=<?= $schedule['id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete" value="<?= $schedule['id'] ?>">
                                <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i> Delete</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="btn-back">
            <a href="../admin/freelancer_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

</body>
</html>