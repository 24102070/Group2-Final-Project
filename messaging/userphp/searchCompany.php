<?php
    include_once "../../config/db.php";

    // Execute query to get all users with id > 0
    $searchTerm = mysqli_query($conn, "SELECT * FROM users WHERE id > 0");

    // Initialize output variable
    $output = "";

    // Check if query succeeded
    if ($searchTerm) {
        while ($row = mysqli_fetch_assoc($searchTerm)) {
            // Append data to $output (example)
            $output .= "User ID: " . $row['id'] . " - Name: " . $row['name'] . "<br>";
        }
    } else {
        $output = "Query failed: " . mysqli_error($conn);
    }

    echo $output;
?>
