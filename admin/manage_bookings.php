<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';
$company_id = $_SESSION['user_id'];

// Handle AJAX: fetch bookings for selected date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_date'])) {
    $date = $_POST['fetch_date'];
    $query = "
        SELECT b.id, b.status, u.name, u.email, s.start_time, s.end_time, p.name AS package
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN company_schedules s ON b.schedule_id = s.id
        LEFT JOIN packages p ON b.package_id = p.id
        WHERE b.company_id = ? AND s.date = ?
        ORDER BY s.start_time ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $company_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Determine background color based on status
            $bgColor = '#fff'; // default white
            if ($row['status'] === 'pending') $bgColor = '#fff3cd'; // yellowish
            else if ($row['status'] === 'ongoing') $bgColor = '#bee5eb'; // light blue
            else if ($row['status'] === 'complete') $bgColor = '#d4edda'; // greenish
            else if ($row['status'] === 'rejected') $bgColor = '#f8d7da'; // reddish
            
            echo "<div class='booking-item p-2 mb-2 rounded' id='booking-{$row['id']}' style='background-color: {$bgColor};'>";
            echo "<strong>{$row['name']}</strong> ({$row['email']})<br>";
            echo "Package: " . ($row['package'] ?? 'None') . "<br>";
            echo "Time: " . date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time'])) . "<br>";

            if ($row['status'] === 'pending') {
                echo "<span>Status: <strong>Pending</strong></span><br>";
                echo "<button class='btn btn-success btn-sm action-btn' onclick=\"updateBookingStatus({$row['id']}, 'ongoing')\">Accept</button> ";
                echo "<button class='btn btn-warning btn-sm action-btn' onclick=\"updateBookingStatus({$row['id']}, 'rejected')\">Reject</button>";
            } else if ($row['status'] === 'rejected') {
                echo "<span>Status: <strong>Rejected</strong></span><br>";
            } else {
                // Dropdown for ongoing/complete
                echo "Status: <select class='form-select form-select-sm booking-status-dropdown' onchange='changeStatus({$row['id']}, this.value)' style='width:auto; display:inline-block;'>";
                $statuses = ['ongoing', 'complete'];
                foreach ($statuses as $statusOption) {
                    $selected = ($row['status'] === $statusOption) ? "selected" : "";
                    echo "<option value='{$statusOption}' {$selected}>" . ucfirst($statusOption) . "</option>";
                }
                echo "</select>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>No bookings found on this date.</p>";
    }
    exit();
}

// Handle AJAX: update booking status or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['booking_id'])) {
    $action = $_POST['action'];
    $booking_id = intval($_POST['booking_id']);

    if (in_array($action, ['ongoing', 'complete', 'rejected'])) {
        $query = "UPDATE bookings SET status = ? WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $action, $booking_id, $company_id);
        $stmt->execute();
        echo 'success';
    } elseif ($action === 'delete') {
        $query = "DELETE FROM bookings WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $booking_id, $company_id);
        $stmt->execute();
        echo 'deleted';
    }
    exit();
}

// AJAX: get detailed booking previews for calendar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview_all'])) {
    $query = "
        SELECT s.date, u.name, p.name AS package, b.status
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN company_schedules s ON b.schedule_id = s.id
        LEFT JOIN packages p ON b.package_id = p.id
        WHERE b.company_id = ?
        ORDER BY s.date, u.name
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $color = '#f6c361'; // default yellow for ongoing
        if ($row['status'] === 'complete') $color = '#28a745'; // green
        else if ($row['status'] === 'rejected') $color = '#dc3545'; // red

        $events[] = [
            'title' => $row['name'] . ' (' . ($row['package'] ?? 'No Package') . ')',
            'start' => $row['date'],
            'allDay' => true,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'display' => 'block'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($events);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.8/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

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

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f6f4f3, #ffffff);
    color: #333;
    padding: 40px;
    line-height: 1.6;
    background-attachment: fixed;
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

  .fc .fc-col-header-cell-cushion {
    color: white !important;
  }

  .fc .fc-daygrid-day-number {
    color: #e55c5c !important;
    cursor: pointer;
    text-decoration: none;
  }

  /* Pink color for Today button */
  .fc .fc-today-button {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: white !important;
  }

  /* Pink color for Previous and Next arrow buttons */
  .fc .fc-prev-button,
  .fc .fc-next-button {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: white !important;
  }

  /* Optional: Change color on hover */
  .fc .fc-today-button:hover,
  .fc .fc-prev-button:hover,
  .fc .fc-next-button:hover {
    background-color: #e55c5c !important;
    border-color: #e55c5c !important;
    color: white !important;
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
</style>

</head>
<body>
<div class="container">
    <h1 class="text-center mt-4">Manage Bookings</h1>
    <a href="dashboard.php" class="dashboard-btn mb-3 d-inline-block">←Back to Dashboard</a>
    <div id="calendar"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bookingModalLabel">Bookings on <span id="modalDate"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="bookingDetails">
        <!-- Bookings load here -->
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            $.ajax({
                url: '',
                type: 'POST',
                data: { preview_all: 1 },
                dataType: 'json',
                success: function(data) {
                    successCallback(data);
                },
                error: function() {
                    failureCallback();
                }
            });
        },
        dateClick: function(info) {
            var selectedDate = info.dateStr;
            $('#modalDate').text(selectedDate);
            $('#bookingDetails').html('<p>Loading bookings...</p>');
            $('#bookingModal').modal('show');

            // Fetch bookings for clicked date
            $.post('', { fetch_date: selectedDate }, function(data) {
                $('#bookingDetails').html(data);
            });
        }
    });

    calendar.render();
});

// Update booking status and update UI dynamically without reload
function updateBookingStatus(bookingId, newStatus) {
    $.post('', { action: newStatus, booking_id: bookingId }, function(response) {
        if (response.trim() === 'success') {
            // Find booking div
            var bookingDiv = $('#booking-' + bookingId);

            if (newStatus === 'ongoing') {
                // Change buttons to dropdown for ongoing/complete
                var dropdownHtml = "Status: <select class='form-select form-select-sm booking-status-dropdown' onchange='changeStatus(" + bookingId + ", this.value)' style='width:auto; display:inline-block;'>\
                    <option value='ongoing' selected>Ongoing</option>\
                    <option value='complete'>Complete</option>\
                </select>\
                <button class='btn btn-danger btn-sm ms-2' onclick='deleteBooking(" + bookingId + ")'>Delete</button>";
                bookingDiv.find('button.action-btn').remove();
                bookingDiv.find('span').remove();
                bookingDiv.append(dropdownHtml);
                bookingDiv.css('background-color', '#bee5eb'); // light blue
            } else if (newStatus === 'rejected') {
                bookingDiv.html(bookingDiv.html().replace(/Status:.*$/m, '<span>Status: <strong>Rejected</strong></span>'));
                bookingDiv.find('button.action-btn').remove();
                bookingDiv.css('background-color', '#f8d7da'); // reddish
            } else if (newStatus === 'complete') {
                // Update dropdown selected value and color
                bookingDiv.find('select.booking-status-dropdown').val('complete');
                bookingDiv.css('background-color', '#d4edda'); // greenish
            }

            // If it was pending, remove buttons and add appropriate controls
            if (newStatus === 'ongoing' || newStatus === 'complete') {
                bookingDiv.css('background-color', newStatus === 'ongoing' ? '#bee5eb' : '#d4edda');
            }
        } else {
            alert('Failed to update status. Try again.');
        }
    });
}

function changeStatus(bookingId, newStatus) {
    updateBookingStatus(bookingId, newStatus);
}

function deleteBooking(bookingId) {
    if (!confirm("Are you sure you want to delete this booking?")) return;
    $.post('', { action: 'delete', booking_id: bookingId }, function(response) {
        if (response.trim() === 'deleted') {
            $('#booking-' + bookingId).remove();
        } else {
            alert('Failed to delete booking. Try again.');
        }
    });
}
</script>
</body>
</html>
