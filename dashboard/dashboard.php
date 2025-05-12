<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT name FROM users WHERE id = $user_id"); $user =
$result->fetch_assoc(); ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css" />
  </head>
  <body>
    <h1>
      Welcome,
      <?php echo $user['name']; ?>!
    </h1>
    <a href="booked.php">Bookings</a>
    <h2>Events & Planning</h2>

    <ul>
      <li><a href="../services/companies.php">View Companies</a></li>
      <li><a href="../services/freelancers.php">View Freelancers</a></li>
      <li>
        <a href="../messaging/messaging.php">View Chats</a>
      </li>
    </ul>

    <a href="../auth/logout.php">Logout</a>
  </body>
</html>
