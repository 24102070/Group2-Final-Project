<?php
session_start();
include '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'];
$user_type = $data['user_type'];
$recipient_id = $data['recipient_id'];
$recipient_type = $data['recipient_type'];
$text_input = $data['text_input'];

if ($user_id && $user_type && $recipient_id && $recipient_type && $text_input) {
    $stmt = $conn->prepare("INSERT INTO messaging (user_id, user_type, recipient_id, recipient_type, text_input) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $user_type, $recipient_id, $recipient_type, $text_input);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}

$conn->close();
?>