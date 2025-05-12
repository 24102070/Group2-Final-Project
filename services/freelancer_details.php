<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Get the freelancer ID from the URL
$freelancer_id = $_GET['id'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $package_id = $_POST['package_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    // Prevent duplicate reviews
    $check_sql = "SELECT id FROM freelancers_review_ratings WHERE freelancer_id = ? AND package_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("iii", $freelancer_id, $package_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $review_msg = "<p style='color:red;'>You have already reviewed this package.</p>";
    } else {
        $insert_sql = "INSERT INTO freelancers_review_ratings (freelancer_id, package_id, user_id, rating, review) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiiss", $freelancer_id, $package_id, $user_id, $rating, $review);
        if ($stmt->execute()) {
            $review_msg = "<p style='color:green;'>Review submitted successfully!</p>";
        } else {
            $review_msg = "<p style='color:red;'>Something went wrong. Try again.</p>";
        }
    }
}


// Fetch freelancer and profile details, including email
$sql = "SELECT f.name, f.email, f.profession, f.description, f.minimum_fee, 
               p.profile_photo, p.cover_photo, p.about, p.contact 
        FROM freelancers f
        LEFT JOIN freelancer_profiles p ON f.id = p.freelancer_id 
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();

if (!$freelancer) {
    echo "Freelancer not found!";
    exit();
}

// Fetch packages from the correct table: packages_freelancers
$sql_packages = "SELECT id, name, details, inclusions, price, image FROM packages_freelancers WHERE freelancer_id = ?";
$stmt_packages = $conn->prepare($sql_packages);
$stmt_packages->bind_param("i", $freelancer_id);
$stmt_packages->execute();
$result_packages = $stmt_packages->get_result();

// Fetch ratings and reviews for each package
$sql_reviews = "SELECT r.package_id, AVG(r.rating) as average_rating, COUNT(r.id) as total_reviews
                FROM freelancers_review_ratings r
                GROUP BY r.package_id";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();

$reviews_data = [];
while ($row = $result_reviews->fetch_assoc()) {
    $reviews_data[$row['package_id']] = [
        'average_rating' => round($row['average_rating'], 1),
        'total_reviews' => $row['total_reviews']
    ];
}

// Default images if no profile photo exists
$profile_photo = !empty($freelancer['profile_photo']) ? "../" . $freelancer['profile_photo'] : "../assets/default-profile.png";
$cover_photo = !empty($freelancer['cover_photo']) ? "../" . $freelancer['cover_photo'] : "../assets/default-cover.png";

// Fetch freelancer posts
$sql_posts = "SELECT * FROM freelancer_posts WHERE freelancer_id = ? ORDER BY created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $freelancer_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($freelancer['name']); ?> - Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/freelancer_details.css">
    
<style>
     body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 20px;
    color:rgb(36, 35, 35) !important;
    line-height: 1.6;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://images.unsplash.com/photo-1676734628558-624737d3e094?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed;
    background-size: cover;
    opacity: 0.3; /* Adjust opacity of the image */
    z-index: -1; /* Ensure the image is behind the content */
}

body {
    min-height: 100vh;
}

.con {
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.1); /* Black background with 0.2 opacity */
    border-radius: 35px;
}

.prof-con {
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.6); 
    border-radius: 35px;
    padding: 15px;
}
</style>

</head>
<body>

<div class="con">
<div class="container">
    <h1 style="font-family: 'Playfair Display', serif; font-weight: 800; font-size: 36px; color: #E67B7B;">
        <?php echo htmlspecialchars($freelancer['name']); ?>
    </h1>
    <p><?php echo htmlspecialchars($freelancer['profession']); ?></p>
</div>

<!-- Cover Photo -->
<div class="container">
    <img src="<?php echo $cover_photo; ?>" class="cover-photo">
</div>

<!-- Profile Section -->
<div class="container">
    
    <div class="profile-section">
        <img src="<?php echo $profile_photo; ?>" class="profile-photo">
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($freelancer['name']); ?></h2>
            <p><?php echo htmlspecialchars($freelancer['contact']); ?></p>
            <p><?php echo htmlspecialchars($freelancer['email']); ?></p>
            <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($freelancer['minimum_fee'], 2); ?></p>
        </div>
    </div>
<div class = "prof-con">

    <div class="profile-details">
        <p><?php echo nl2br(htmlspecialchars($freelancer['about'])); ?></p>
    </div>
</div>
</div>

<!-- Packages Section -->
<div class="container">
    <h2 style="font-family: 'Playfair Display', serif; color: #E67B7B;">Available Packages</h2>
    
    <div class="search-container">
        <input type="text" id="searchBar" onkeyup="filterPackages()" placeholder="Search for packages...">
    </div>
    
    <div class="packages-container">
        <?php if ($result_packages->num_rows > 0): ?>
            <?php while ($package = $result_packages->fetch_assoc()): ?>
                <div class="package-card">
                    <?php if (!empty($package['image'])): ?>
                        <div class="package-image-container">
                            <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image" alt="<?php echo htmlspecialchars($package['name']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="package-image-container">
                            <img src="../assets/default-package.jpg" class="package-image" alt="Default Package Image">
                        </div>
                    <?php endif; ?>
                    
                    <div class="package-content">
                        <h3 class="package-title"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                        <p><strong>Inclusions:</strong><br><?php echo nl2br(htmlspecialchars($package['inclusions'])); ?></p>
                        <p class="package-price">PHP <?php echo number_format($package['price'], 2); ?></p>
                        
                        <?php 
                            $pkg_id = $package['id'];
                            $avg = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['average_rating'] : "No ratings yet";
                            $total = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['total_reviews'] : 0;
                        ?>
                        <p><strong>Rating:</strong> <?= is_numeric($avg) ? "$avg ★ ($total reviews)" : $avg ?></p>
                        
                        <div class="package-actions">
                            <a href="../booking/freelancer_book.php?freelancer_id=<?php echo $freelancer_id; ?>&package_id=<?php echo $package['id']; ?>" class="btn-book">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </a>
                        </div>
                        
                        <?php
                            $user_id = $_SESSION['user_id'];
                            $check_sql = "SELECT id FROM freelancers_review_ratings WHERE user_id = ? AND freelancer_id = ? AND package_id = ?";
                            $stmt_check = $conn->prepare($check_sql);
                            $stmt_check->bind_param("iii", $user_id, $freelancer_id, $pkg_id);
                            $stmt_check->execute();
                            $result_check = $stmt_check->get_result();
                            $already_reviewed = $result_check->num_rows > 0;
                        ?>
                        
                        <div class="review-container">
                            <?php if (!$already_reviewed): ?>
                                <h4>Leave a Review</h4>
                                <form action="freelancer_details.php?id=<?php echo $freelancer_id; ?>" method="POST">
                                    <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                    
                                    <div class="star-rating">
                                        <input type="radio" id="star5-<?php echo $package['id']; ?>" name="rating" value="5"><label for="star5-<?php echo $package['id']; ?>">★</label>
                                        <input type="radio" id="star4-<?php echo $package['id']; ?>" name="rating" value="4"><label for="star4-<?php echo $package['id']; ?>">★</label>
                                        <input type="radio" id="star3-<?php echo $package['id']; ?>" name="rating" value="3"><label for="star3-<?php echo $package['id']; ?>">★</label>
                                        <input type="radio" id="star2-<?php echo $package['id']; ?>" name="rating" value="2"><label for="star2-<?php echo $package['id']; ?>">★</label>
                                        <input type="radio" id="star1-<?php echo $package['id']; ?>" name="rating" value="1"><label for="star1-<?php echo $package['id']; ?>">★</label>
                                    </div>
                                    
                                    <textarea name="review" placeholder="Write your review here" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; min-height: 80px;"></textarea><br>
                                    <button type="submit" name="submit_review" class="btn-book" style="margin-top: 10px;">
                                        <i class="fas fa-paper-plane"></i> Submit Review
                                    </button>
                                </form>
                            <?php else: ?>
                                <p><em>You have already reviewed this package.</em></p>
                            <?php endif; ?>
                            
                            <?php
                                $reviews_sql = "SELECT frr.rating, frr.review, frr.created_at, u.name 
                                                FROM freelancers_review_ratings frr 
                                                JOIN users u ON frr.user_id = u.id 
                                                WHERE frr.freelancer_id = ? AND frr.package_id = ? 
                                                ORDER BY frr.created_at DESC";
                                $stmt_reviews = $conn->prepare($reviews_sql);
                                $stmt_reviews->bind_param("ii", $freelancer_id, $package['id']);
                                $stmt_reviews->execute();
                                $reviews_result = $stmt_reviews->get_result();
                                
                                $reviews = [];
                                while ($row = $reviews_result->fetch_assoc()) {
                                    $reviews[] = $row;
                                }
                                $review_count = count($reviews);
                            ?>
                            
                            <h4 style="margin-top: 20px;">Reviews</h4>
                            <?php if ($review_count > 0): ?>
                                <?php for ($i = 0; $i < min(3, $review_count); $i++): ?>
                                    <div class="review-card">
                                        <div class="reviewer-name"><?= htmlspecialchars($reviews[$i]['name']) ?> (<?= $reviews[$i]['rating'] ?>/5)</div>
                                        <p><?= nl2br(htmlspecialchars($reviews[$i]['review'])) ?></p>
                                        <small><?= date("F j, Y", strtotime($reviews[$i]['created_at'])) ?></small>
                                    </div>
                                <?php endfor; ?>
                                
                                <?php if ($review_count > 3): ?>
                                    <button class="btn-book" onclick="toggleReviews(<?= $package['id'] ?>)" style="margin-top: 10px;">
                                        <i class="fas fa-chevron-down"></i> View More
                                    </button>
                                    <div id="more-reviews-<?= $package['id'] ?>" style="display: none; margin-top: 10px;">
                                        <?php for ($i = 3; $i < $review_count; $i++): ?>
                                            <div class="review-card">
                                                <div class="reviewer-name"><?= htmlspecialchars($reviews[$i]['name']) ?> (<?= $reviews[$i]['rating'] ?>/5)</div>
                                                <p><?= nl2br(htmlspecialchars($reviews[$i]['review'])) ?></p>
                                                <small><?= date("F j, Y", strtotime($reviews[$i]['created_at'])) ?></small>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>No reviews yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-content">
                <i class="fas fa-box-open"></i>
                <p>No packages available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Posts Section -->
<div class="container">
    <h2 style="font-family: 'Playfair Display', serif; color: #E67B7B;">Recent Posts</h2>
    
    <div class="post-grid">
        <?php if ($result_posts->num_rows > 0): ?>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <div class="post-card">
                    <?php if ($post['media_type'] == 'image' && !empty($post['media_path'])): ?>
                        <div class="post-media-container">
                            <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" class="post-media" alt="Post Image">
                        </div>
                    <?php elseif ($post['media_type'] == 'video' && !empty($post['media_path'])): ?>
                        <div class="post-media-container">
                            <video controls class="post-media">
                                <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                            </video>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <div class="post-caption"><?php echo nl2br(htmlspecialchars($post['caption'])); ?></div>
                        <p class="post-date">Posted on: <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-content">
                <i class="fas fa-newspaper"></i>
                <p>No posts available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="text-align: center;">
    <a href="freelancers.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Freelancers
    </a>
</div>
</div>
<script>
    function toggleReviews(packageId) {
        var moreReviews = document.getElementById("more-reviews-" + packageId);
        var button = event.target;
        
        if (moreReviews.style.display === "none") {
            moreReviews.style.display = "block";
            button.innerHTML = '<i class="fas fa-chevron-up"></i> View Less';
        } else {
            moreReviews.style.display = "none";
            button.innerHTML = '<i class="fas fa-chevron-down"></i> View More';
        }
    }

    function filterPackages() {
        let searchInput = document.getElementById('searchBar').value.toLowerCase();
        let packageCards = document.querySelectorAll('.package-card');
        
        packageCards.forEach(function(packageCard) {
            let packageName = packageCard.querySelector('.package-title').textContent.toLowerCase();
            let packageDetails = packageCard.querySelector('.package-details').textContent.toLowerCase();
            
            if (packageName.includes(searchInput) || packageDetails.includes(searchInput)) {
                packageCard.style.display = '';
            } else {
                packageCard.style.display = 'none';
            }
        });
    }
</script>

</body>
</html>