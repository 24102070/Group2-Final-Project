<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Get the freelancer ID from the URL
$freelancer_id = $_GET['id'];




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

    
</head>

<style>
  body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 20px;
    color: #5A4A42;
    line-height: 1.6;
    background: url('https://images.unsplash.com/photo-1676734628558-624737d3e094?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    overflow-x: hidden;
}

.overlay-container {
    max-width: 1100px;
    margin: auto;
    padding: 20px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

/* Cover & Profile */
.cover-photo {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 10px;
}
.profile-container {
    display: flex;
    justify-content: center;
    margin-top: -60px;
}
.profile-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
}

/* Headings */
.container h2 {
    color: #e88b5f;
    margin: 20px 0 10px;
}

/* --- CARD GRID --- */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 20px;
}

/* --- PACKAGE AND POST CARD STYLES --- */
.package-card, .post {
    border: 1px solid #eee;
    background: #fffdfa;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(240, 197, 168, 0.15);
    transition: all 0.3s ease-in-out;
    transform: scale(1);
    max-height: 320px;
    max-width: 280px;
    position: relative;
    display: flex;
    flex-direction: column;
}

/* Hover effect for the cards */
.package-card:hover, .post:hover {
    transform: scale(1.05);
    max-height: none;
    z-index: 10;
}

/* --- CARD IMAGE --- */
.package-image, .post-media img, .post-media video {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
}

/* --- CARD CONTENT --- */
.package-title, .post-caption {
    font-size: 1rem;
    font-weight: 600;
    color: #d17250;
    padding: 10px 15px 0;
}

/* Package and Post Details */
.package-details, .package-price {
    font-size: 0.9rem;
    padding: 0 15px 10px;
}

.package-price {
    font-weight: 600;
    color: #2d6a4f;
}

/* --- RATING INFO --- */
.package-rating {
    font-size: 0.8rem;
    color: #6c757d;
    padding: 5px 15px;
}

/* --- BUTTON --- */
.btn-book {
    display: inline-block;
    padding: 8px 16px;
    margin: 15px;
    font-size: 0.9rem;
    background-color: #f28c63;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 6px;
    transition: background 0.3s;
}

.btn-book:hover {
    background-color: #d8754e;
}

/* --- RESPONSIVE STYLES --- */
@media (max-width: 768px) {
    /* Adjust grid layout */
    .card-grid {
        padding: 10px;
    }

    /* Adjust card sizes and padding */
    .package-card, .post {
        max-height: none;
        padding: 10px;
    }

    /* Adjust image size for mobile */
    .package-image {
        height: 140px;
    }

    /* Adjust text size for mobile */
    .package-title, .post-caption {
        font-size: 1rem;
    }

    .package-price {
        font-size: 1rem;
    }
}
.back-btn {
            display: inline-block;
    margin-top: 2rem;
    text-decoration: none;
    color: var(--primary);
    font-weight: 500;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Poppins', sans-serif;
    background: white;
    padding: 0.8rem 1.5rem;
    border-radius: var(--radius);
    border: 2px solid var(--primary);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
     display: block;
            text-align: center;
            margin: 30px auto;
            color: #E67B7B;
            text-decoration: none;
            font-size: 1.1em;
        }

        .back-btn:hover {
            text-decoration: underline;
        }
</style>
<body>

     <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
    <!-- Cover Photo -->
    <div>
        <img src="<?php echo $cover_photo; ?>" class="cover-photo">
    </div>

    <!-- Profile Photo -->
    <div class="profile-container">
        <img src="<?php echo $profile_photo; ?>" class="profile-photo">
    </div>

    <!-- Freelancer Details -->
    <div class="container">
        <h2>Freelancer Profile</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($freelancer['name']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($freelancer['description'])); ?></p>
        <p><strong>Profession:</strong> <?php echo htmlspecialchars($freelancer['profession']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($freelancer['contact']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($freelancer['email']); ?></p>
        <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($freelancer['minimum_fee'], 2); ?></p>
        <!--For Messaging Feature (not final)-->
        <a href="../messaging/messaging.php"><i class="fas fa-comment"></i>Contact Now</a>
    </div>

 

    <!-- Packages List -->
     
    <div class="container">
        
        <h2>Available Packages</h2>
        <div class="card-grid">
        <?php if ($result_packages->num_rows > 0): ?>
            <?php while ($package = $result_packages->fetch_assoc()): ?>
                <div class="package-card">
                    <?php if (!empty($package['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image" alt="Package Image">
                    <?php else: ?>
                        <img src="../assets/default-package.jpg" class="package-image" alt="Default Package Image">
                    <?php endif; ?>
                    <div class="package-title"><?php echo htmlspecialchars($package['name']); ?></div>
                    <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                    <p><strong>Inclusions:</strong><br><?php echo nl2br(htmlspecialchars($package['inclusions'])); ?></p>
                    <p class="package-price">Price: PHP <?php echo number_format($package['price'], 2); ?></p>
                  
                

                     <!-- Show average rating and number of reviews -->
                <?php 
                    $pkg_id = $package['id'];
                    $avg = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['average_rating'] : "No ratings yet";
                    $total = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['total_reviews'] : 0;
                ?>
                <p><strong>Rating:</strong> <?= is_numeric($avg) ? "$avg ★ ($total reviews)" : $avg ?></p>

                

                <?php
// Check if user already reviewed this package
$user_review = false;
$check_user_review = $conn->prepare("SELECT id FROM freelancers_review_ratings WHERE freelancer_id = ? AND package_id = ? AND user_id = ?");
$check_user_review->bind_param("iii", $freelancer_id, $package['id'], $_SESSION['user_id']);
$check_user_review->execute();
$check_user_review->store_result();
$user_review = $check_user_review->num_rows > 0;

// Fetch all reviews for this package
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

<div style="margin-top: 15px;">
    <h4>Reviews:</h4>
    <?php if ($review_count > 0): ?>
        <?php for ($i = 0; $i < min(3, $review_count); $i++): ?>
            <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                <strong><?= htmlspecialchars($reviews[$i]['name']) ?></strong>
                (<?= $reviews[$i]['rating'] ?>/5)<br>
                <small><?= date("F j, Y", strtotime($reviews[$i]['created_at'])) ?></small><br>
                <p><?= nl2br(htmlspecialchars($reviews[$i]['review'])) ?></p>
            </div>
        <?php endfor; ?>

        <?php if ($review_count > 3): ?>
            <button class="review-btn" onclick="toggleReviews(<?= $package['id'] ?>)">View More</button>
            <div id="more-reviews-<?= $package['id'] ?>" style="display: none; margin-top: 10px;">
                <?php for ($i = 3; $i < $review_count; $i++): ?>
                    <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                        <strong><?= htmlspecialchars($reviews[$i]['name']) ?></strong>
                        (<?= $reviews[$i]['rating'] ?>/5)<br>
                        <small><?= date("F j, Y", strtotime($reviews[$i]['created_at'])) ?></small><br>
                        <p><?= nl2br(htmlspecialchars($reviews[$i]['review'])) ?></p>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>
</div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No packages available.</p>
        <?php endif; ?>
        </div>
    </div>


       <!-- Freelancer Posts -->
      
        <h2>Freelancer Posts</h2>
        <div class="card-grid">
        <?php if ($result_posts->num_rows > 0): ?>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <div class="post">
                    
                    <?php if ($post['media_type'] == 'image' && !empty($post['media_path'])): ?>
                        <div class="post-media">
                            <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" alt="Post Image">
                        </div>
                    <?php elseif ($post['media_type'] == 'video' && !empty($post['media_path'])): ?>
                        <div class="post-media">
                            <video controls>
                                <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    <?php endif; ?>
                    <div class="post-caption"><?php echo nl2br(htmlspecialchars($post['caption'])); ?></div>
                    <p><small>Posted on: <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts available.</p>
        <?php endif; ?>
    </div>
      
    <a href="browse_freelancers.php" class="back-btn"> <i class="fas fa-arrow-left"></i>Back</a>
        </div>

    <script>
    function toggleReviews(packageId) {
        var moreReviews = document.getElementById("more-reviews-" + packageId);
        if (moreReviews.style.display === "none") {
            moreReviews.style.display = "block";
        } else {
            moreReviews.style.display = "none";
        }
    }
</script>

</body>
</html>
