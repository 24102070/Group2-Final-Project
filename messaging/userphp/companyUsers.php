<?php
session_start();
include_once("../../config/db.php");

// Fetch all company profiles
$sql = mysqli_query($conn, "SELECT * FROM company_profiles");
$company_profiles = mysqli_fetch_all($sql, MYSQLI_ASSOC);  // All company_profiles

// Fetch all approved companies
$names = mysqli_query($conn, "SELECT * FROM companies WHERE approval='Approved'");
$companies = mysqli_fetch_all($names, MYSQLI_ASSOC);  // All approved companies

$output = "";

// If no approved companies
if (count($companies) == 0) {
    $output .= "No users are available to chat";
} else {
    // Create an associative array to quickly find profile by company_id
    $profiles_assoc = [];
    foreach ($company_profiles as $profile) {
        $profiles_assoc[$profile['company_id']] = $profile;
    }

    // Loop through approved companies
    foreach ($companies as $company) {
        // Match profile based on company_id
        $profile = isset($profiles_assoc[$company['id']]) ? $profiles_assoc[$company['id']] : null;

        // Use fallback image if no profile or photo is empty
        $photo = ($profile && !empty($profile['profile_photo'])) ? $profile['profile_photo'] : 'images/default-profile.png';

        $output .= '
            <div class="conversation-card" data-id="' . $company['id'] . '" data-name="' . htmlspecialchars($company['name']) . '"
                data-type="Company" data-user-type="company" onclick="openChatWindow(this)">
                <div class="profile-picture" style="background-image: url(\'../' . $photo . '\');"></div>
                <div class="message-content">
                    <div class="username">' . htmlspecialchars($company['name']) . '<div class="on-status"></div></div>
                    <div class="message">this is a test<span class="timestamp">3:00 AM</span></div>
                </div>
            </div>
        ';
    }
}

echo $output;
?>
