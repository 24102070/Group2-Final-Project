<?php
session_start();
include_once("../../config/db.php");

// Fetch company profiles
$sql = mysqli_query($conn, "SELECT * FROM company_profiles");
$company_profiles = mysqli_fetch_all($sql, MYSQLI_ASSOC);  // Fetch all rows from company_profiles

// Fetch companies
$names = mysqli_query($conn, "SELECT * FROM companies WHERE approval='Approved'");
$companies = mysqli_fetch_all($names, MYSQLI_ASSOC);  // Fetch all rows from companies

$output = "";

// Check if there are company profiles
if (count($company_profiles) == 0) {
    $output .= "No users are available to chat";
} else {
    // Loop through company_profiles and companies in parallel
    for ($i = 0; $i < count($companies); $i++) {
        $profile = $company_profiles[$i];  // Get the current company profile
        $company = isset($companies[$i]) ? $companies[$i] : null;  // Get the corresponding company

        if ($company) {
            // Use fallback image if profile_photo is empty
            $photo = !empty($profile['profile_photo']) ? $profile['profile_photo'] : 'images/default-profile.png';

            $output .= '
                    <div class="conversation-card" data-id="' . $company['id'] . '"data-name="' . htmlspecialchars($company['name']) . '"
                     data-type="Company" data-user-type="company" onclick="openChatWindow(this)">
                    <div class="profile-picture" style="background-image: url(\'../' . $photo . '\');"></div>
                    <div class="message-content">
                        <div class="username">' . $company['name'] . '<div class="on-status"></div></div>
                        <div class="message">this is a test<span class="timestamp">3:00 AM</span></div>
                    </div>
                </div>
            ';
        }
    }
}

echo $output;
?>