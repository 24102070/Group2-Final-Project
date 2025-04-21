<?php
include 'config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Planning Hub</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

    <header>
        <h1>Plan Your Dream Event with Ease!</h1>
        <p>Find, book, and collaborate effortlessly with top-rated event professionals.</p>
        <a href="auth/register.php" class="btn">Get Started</a>
        <a href="auth/login.php" class="btn">Log In</a>
    </header>

    <section class="collaborators">
        <h2>Who Can Be Our Collaborators?</h2>
        <ul>
            <li>🎉 Event Planning Companies</li>
            <li>🍽️ Catering Services</li>
            <li>📷 Photographers & Videographers</li>
            <li>🎶 Entertainment Providers</li>
            <li>🏨 Venue Providers</li>
            <li>🎨 Decor & Styling Services</li>
            <li>💄 Freelance Professionals</li>
        </ul>
    </section>

    <section class="featured">
        <h2>Featured Companies & Freelancers</h2>
        <div class="grid">
            <div class="card">
                <h3>DreamEvents PH</h3>
                <p>Luxury weddings and corporate gatherings. Flawless execution.</p>
                <p>💰 Min Fee: PHP 50,000</p>
            </div>
            <div class="card">
                <h3>Festive Creations</h3>
                <p>Creative, budget-friendly events for every occasion.</p>
                <p>💰 Min Fee: PHP 30,000</p>
            </div>
            <div class="card">
                <h3>Mark Villanueva (Photographer)</h3>
                <p>Capturing life’s best moments.</p>
                <p>💰 Min Fee: PHP 5,000</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Event Planning Hub. All rights reserved.</p>
    </footer>

</body>
</html>
