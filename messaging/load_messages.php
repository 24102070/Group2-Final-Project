<?php
session_start();
include '../config/db.php';

$user_id = $_GET['user_id'];
$user_type = $_GET['user_type'];
$recipient_id = $_GET['recipient_id'];
$recipient_type = $_GET['recipient_type'];

$stmt = $conn->prepare("SELECT user_id, user_type, text_input, sent_at FROM messaging 
    WHERE (user_id = ? AND user_type = ? AND recipient_id = ? AND recipient_type = ?) 
    OR (user_id = ? AND user_type = ? AND recipient_id = ? AND recipient_type = ?) 
    ORDER BY sent_at ASC");

$stmt->bind_param("isssisss", 
    $user_id, $user_type, $recipient_id, $recipient_type,
    $recipient_id, $recipient_type, $user_id, $user_type
);

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>