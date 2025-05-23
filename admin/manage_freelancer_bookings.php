<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];

// Handle accept or reject booking (same as before)
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

// Handle status update (ongoing/done)
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['new_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    if ($new_status == 'ongoing' || $new_status == 'done') {
        $query = "UPDATE freelancer_bookings SET status = ? WHERE id = ? AND freelancer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $new_status, $booking_id, $freelancer_id);
        $stmt->execute();
        header("Location: manage_freelancer_bookings.php");
        exit();
    }
}

// Get current month and year or from query params for calendar navigation
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Calculate first day of the month and total days
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);
$startDayOfWeek = date('w', $firstDayOfMonth); // 0 (Sun) to 6 (Sat)

// Fetch bookings for this freelancer within this month
$startDate = "$year-$month-01";
$endDate = "$year-$month-$daysInMonth";

$sql = "
    SELECT fb.id, fb.status, fb.schedule_id, 
           fs.date AS schedule_date, fs.start_time, fs.end_time,
           pf.name AS package_name,
           u.name AS user_name
    FROM freelancer_bookings fb
    JOIN freelancer_schedules fs ON fb.schedule_id = fs.id
    LEFT JOIN packages_freelancers pf ON fb.package_id = pf.id
    JOIN users u ON fb.user_id = u.id
    WHERE fb.freelancer_id = ? AND fs.date BETWEEN ? AND ?
    ORDER BY fs.date, fs.start_time
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $freelancer_id, $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Organize bookings by date for quick lookup
$bookingsByDate = [];
while ($row = $result->fetch_assoc()) {
    $bookingsByDate[$row['schedule_date']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Bookings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
       /* General Body and Container */
body {
    font-family: 'Poppins', sans-serif;
    max-width: 900px;
    margin: 20px auto;
    background-color: #f9f9f9;
    color: #333;
}

body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background-image: url('../images/weddingadmin.jfif');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.4;
    z-index: -1;
  }

  h1 {
    text-align: center;
    font-size: 3rem;
    font-weight: 700;
    color: rgb(255, 176, 160);
    margin-bottom: 20px;
    letter-spacing: 1px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
    text-transform: uppercase;
  }
.container {
    padding: 0 15px;
}

/* Header */
h1 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
    color: #222;
}
:root {
      --primary-color: #e4816c;
      --primary-dark: #d56e59;
      --accent-color: #f6c361;
      --danger-color: #f47373;
      --success-color: #73c37f;
      --light-bg: rgba(255, 249, 246, 0.85);
      --text-color: #5c3a2e;
      --light-text: #a08679;
      --border-color: #f3c9b5;
  }


  

  body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background-image: url('../images/weddingadmin.jfif');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.4;
    z-index: -1;
  }

  h1 {
    text-align: center;
    font-size: 3rem;
    font-weight: 700;
    color: rgb(255, 176, 160);
    margin-bottom: 20px;
    letter-spacing: 1px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
    text-transform: uppercase;
  }

.container {
    max-width: 900px; 
    margin: 50px auto;
    padding: 30px;
    background-color: var(--secondary, #f6f4f3);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    overflow-x: auto; 
}


table.calendar {
    width: 100%;
    max-width: 100%; 
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    table-layout: fixed; 
}


table.calendar th, 
table.calendar td {
    border: 1px solid #ddd;
    width: 14.28%; 
    height: 100px;
    vertical-align: top;
    padding: 5px;
    position: relative;
    box-sizing: border-box;
    font-size: 0.9rem;
    color:  rgb(255, 176, 160) !important;
    word-wrap: break-word;  
    overflow-wrap: break-word;
    white-space: normal;  
}

table.calendar th{
    background-color: rgb(255, 176, 160) !important;
    color: white !important;
}


.booking {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

@media (max-width: 600px) {
    table.calendar th, table.calendar td {
        font-size: 0.8rem;
        height: auto;
        padding: 6px;
    }
}

  body {
    background-color: var(--light-bg);
    color: var(--text-color);
  }

  .modal-content {
    background-color: #fff;
    border: 1px solid var(--border-color);
  }

  .modal-header {
    background-color: var(--primary-color);
    color: white;
  }

  .modal-body .booking-item {
    background-color: #fff;
    padding: 10px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-color);
  }

  .btn-success {
    background-color: var(--success-color);
    border-color: var(--success-color);
    color: white;
  }

  .btn-warning {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
    color: var(--text-color);
  }

  .btn-danger {
    background-color: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
  }

  .btn-success:hover {
    background-color: #5aa865;
  }

  .btn-warning:hover {
    background-color: #e5b34f;
  }

  .btn-danger:hover {
    background-color: #e55c5c;
  }

  .action-btn {
    margin-right: 5px;
  }

  #calendar {
    max-width: 2000px;
    margin: 50px auto;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border-top: solid rgb(255, 176, 160) 15px;
  }


  .dashboard-btn {
    background-color: var(--primary-color);
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
    margin-top: 20px;
    transition: background-color 0.3s ease;
  }

  .dashboard-btn:hover {
    background-color: #e55c5c;
  }
/* Calendar Navigation */
.calendar-nav {
    text-align: center;
    margin-bottom: 20px;
}

.calendar-nav a {
    margin: 0 10px;
    text-decoration: none;
    font-weight: 600;
    color: #3498db;
    transition: color 0.3s ease;
}

.calendar-nav a:hover {
    color: #1d6fa5;
}

/* Calendar Table */
table.calendar {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

table.calendar th, table.calendar td {
    border: 1px solid #ddd;
    width: 14.28%;
    height: 120px;
    vertical-align: top;
    padding: 8px;
    position: relative;
    box-sizing: border-box;
    font-size: 0.9rem;
    color: #555;
}

table.calendar th {
    background-color: #f4f4f4;
    font-weight: 600;
    color: #444;
}

/* Date Number in Each Cell */
.date-number {
    font-weight: 700;
    margin-bottom: 8px;
    color: #222;
}

/* Booking Blocks */
.booking {
    background-color: #3498db;
    color: white;
    padding: 5px 8px;
    border-radius: 5px;
    margin-bottom: 6px;
    font-size: 0.85em;
    cursor: pointer;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: opacity 0.2s ease;
}

.booking.pending { background-color: #f39c12; }
.booking.accept { background-color: #27ae60; }
.booking.reject { background-color: #e74c3c; }
.booking.ongoing { background-color: #2980b9; }
.booking.done { background-color: #8e44ad; }

.booking:hover {
    opacity: 0.85;
}

/* Back to Dashboard Button */
a > button {
    display: inline-block;
    background-color: #3498db;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
    margin-top: 10px;
}

a > button:hover {
    background-color: #2980b9;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 60px;
    left: 0; top: 0; width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: auto;
    padding: 25px 30px;
    border-radius: 8px;
    width: 400px;
    max-width: 90%;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.modal-close {
    color: #aaa;
    position: absolute;
    top: 12px;
    right: 18px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: #333;
}

.modal-content h2 {
    margin-top: 0;
    font-weight: 700;
    margin-bottom: 15px;
    color: #222;
}

/* Modal Paragraphs */
.modal-content p {
    margin: 8px 0;
    font-size: 0.95rem;
    color: #444;
}

.modal-content p strong {
    color: #222;
}

/* Modal Action Buttons Container */
#actionButtons {
    margin-top: 20px;
    text-align: center;
}

/* Form inside Modal */
form select {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
    box-sizing: border-box;
}

form button {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #2980b9;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    table.calendar th, table.calendar td {
        height: 100px;
        font-size: 0.8rem;
        padding: 6px;
    }
    .modal-content {
        width: 95%;
        padding: 20px;
    }
}

    </style>
</head>
<body>

<div class="container">
    <h1><?= htmlspecialchars($monthName . " " . $year) ?> Manage Bookings</h1>
     <a href="freelancer_dashboard.php"><button style = "background-color:#e55c5c; ">Back to Dashboard</button></a>
    <div class="calendar-nav">
        <a style = "color:  #e55c5c;"href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>">&#8592; Prev</a>
        <a style = "color:  #e55c5c;" href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>">Next &#8594;</a>
    </div>

    <table class="calendar">
        <thead>
            <tr>
                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th>
                <th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $day = 1;
        $cells = 0;
        echo "<tr>";

        // Empty cells before first day of month
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            echo "<td></td>";
            $cells++;
        }

        while ($day <= $daysInMonth) {
            if ($cells % 7 == 0 && $cells != 0) {
                echo "</tr><tr>";
            }

            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            echo "<td>";
            echo "<div class='date-number'>$day</div>";

            if (isset($bookingsByDate[$dateStr])) {
                foreach ($bookingsByDate[$dateStr] as $booking) {
                    $statusClass = htmlspecialchars($booking['status']);
                    $displayText = htmlspecialchars($booking['user_name']) . " - " . htmlspecialchars($booking['package_name'] ?? 'No Package');

                    // Use data attributes to pass booking info to modal
                    echo "<div 
                            class='booking $statusClass' 
                            data-booking-id='" . $booking['id'] . "'
                            data-status='" . $booking['status'] . "'
                            data-user-name='" . htmlspecialchars($booking['user_name'], ENT_QUOTES) . "'
                            data-package='" . htmlspecialchars($booking['package_name'] ?? 'No Package', ENT_QUOTES) . "'
                            data-date='" . $dateStr . "'
                            data-start='" . $booking['start_time'] . "'
                            data-end='" . $booking['end_time'] . "'
                            >
                            $displayText
                          </div>";
                }
            }

            echo "</td>";
            $day++;
            $cells++;
        }

        // Fill remaining cells of last week
        while ($cells % 7 != 0) {
            echo "<td></td>";
            $cells++;
        }
        echo "</tr>";
        ?>
        </tbody>
    </table>

    <br>
   
</div>

<!-- Modal for booking details and actions -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Booking Details</h2>
        <p><strong>User:</strong> <span id="modalUserName"></span></p>
        <p><strong>Package:</strong> <span id="modalPackage"></span></p>
        <p><strong>Schedule:</strong> <span id="modalSchedule"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>

        <div id="actionButtons"></div>
    </div>
</div>

<script>
    const modal = document.getElementById('bookingModal');
    const modalClose = document.querySelector('.modal-close');
    const modalUserName = document.getElementById('modalUserName');
    const modalPackage = document.getElementById('modalPackage');
    const modalSchedule = document.getElementById('modalSchedule');
    const modalStatus = document.getElementById('modalStatus');
    const actionButtons = document.getElementById('actionButtons');

    document.querySelectorAll('.booking').forEach(el => {
        el.addEventListener('click', () => {
            const bookingId = el.getAttribute('data-booking-id');
            const userName = el.getAttribute('data-user-name');
            const packageName = el.getAttribute('data-package');
            const date = el.getAttribute('data-date');
            const start = el.getAttribute('data-start');
            const end = el.getAttribute('data-end');
            const status = el.getAttribute('data-status');

            modalUserName.textContent = userName;
            modalPackage.textContent = packageName;
            modalSchedule.textContent = date + ' ' + start + ' - ' + end;
            modalStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1);

            // Clear previous buttons
            actionButtons.innerHTML = '';

            // Show action buttons based on status
            if (status === 'pending') {
                actionButtons.innerHTML = `
                    <a href="?action=accept&booking_id=${bookingId}" class="btn-accept" style="margin-right: 10px; color: green; font-weight: 600;">Accept</a>
                    <a href="?action=reject&booking_id=${bookingId}" class="btn-reject" style="color: red; font-weight: 600;">Reject</a>
                `;
            } else if (status === 'accept') {
                actionButtons.innerHTML = `
                    <form method="POST" action="manage_freelancer_bookings.php">
                        <input type="hidden" name="booking_id" value="${bookingId}">
                        <select name="new_status" onchange="this.form.submit()">
                            <option value="ongoing">Ongoing</option>
                            <option value="done">Done</option>
                        </select>
                        <input type="hidden" name="update_status" value="true">
                    </form>
                `;
            } else {
                actionButtons.textContent = "No actions available.";
            }

            modal.style.display = 'block';
        });
    });

    modalClose.onclick = () => {
        modal.style.display = 'none';
    };

    window.onclick = event => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
