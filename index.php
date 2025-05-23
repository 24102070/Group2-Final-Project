<?php
include 'config/db.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EVE- Events, Venue, and Experience</title>

  <!-- Fonts and Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/hompage.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    html, body{
      scroll-behavior: smooth;
    }

    .login-container{
      gap: 10px;
    }
    
  </style>
</head>
<body>

<!-- NAVIGATION -->
<nav class="navbar">
  <div class="nav-left">
    <img src="assets/IMAGES/logo.png" alt="Logo" class="nav-logo-img">
    <div class="nav-logo-text">
      <span class="logo-line1">EVE</span>
      <span class="logo-line2">Events, Venue, Experience</span>
    </div>
  </div>

  <!-- Hamburger menu (mobile view) -->
  <div class="nav-toggle" id="nav-toggle">
    <i class="fas fa-bars"></i>
  </div>

  <ul class="nav-links" id="nav-links">
    <li><a href="#stats">About Us</a></li>
    <li><a href="#feat">Services</a></li>
    <li><a href="#foot">Contact Us</a></li>
  </ul>

  <div class="nav-right">
    <div class="search-container">
      <input type="text" placeholder="Search..." class="search-input">
      <button class="search-btn"><i class="fas fa-search"></i></button>
    </div>
    <div class="login-container" style = "background-color: white;">
  
      <a href="auth/login.php" class="login-btn">Sign in</a>
       <a href="auth/register_company.php" class="login-btn">Company Site</a>
        <a href="auth/register_freelancer.php" class="login-btn">Freelancer Site</a>
    </div>
  </div>
</nav>

<script>
  // Toggle the nav menu on mobile
  const navToggle = document.getElementById('nav-toggle');
  const navLinks = document.getElementById('nav-links');

  navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });
</script>


<section class="hero">
  <div class="hero-container">
    <div class="hero-left">
      <p class="hero-text">
        <span class="black-text">Where Your</span>
        <span class="peach-italic">Dream Day</span>
        <span class="black-text">Begins</span>
      </p>
    </div>

    <img src="assets/IMAGES/hero_middle.png" alt="Hero Image" class="hero-image" />

    <div class="hero-right">
      <p class="hero-text">
        <span class="black-text">Book your</span>
        <span class="peach-italic">Perfect Wedding</span>
        <span class="black-text">with</span><br>
        <span class="black-text">Ease</span>
      </p>
    </div>
  </div>
</section>



<!-- STATS SECTION -->
<section class="stats-section">
  <div class="stats-box" id = "stats">
    <img src="assets/IMAGES/HERO-STATS.png" alt="Statistics background">
    <div class="stats-content">
      <div class="stats-text">
        <div class="stats-number">1,000+</div>
        <div class="stats-label">Events Hosted</div>
      </div>
      <div class="stats-text-center">
        <div class="stats-number-center">1,500+</div>
        <div class="stats-label">Happy Clients</div>
      </div>
      <div class="stats-text">
        <div class="stats-number">1,000+</div>
        <div class="stats-label">Venues Available</div>
      </div>
    </div>
  </div>
</section>

<!-- SLIDER -->
<iframe src="assets/slider.html" width="76%" height="500" style="border: none; background: transparent; border-radius: 1rem; margin: 0 auto; display: block;"></iframe>

<!-- COLLABORATORS SECTION -->
<!-- COLLABORATORS & FEATURED SECTION -->
<!-- COLLABORATORS & FEATURED SECTION -->
<section class="collaborators-featured">
  <div class="collaborators-list">
    <h3>Who Can Be Our Collaborators?</h3>
    <div class="scrolling-text">
      <span>
        🎉 Event Planning Companies | 🍽️ Catering Services | 📷 Photographers & Videographers |
        🎶 Entertainment Providers | 🏨 Venue Providers | 🎨 Decor & Styling Services | 💄 Freelance Professionals
      </span>
    </div>
  </div>

  <div class="featured-grid" id = "feat">
    <h3>Featured Companies & Freelancers</h3>
    <div class="grid">
      <div class="card">
        <img src="assets/IMAGES/dreamevents.jpg" alt="DreamEvents PH" class="card-img" />
        <h3>DreamEvents PH</h3>
        <p>Luxury weddings and corporate gatherings. Flawless execution.</p>
        <p>💰 Min Fee: PHP 50,000</p>
        <a href="#" class="card-btn">View More</a>
      </div>
      <div class="card">
        <img src="assets/IMAGES/festivecreations.jpg" alt="Festive Creations" class="card-img" />
        <h3>Festive Creations</h3>
        <p>Creative, budget-friendly events for every occasion.</p>
        <p>💰 Min Fee: PHP 30,000</p>
        <a href="#" class="card-btn">View More</a>
      </div>
      <div class="card">
        <img src="assets/IMAGES/markvillanueva.jfif" alt="Mark Villanueva" class="card-img" />
        <h3>Mark Villanueva (Photographer)</h3>
        <p>Capturing life’s best moments.</p>
        <p>💰 Min Fee: PHP 5,000</p>
        <a href="#" class="card-btn">View More</a>
      </div>
    </div>
  </div>
</section>



<!-- FOOTER -->
<footer class="footer">
  <div class="footer-container">
    <!-- About -->
    <div class="footer-section">
      <h3 class="footer-heading">About Us</h3>
      <p class="footer-about">We specialize in creating unforgettable event experiences with our premium venues and exceptional service.</p>
      <div class="footer-social">
        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="footer-section" id = "foot">
      <h3 class="footer-heading">Quick Links</h3>
      <ul class="footer-links">
        <li><a href="#">Home</a></li>
        <li><a href="#">Our Venues</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Gallery</a></li>
        <li><a href="#">Testimonials</a></li>
        <li><a href="#">FAQ</a></li>
      </ul>
    </div>

    <!-- Contact Info -->
    <div class="footer-section">
      <h3 class="footer-heading">Contact Us</h3>
      <div class="footer-contact">
        <div class="contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>123 Event Street, City, Country</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-phone"></i>
          <span>+1 (234) 567-8900</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <span>info@eventsvenue.com</span>
        </div>
      </div>
    </div>

    <!-- Newsletter -->
    <div class="footer-section">
      <h3 class="footer-heading">Newsletter</h3>
      <p class="footer-newsletter-text">Subscribe to get updates on our latest offers</p>
      <form class="newsletter-form">
        <input type="email" placeholder="Your Email" required>
        <button type="submit" class="newsletter-btn">Subscribe</button>
      </form>
    </div>
  </div>

  <!-- Bottom Footer -->
  <div class="footer-bottom">
    <p>&copy; 2025 EVE Events. All rights reserved.</p>
    <div class="footer-legal">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
      <a href="#">Sitemap</a>
    </div>
  </div>
</footer>

<!-- Scroll to Top Button -->
<button onclick="scrollToTop()" id="scrollTopBtn" title="Go to top">
  <i class="fas fa-arrow-up"></i>
</button>

<script>
  // Scroll to top 
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Show or hide the scroll button
  window.onscroll = function () {
    const btn = document.getElementById("scrollTopBtn");
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
      btn.style.display = "block";
    } else {
      btn.style.display = "none";
    }
  };

  // Scroll to top when any navbar link is clicked
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', function () {
      scrollToTop();
    });
  });
</script>

<style>
  #scrollTopBtn {
    display: none;
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 99;
    font-size: 18px;
    border: none;
    outline: none;
    background-color: #f28585;
    color: white;
    cursor: pointer;
    padding: 12px 16px;
    border-radius: 50%;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.2);
    transition: background-color 0.3s ease;
  }

  #scrollTopBtn:hover {
    background-color: #db6e6e;
  }

  #scrollTopBtn i {
    font-size: 20px;
  }
</style>

</body>
</html>
