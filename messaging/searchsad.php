<?php

//SETTINGS 
include '../config/db.php';

//CONNECT TO DATABASE

//SEARCH
$stmt = $pdo->prepare("SELECT * FROM `temp_table` WHERE `user` LIKE ? OR `email` LIKE ?");
$stmt->execute([
    "%".$_POST['search']."%", "%".$_POST['search']."%"
]);

$results = $stmt->fetchAll();


