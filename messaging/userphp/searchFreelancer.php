<?php
    include_once "../../config/db.php";
    $searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm']);
    $output = "";
    $sql= mysqli_query($conn, "SELECT * FROM freelancers where name LIKE '%{$searchTerm}%' AND approval='Approved'");
    if(mysqli_num_rows($sql)>0){

    }
    else{
        $output .= 'No user found related to your search term';
    }
    echo $output;
?>