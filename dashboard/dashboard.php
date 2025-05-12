<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT name FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PeachFolio</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/dashboard_2.css">

</head>

<body>
    <div class="bg-elements">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
    </div>

    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
<div class = "dash-con">
    <div class="dashboard-container">
        <div class="user-avatar"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></div>
        
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p style="color: var(--peach-accent); margin-bottom: 1.5rem;">What would you like to do today?</p>

        <a href="booked.php" class="action-btn">
            <i class="far fa-calendar-alt"></i> My Bookings
        </a>

        <h2><span>Explore Services</span></h2>
        <ul class="nav-links">
            <li>
                <a href="../services/companies.php">
                    <i class="fas fa-building"></i> View Companies
                </a>
            </li>
            <li>
                <a href="../services/freelancers.php">
                    <i class="fas fa-user-tie"></i> View Freelancers
                </a>
            </li>

            <li>
                <a href="../messaging/messaging.php"><i class="fas fa-comment"></i>View Chats</a>
            </li>
        
        <a class="logout" href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

    <script>
        // Add occasional confetti for a celebratory feel
        function createConfetti() {
            const colors = ['#FFD3B6', '#FFAAA5', '#FF8B94', '#DC8665'];
            const container = document.body;
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 3 + 's';
                container.appendChild(confetti);
                
                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, 3000);
            }
        }

        // Run confetti occasionally
        setInterval(createConfetti, 30000);
        
        // Also run when clicking the dashboard
        document.querySelector('.dashboard-container').addEventListener('click', function() {
            if(Math.random() > 0.8) { // 20% chance on click
                createConfetti();
            }
        });
    </script>
</body>
</html>