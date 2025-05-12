<?php
session_start();
include_once("../../config/db.php");

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$recipient_id = isset($_GET['recipient_id']) ? $_GET['recipient_id'] : null;
$recipient_name = isset($_GET['name']) ? $_GET['name'] : null;
$recipient_type = isset($_GET['type']) ? $_GET['type'] : null;

// Fetch freelancer profiles
$sql = mysqli_query($conn, "SELECT * FROM freelancer_profiles");
$freelancer_profiles = mysqli_fetch_all($sql, MYSQLI_ASSOC);  // Fetch all rows as an associative array

// Fetch freelancer names (only approved ones)
$names = mysqli_query($conn, "SELECT * FROM freelancers WHERE approval='Approved'");
$freelancers = mysqli_fetch_all($names, MYSQLI_ASSOC);  // Fetch all rows as an associative array

$output = "";

if (count($freelancers) == 0) {
    $output .= "No users are available to chat";
} else {
    // Iterate through freelancers
    for ($i = 0; $i < count($freelancers); $i++) {
        $freelancer = isset($freelancers[$i]) ? $freelancers[$i] : null;
        $profile = isset($freelancer_profiles[$i]) ? $freelancer_profiles[$i] : null;

        // Set fallback profile photo
        $photo = (!empty($profile['profile_photo'])) 
            ? '../' . $profile['profile_photo'] 
            : 'https://ui-avatars.com/api/?name=' . urlencode($freelancer['name']);

        if ($freelancer) {
            $output .= '
                <div class="conversation-card" data-id="' . $freelancer['id'] . '"data-name="' . htmlspecialchars($freelancer['name']) . '"
                data-type="Freelancer"data-user-type="freelancer"onclick="openChatWindow(this)">
                    <div class="profile-picture" style="background-image: url(\'' . $photo . '\');"></div>
                    <div class="message-content">
                        <div class="username">' . htmlspecialchars($freelancer['name']) . '<div class="on-status"></div></div>
                        <div class="message">this is a test<span class="timestamp">3:00 AM</span></div>
                    </div>
                </div>
            ';
        }
    }
}

echo $output;
?>