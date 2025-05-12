<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';



$company_id = $_GET['id'];






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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/browse_Company_profile.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <!-- Cover Photo -->
        <img src="<?php echo $cover_photo; ?>" class="cover-photo">

        <!-- Profile Photo -->
        <div class="profile-container">
            <img src="<?php echo $profile_photo; ?>" class="profile-photo">
        </div>

        <h1><?php echo htmlspecialchars($company['name']); ?></h1>

        <!-- Company Details -->
        <div class="section">
            <h2>Company Profile</h2>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($company['about'])); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($company['contact']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($company['email']); ?></p>
            <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($company['minimum_fee'], 2); ?></p>
            <a href="../messages/new_message.php?company_id=<?php echo $company_id; ?>" class="btn btn-book">Contact Now</a>
        </div>

        <!-- Packages -->
        <div class="section">
            <h2>Available Packages</h2>
            <div class="package-grid">
                <?php while ($package = $result_packages->fetch_assoc()): ?>
                    <div class="package-card">
                        <?php if (!empty($package['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image" alt="Package Image">
                        <?php endif; ?>
                        <div class="package-title"><?php echo htmlspecialchars($package['name']); ?></div>
                        <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                        <p class="package-price">Price: PHP <?php echo number_format($package['price'], 2); ?></p>
                        <a href="../booking/book.php?company_id=<?php echo $company_id; ?>&package_id=<?php echo $package['id']; ?>" class="btn btn-book">Book Now</a>

                        <!-- Rating and Reviews -->
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
                                    <div class="review">
                                        <strong><?php echo htmlspecialchars($reviews[$i]['name']); ?></strong>
                                        <div class="rating"><?php echo str_repeat('★', $reviews[$i]['rating']) . str_repeat('☆', 5 - $reviews[$i]['rating']); ?></div>
                                        <small><?php echo date("F j, Y", strtotime($reviews[$i]['created_at'])); ?></small><br>
                                        <p><?php echo nl2br(htmlspecialchars($reviews[$i]['review'])); ?></p>
                                    </div>
                                <?php endfor; ?>

                                <?php if ($review_count > 3): ?>
                                    <button class="btn review-btn" onclick="toggleReviews(<?php echo $package['id']; ?>)">View More</button>
                                    <div id="more-reviews-<?php echo $package['id']; ?>" style="display: none; margin-top: 10px;">
                                        <?php for ($i = 3; $i < $review_count; $i++): ?>
                                            <div class="review">
                                                <strong><?php echo htmlspecialchars($reviews[$i]['name']); ?></strong>
                                                <div class="rating"><?php echo str_repeat('★', $reviews[$i]['rating']) . str_repeat('☆', 5 - $reviews[$i]['rating']); ?></div>
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
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Company Posts -->
        <div class="section">
            <h2>Company Posts</h2>
            <div class="package-grid">
                <?php if ($result_posts->num_rows > 0): ?>
                    <?php while ($post = $result_posts->fetch_assoc()): ?>
                        <div class="package-card">
                            <?php if (!empty($post['media_path']) && $post['media_type'] == 'image'): ?>
                                <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" class="package-image" alt="Post Image">
                            <?php elseif (!empty($post['media_path']) && $post['media_type'] == 'video'): ?>
                                <video class="package-image" controls>
                                    <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                            <p class="package-details"><?php echo nl2br(htmlspecialchars($post['caption'])); ?></p>
                            <p class="package-price">Posted on: <?php echo date("F j, Y", strtotime($post['created_at'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No posts from this company yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <a href="browse_companies.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Companies</a>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const packageCards = document.querySelectorAll('.package-card');
        
        packageCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't toggle if clicking on a link inside the card
                if (e.target.tagName === 'A' || e.target.closest('a')) {
                    return;
                }
                
                // Toggle the expanded class
                this.classList.toggle('expanded');
                
                // Close other expanded cards if needed
                if (this.classList.contains('expanded')) {
                    packageCards.forEach(otherCard => {
                        if (otherCard !== this && otherCard.classList.contains('expanded')) {
                            otherCard.classList.remove('expanded');
                        }
                    });
                }
            });
        });
    });

    function toggleReviews(packageId) {
        const moreBox = document.getElementById(`more-reviews-${packageId}`);
        const btn = moreBox.previousElementSibling;
        
        if (moreBox.style.display === "none") {
            moreBox.style.display = "block";
            btn.textContent = "Show Less";
        } else {
            moreBox.style.display = "none";
            btn.textContent = "View More";
        }
        
        // Stop event propagation to prevent card toggle
        event.stopPropagation();
    }
    </script>

</body>
</html>