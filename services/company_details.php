<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';



$company_id = $_GET['id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $company_id = $_POST['company_id'];
    $package_id = $_POST['package_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];
    $user_id = $_SESSION['user_id'];

    // Prevent duplicate review
    $check_sql = "SELECT id FROM company_reviews WHERE company_id = ? AND package_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("iii", $company_id, $package_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $review_msg = "<p style='color:red;'>You have already reviewed this package.</p>";
    } else {
        $insert_sql = "INSERT INTO company_reviews (company_id, package_id, user_id, rating, review) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiiis", $company_id, $package_id, $user_id, $rating, $review);
        if ($stmt->execute()) {
            $review_msg = "<p style='color:green;'>Review submitted successfully!</p>";
        } else {
            $review_msg = "<p style='color:red;'>Something went wrong. Try again.</p>";
        }
    }
}



// Fetch company profile
$sql = "SELECT c.name, c.email, c.minimum_fee, 
               p.profile_photo, p.cover_photo, p.about, p.contact 
        FROM companies c 
        LEFT JOIN company_profiles p ON c.id = p.company_id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

if (!$company) {
    echo "Company not found!";
    exit();
}

// Fetch packages
$sql_packages = "SELECT id, name, details, price, image FROM packages WHERE company_id = ?";
$stmt_packages = $conn->prepare($sql_packages);
$stmt_packages->bind_param("i", $company_id);
$stmt_packages->execute();
$result_packages = $stmt_packages->get_result();

// Fetch posts
$sql_posts = "SELECT caption, media_path, media_type, created_at 
              FROM company_posts 
              WHERE company_id = ? 
              ORDER BY created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $company_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

$profile_photo = !empty($company['profile_photo']) ? "../" . $company['profile_photo'] : "../assets/default-profile.png";
$cover_photo = !empty($company['cover_photo']) ? "../" . $company['cover_photo'] : "../assets/default-cover.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($company['name']); ?> - Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
  

    <style>
        :root {
            --peach-primary: #ff9a8b;
            --peach-secondary: #ff6b95;
            --peach-light: #ffcad4;
            --peach-lighter: #fff0f3;
            --peach-dark: #e75480;
            --peach-text: #5a3d2b;
            --peach-shadow: rgba(255, 154, 139, 0.3);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 90px;
            padding: 0;
            background-color: url('https://images.unsplash.com/photo-1722925541444-d53b83e338b6?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            line-height: 1.6;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .company-header {
            position: relative;
            margin-bottom: 50px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--peach-shadow);
        }
        
        .header-wrapper {
            position: relative;
            padding-bottom: 200px; /* Ensures space for profile image */
        }

        .profile-container {
            position: absolute;
            bottom: -95px; /* Push it below the cover photo */
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .cover-wrapper {
    position: relative;
    height: 350px;
    overflow: visible;
    display: flex;
    justify-content: center;  /* Centers the cover photo horizontally */
    align-items: center;  /* Centers the cover photo vertically */
}

.cover-photo {
    width: 100vw;            /* Ensures the width of the cover photo covers most of the screen */
    max-height: 300px;      /* Sets a max height */
    object-fit: cover;      /* Ensures the image covers the space */
    display: block;
    border-radius: 30px;
}


        .profile-wrapper {
            position: relative;
            margin-top: -75px; /* pull profile upwards to overlap */
            height: 150px;      /* match profile photo height */
            z-index: 10;
        }

        .profile-container {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .profile-photo:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .company-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px var(--peach-shadow);
            transition: transform 0.3s ease;
        }
        
        .company-card:hover {
            transform: translateY(-5px);
        }
        
        h1, h2, h3, h4 {
            color: #ff9a8b;
            margin-top: 0;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(to right, var(--peach-primary), var(--peach-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        h2 {
            font-size: 2rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, var(--peach-primary), var(--peach-secondary));
            border-radius: 3px;
        }
        
        .section-divider {
            height: 2px;
            background: linear-gradient(to right, transparent, var(--peach-light), transparent);
            margin: 40px 0;
            border: none;
        }
        
        /* Package Cards */
        .package-grid, .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .package-card, .post-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px var(--peach-shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .package-card:hover, .post-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px var(--peach-shadow);
        }
        
        .package-image-container, .post-media-container {
            height: 200px;
            overflow: hidden;
        }
        
        .package-image, .post-media {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .package-card:hover .package-image, .post-card:hover .post-media {
            transform: scale(1.05);
        }
        
        .package-content, .post-content {
            padding: 20px;
            position: relative;
        }
        
        .package-title, .post-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--peach-dark);
        }
        
        .package-price {
            font-weight: 700;
            color: var(--peach-secondary);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .package-details, .post-full {
            color: var(--peach-text);
            margin-bottom: 15px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }
        
        .package-card.expanded .package-details,
        .post-card.expanded .post-full {
            max-height: 500px;
        }
        
        .expand-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            color: var(--peach-primary);
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        
        .package-card.expanded .expand-icon,
        .post-card.expanded .expand-icon {
            transform: rotate(180deg);
        }
        
        .post-preview {
            color: var(--peach-text);
            margin-bottom: 15px;
        }
        
        .post-card:not(.expanded) .post-full {
            display: none;
        }
        
        .post-card.expanded .post-preview {
            display: none;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--peach-primary), var(--peach-secondary));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px var(--peach-shadow);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--peach-primary);
            color: var(--peach-primary);
        }
        
        .btn-outline:hover {
            background: var(--peach-primary);
            color: white;
        }
        
        /* Reviews Section */
        .review-section {
            margin-top: 30px;
        }
        /* Initially hide the review section */
/* Initially hide the review section */
.package-card .review-section {
    display: none;
}

/* When expanded, show the review section */
.package-card.expanded .review-section {
    display: block;
}


        
        .review-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px var(--peach-shadow);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .review-author {
            font-weight: 600;
            color: var(--peach-dark);
        }
        
        .review-date {
            color: #999;
            font-size: 0.9rem;
        }
        
        .review-rating {
            color: #ffc107;
            margin-bottom: 10px;
        }
        
        .review-text {
            color: var(--peach-text);
        }
        
        /* Star Rating */
        .star-rating {
            direction: rtl;
            display: inline-block;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            color: #ddd;
            font-size: 25px;
            padding: 0 5px;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .star-rating input:checked ~ label,
        .star-rating input:hover ~ label,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
        
        /* Review Form */
        .review-form {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px var(--peach-shadow);
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--peach-primary);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .post-date {
            color: #999;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            background: linear-gradient(to right, var(--peach-primary), var(--peach-secondary));
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px var(--peach-shadow);
        }
        
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--peach-shadow);
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .mt-0 { margin-top: 0; }
        .mb-0 { margin-bottom: 0; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }

        /* In the responsive section */
        @media (max-width: 768px) {
            .profile-photo {
                width: 120px;
                height: 120px;
            }
            
            .cover-photo {
                margin-left: -100px;
            }
            
            .package-grid, .posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="main-container">
    <!-- Cover Photo Section -->
    <div class="cover-wrapper">
        <img src="<?php echo $cover_photo; ?>" class="cover-photo" alt="Company Cover Photo">
    </div>

    <!-- Profile Photo Section -->
    <div class="profile-wrapper">
        <div class="profile-container">
            <img src="<?php echo $profile_photo; ?>" class="profile-photo" alt="Company Profile Photo">
        </div>
    </div>
</div>

    <?php
        $reviews_data = [];

        // Get all ratings for this company
        $sql = "SELECT package_id, AVG(rating) AS average_rating, COUNT(*) AS total_reviews 
                FROM company_reviews 
                WHERE company_id = ? 
                GROUP BY package_id";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $reviews_data[$row['package_id']] = [
                'average_rating' => round($row['average_rating'], 1),
                'total_reviews' => $row['total_reviews']
            ];
        }
        ?>

    
    <!-- Company Info -->
    <div class="company-card fade-in" style="animation-delay: 0.2s;">
        <h1><?php echo htmlspecialchars($company['name']); ?></h1>
        
        <div class="company-info">
            <p><strong><i class="fas fa-info-circle"></i> About:</strong> <?php echo nl2br(htmlspecialchars($company['about'])); ?></p>
            <p><strong><i class="fas fa-phone"></i> Contact:</strong> <?php echo htmlspecialchars($company['contact']); ?></p>
            <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($company['email']); ?></p>
            <p><strong><i class="fas fa-tag"></i> Minimum Fee:</strong> PHP <?php echo number_format($company['minimum_fee'], 2); ?></p>
        </div>
    </div>
    
    <hr class="section-divider">
    
  <!-- Packages Section -->
<div class="fade-in" style="animation-delay: 0.4s;">
    <h2><i class="fas fa-box-open"></i> Our Packages</h2>
    <div class="package-grid">

    <?php while ($package = $result_packages->fetch_assoc()): ?>
        <div class="package-card" onclick="togglePackage(this)">
            <div class="package-image-container">
                <?php if (!empty($package['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image" alt="Package Image">
                <?php endif; ?>
            </div>

            <div class="package-content">
                <i class="fas fa-chevron-down expand-icon"></i>
                <h3 class="package-title"><?php echo htmlspecialchars($package['name']); ?></h3>
                <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                <p class="package-price">Price: PHP <?php echo number_format($package['price'], 2); ?></p>

                <div class="mt-20">
                    <a href="../booking/book.php?company_id=<?php echo $company_id; ?>&package_id=<?php echo $package['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </a>
                </div>

                <!-- Rating and Reviews -->
                <div class="review-section mt-20">
                    <?php
                    // Check if this user has already reviewed this package
                    $check_review_sql = "SELECT * FROM company_reviews WHERE company_id = ? AND package_id = ? AND user_id = ?";
                    $stmt_check = $conn->prepare($check_review_sql);
                    $stmt_check->bind_param("iii", $company_id, $package['id'], $_SESSION['user_id']);
                    $stmt_check->execute();
                    $user_review_result = $stmt_check->get_result();
                    $user_review = $user_review_result->fetch_assoc();

                    // Get all reviews
                    $reviews_sql = "SELECT cr.rating, cr.review, cr.created_at, u.name 
                                    FROM company_reviews cr 
                                    JOIN users u ON cr.user_id = u.id 
                                    WHERE cr.company_id = ? AND cr.package_id = ? 
                                    ORDER BY cr.created_at DESC";
                    $stmt_reviews = $conn->prepare($reviews_sql);
                    $stmt_reviews->bind_param("ii", $company_id, $package['id']);
                    $stmt_reviews->execute();
                    $reviews_result = $stmt_reviews->get_result();
                    ?>

                    <div style="margin-top: 15px;">
                        <h4>Reviews:</h4>
                        <?php if ($reviews_result->num_rows > 0): ?>
                            <?php
                            $reviews = [];
                            while ($review = $reviews_result->fetch_assoc()) {
                                $reviews[] = $review;
                            }
                            $review_count = count($reviews);
                            ?>

                            <?php for ($i = 0; $i < min(3, $review_count); $i++): ?>
                                <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                                    <strong><?php echo htmlspecialchars($reviews[$i]['name']); ?></strong>
                                    (<?php echo $reviews[$i]['rating']; ?>/5)<br>
                                    <small><?php echo date("F j, Y", strtotime($reviews[$i]['created_at'])); ?></small><br>
                                    <p><?php echo nl2br(htmlspecialchars($reviews[$i]['review'])); ?></p>
                                </div>
                            <?php endfor; ?>

                            <?php if ($review_count > 3): ?>
                                <button class="btn btn-primary" onclick="toggleReviews(<?php echo $package['id']; ?>)">View More</button>

                                <div id="more-reviews-<?php echo $package['id']; ?>" style="display: none; margin-top: 10px;">
                                    <?php for ($i = 3; $i < $review_count; $i++): ?>
                                        <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                                            <strong><?php echo htmlspecialchars($reviews[$i]['name']); ?></strong>
                                            (<?php echo $reviews[$i]['rating']; ?>/5)<br>
                                            <small><?php echo date("F j, Y", strtotime($reviews[$i]['created_at'])); ?></small><br>
                                            <p><?php echo nl2br(htmlspecialchars($reviews[$i]['review'])); ?></p>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>No reviews yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Display message if any -->
                    <?php if (isset($review_msg)) echo $review_msg; ?>

                    <?php 
                        $pkg_id = $package['id'];
                        $avg = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['average_rating'] : "No ratings yet";
                        $total = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['total_reviews'] : 0;
                    ?>
                    <p><strong>Rating:</strong> <?= is_numeric($avg) ? "$avg ★ ($total reviews)" : $avg ?></p>

                    <!-- Review Form -->
                    <?php if (!$user_review): ?>
                        <form method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
                            <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                            <div class="star-rating">
                                <input type="radio" id="star5-<?php echo $package['id']; ?>" name="rating" value="5"><label for="star5-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star4-<?php echo $package['id']; ?>" name="rating" value="4"><label for="star4-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star3-<?php echo $package['id']; ?>" name="rating" value="3"><label for="star3-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star2-<?php echo $package['id']; ?>" name="rating" value="2"><label for="star2-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star1-<?php echo $package['id']; ?>" name="rating" value="1"><label for="star1-<?php echo $package['id']; ?>">★</label>
                            </div>
                            <br>
                            <textarea name="review" placeholder="Write your review..." required rows="3" style="width:100%; margin-top:5px;"></textarea>
                            <br>
                            <button type="submit" name="submit_review">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <p style="color: #ff9a8b;"><i>You have already reviewed this package.</i></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    </div> <!-- End of .package-grid -->
</div> <!-- End of .fade-in -->

    <hr class="section-divider">
    
    <!-- Company Posts -->
    <div class="fade-in" style="animation-delay: 0.6s;">
        <h2><i class="fas fa-newspaper"></i> Company Updates</h2>
        
        <?php if ($result_posts->num_rows > 0): ?>
            <div class="posts-grid">
                <?php while ($post = $result_posts->fetch_assoc()): ?>
                    <div class="post-card" onclick="togglePost(this)">
                        <div class="post-media-container">
                            <?php if (!empty($post['media_path']) && $post['media_type'] == 'image'): ?>
                                <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" class="post-media" alt="Post Image">
                            <?php elseif (!empty($post['media_path']) && $post['media_type'] == 'video'): ?>
                                <video class="post-media" <?php echo !isset($_POST['expanded_post']) ? 'controls' : ''; ?>>
                                    <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        </div>
                        <div class="post-content">
                            <i class="fas fa-chevron-down expand-icon"></i>
                            <h3 class="post-title">Update on <?php echo date("F j, Y", strtotime($post['created_at'])); ?></h3>
                            <div class="post-preview">
                                <?php echo nl2br(htmlspecialchars(truncateText($post['caption'], 100))); ?>
                            </div>
                            <div class="post-full">
                                <p class="post-caption"><?php echo nl2br(htmlspecialchars($post['caption'])); ?></p>
                                <p class="post-date"><i class="far fa-clock"></i> <?php echo date("F j, Y", strtotime($post['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No posts from this company yet.</p>
        <?php endif; ?>
    </div>
    
    <!-- Back Button -->
    <a href="companies.php" class="back-btn fade-in" style="animation-delay: 0.8s;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<script>
    <?php
function truncateText($text, $limit) {
    // Check if the text length exceeds the limit
    if (strlen($text) > $limit) {
        // Truncate the text and append ellipsis
        $text = substr($text, 0, $limit) . '...';
    }
    return $text;
}
?>

    // Toggle package details
    function togglePackage(card) {
      
        card.classList.toggle('expanded');  // Toggle the 'expanded' class
    
    // This ensures the review section toggles with the package card
    const reviewSection = card.querySelector('.review-section');
    if (card.classList.contains('expanded')) {
        reviewSection.style.display = 'block';  // Show reviews
    } else {
        reviewSection.style.display = 'none';  // Hide reviews
    }
    }
    
    // Toggle post details
    function togglePost(card) {
        event.stopPropagation();
        card.classList.toggle('expanded');
        
        // Toggle video controls when expanded
        const video = card.querySelector('video');
        if (video) {
            if (card.classList.contains('expanded')) {
                video.setAttribute('controls', 'true');
            } else {
                video.removeAttribute('controls');
            }
        }
    }
    
    // Toggle more reviews
    function toggleReviews(packageId) {
        const moreBox = document.getElementById(`more-reviews-${packageId}`);
        if (moreBox.style.display === "none") {
            moreBox.style.display = "block";
        } else {
            moreBox.style.display = "none";
        }
    }
    
    // Add click animation to all cards
    document.querySelectorAll('.package-card, .post-card').forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = this.classList.contains('expanded') ? 'translateY(-10px) scale(1)' : 'translateY(-10px)';
            }, 150);
        });
    });
</script>
</body>
</html>