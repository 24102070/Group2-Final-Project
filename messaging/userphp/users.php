<?php
session_start();
include_once("../../config/db.php");

// Fetch users (only approved ones)
$names = mysqli_query($conn, "SELECT * FROM users");
$users = mysqli_fetch_all($names, MYSQLI_ASSOC);  // Fetch all rows as an associative array

$output = "";

if (count($users) == 0) {
    $output .= "No users are available to chat";
} else {
    // Iterate through users
    for ($i = 0; $i < count($users); $i++) {
        $user = isset($users[$i]) ? $users[$i] : null;

        // Set fallback profile photo (using the user's name to generate a default avatar)
        $photo = !empty($user['profile_photo']) 
            ? '../' . $user['profile_photo']  // If profile photo exists, use it
            : 'https://ui-avatars.com/api/?name=' . urlencode($user['NAME']);  // Otherwise, use a fallback avatar

        if ($user) {
            $output .= '
                <div class="conversation-card"
                     data-id="' . $user['id'] . '"
                     data-name="' . htmlspecialchars($user['NAME']) . '"
                     data-type="User"
                     data-user-type="user"
                     onclick="openChatWindow(this)">
                    <div class="profile-picture" style="background-image: url(\'' . $photo . '\');"></div>
                    <div class="message-content">
                        <div class="username">' . htmlspecialchars($user['NAME']) . '<div class="on-status"></div></div>
                        <div class="message">this is a test<span class="timestamp">3:00 AM</span></div>
                    </div>
                </div>
            ';
        }
    }
}

echo $output;
?>
