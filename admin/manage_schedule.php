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

    // First delete any related bookings
    $stmt = $conn->prepare("DELETE FROM bookings WHERE schedule_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Then delete the schedule itself
    $stmt = $conn->prepare("DELETE FROM company_schedules WHERE id = ? AND company_id = ?");
    $stmt->bind_param("ii", $id, $company_id);
    $stmt->execute();

    $_SESSION['message'] = "Schedule and related bookings successfully deleted.";
    header("Location: manage_schedule.php");
    exit();
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
    <link rel="stylesheet" href="../assets/manage_schedules.css">
    <style>
       
      
.btn {
    padding: 10px 16px;
    border-radius: 30px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: none;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-add {
    background-color: rgba(230, 123, 123, 0.9);
    color: white;
    width: 100%;
    padding: 12px;
    margin-top: 15px;
}

.btn-add:hover {
    background-color: rgba(212, 106, 106, 0.9);
    box-shadow: 0 4px 12px rgba(230, 123, 123, 0.3);
}

.btn-edit {
    background-color: rgba(251, 188, 4, 0.9);
    color: white;
}

.btn-edit:hover {
    background-color: rgba(230, 170, 0, 0.9);
    box-shadow: 0 4px 12px rgba(251, 188, 4, 0.3);
}

.btn-delete {
    background-color: rgba(234, 67, 53, 0.9);
    color: white;
}

.btn-delete:hover {
    background-color: rgba(210, 50, 40, 0.9);
    box-shadow: 0 4px 12px rgba(234, 67, 53, 0.3);
}
/* Save button */
.save-btn {
    background-color: #28a745;
    color: white;
}
.save-btn:hover {
    background-color: #218838;
}
.dashboard-btn {
    background-color: #6c757d;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
    margin-top: 20px;
    transition: background-color 0.3s ease;
}

.dashboard-btn:hover {
    background-color: #5a6268;
}
.btn-save {
    background-color: rgba(52, 168, 83, 0.9);
    color: white;
}

.btn-save:hover {
    background-color: rgba(40, 140, 70, 0.9);
    box-shadow: 0 4px 12px rgba(52, 168, 83, 0.3);
}

.btn-cancel {
    background-color: rgba(108, 117, 125, 0.9);
    color: white;
}

.btn-cancel:hover {
    background-color: rgba(90, 98, 104, 0.9);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
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
                    <form class = "formcal"method="POST">
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
                            <button type="submit" class="btn btn-save">Save</button>
                            <a href="manage_schedule.php" class="btn btn-cancel">Cancel</a>
                        </td>
                    </form>
                <?php else: ?>
                    <td><?= date("F j, Y", strtotime($schedule['date'])) ?></td>
                    <td><?= date("g:i A", strtotime($schedule['start_time'])) ?> - <?= date("g:i A", strtotime($schedule['end_time'])) ?></td>
                    <td>
                     
                        <a href="manage_schedule.php?edit=<?= $schedule['id'] ?>" class="btn btn-edit">Edit</a>
                
             
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= $schedule['id'] ?>">
                            <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure?')">Remove</button>
                        </form>

                       
             
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="dashboard.php" class="dashboard-btn">←Back to Dashboard</a>
</div>


</body>
</html>
