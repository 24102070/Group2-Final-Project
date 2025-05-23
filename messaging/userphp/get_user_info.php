<?php
function getUserName() {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        return null;
    }

    global $conn;
    $userId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    $role = $_SESSION['role'];

    switch ($role) {
        case 'admin':
        case 'user':
            $sql = "SELECT name FROM users WHERE id = ?";
            break;
        case 'company':
            $sql = "SELECT company_name as name FROM companies WHERE id = ?";
            break;
        case 'freelancer':
            $sql = "SELECT CONCAT(first_name, ' ', last_name) as name FROM freelancers WHERE id = ?";
            break;
        default:
            return null;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['name'];
    }
    
    return null;
}
?> 