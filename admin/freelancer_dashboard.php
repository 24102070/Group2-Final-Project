<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$freelancer_id = $_SESSION['user_id'];

// Fetch reviews for the freelancer's packages (with user name)
$sql_reviews = "
    SELECT r.package_id, r.rating, r.review, u.name AS reviewer_name 
    FROM freelancers_review_ratings r
    JOIN users u ON r.user_id = u.id
    WHERE r.freelancer_id = ?
";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $freelancer_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();

$reviews_data = [];
while ($review = $result_reviews->fetch_assoc()) {
    $reviews_data[$review['package_id']][] = $review;
}

// Fetch freelancer and profile details
$sql = "SELECT f.name, f.minimum_fee, 
               p.profile_photo, p.cover_photo, p.about, p.contact 
        FROM freelancers f 
        LEFT JOIN freelancer_profiles p ON f.id = p.freelancer_id 
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();

// Fetch packages
$sql_packages = "SELECT id, name, details, price, image FROM packages_freelancers WHERE freelancer_id = ?";
$stmt_packages = $conn->prepare($sql_packages);
$stmt_packages->bind_param("i", $freelancer_id);
$stmt_packages->execute();
$result_packages = $stmt_packages->get_result();

// Fetch freelancer posts
$sql_posts = "SELECT id, caption, media_path, media_type FROM freelancer_posts WHERE freelancer_id = ? ORDER BY id DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $freelancer_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

// Default images
$profile_photo = !empty($freelancer['profile_photo']) ? "../" . $freelancer['profile_photo'] : "../assets/default-profile.png";
$cover_photo = !empty($freelancer['cover_photo']) ? "../" . $freelancer['cover_photo'] : "../assets/default-cover.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        .cover-photo {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
        }

        .profile-container {
            display: flex;
            justify-content: center;
            margin-top: -50px;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .package-card {
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
        }

        .package-title {
            font-size: 24px;
            font-weight: bold;
        }

        .package-details, .package-price {
            font-size: 16px;
        }

        .package-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .btn-view {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            margin-right: 10px;
        }

        .btn-view:hover {
            text-decoration: underline;
        }

        .review-container {
    margin-top: 15px;
}

.review-card {
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-left: 5px solid #007bff;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease;
}

.review-card:hover {
    transform: scale(1.01);
}

.reviewer-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 5px;
    color: #333;
}

.review-rating {
    color: #ffaa00;
    font-weight: bold;
    margin-bottom: 8px;
}

.review-text {
    font-size: 14px;
    color: #555;
    white-space: pre-wrap;
}

.btn-view-more {
    background-color: transparent;
    border: none;
    color: #007bff;
    font-size: 14px;
    cursor: pointer;
    text-decoration: underline;
    margin-top: 5px;
    padding: 0;
}
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($freelancer['name']); ?>!</h1>
    <p>Manage your gigs and update your profile.</p>

    <a href="manage_freelancer_bookings.php">Manage Bookings</a> |
    <a href="manage_freelancer_schedules.php">Manage Schedules</a> |
    <a href="update_freelancer_profile.php">Update Profile</a> |
    <a href="add_package_freelancer.php">Add Package</a> |
    <a href="create_freelancer_post.php">Create Post</a> |
    <a href="browse_companies.php">Connect and Browse</a> |
    <a href="../messaging/messaging.php">Messages</a> |

    <a href="../auth/logout.php">Logout</a>
    
</div>

<hr>

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
    <p><strong>About Me:</strong> <?php echo nl2br(htmlspecialchars($freelancer['about'])); ?></p>
    <p><strong>Contact:</strong> <?php echo htmlspecialchars($freelancer['contact']); ?></p>
    <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($freelancer['minimum_fee'], 2); ?></p>
    
</div>

<!-- Packages -->
<div class="container">
    <h2>My Packages</h2>
    <?php if ($result_packages->num_rows > 0): ?>
        <?php while ($package = $result_packages->fetch_assoc()): ?>
            <div class="package-card" style="background:#fff; padding:20px; margin-bottom:20px; border-radius:10px;">
                <img src="../<?php echo $package['image'] ?: 'assets/default-package.png'; ?>" class="package-image" style="width:100%; max-height:200px; object-fit:cover; border-radius:8px;">
                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                <p><strong>Price:</strong> PHP <?php echo number_format($package['price'], 2); ?></p>
                <a href="view_freelance_packages.php?id=<?php echo $package['id']; ?>" class="btn-view">View Details</a>

                <!-- Reviews -->
                <div class="review-container">
                    <h4>Reviews</h4>
                    <?php
                    $reviews = $reviews_data[$package['id']] ?? [];
                    if (count($reviews) > 0):
                        foreach (array_slice($reviews, 0, 3) as $review): ?>
                            <div class="review-card">
                                <div class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?> - Rating: <?php echo $review['rating']; ?>/5</div>
                                <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($reviews) > 3): ?>
                            <div id="more-reviews-<?php echo $package['id']; ?>" style="display:none;">
                                <?php foreach (array_slice($reviews, 3) as $review): ?>
                                    <div class="review-card">
                                        <div class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?> - Rating: <?php echo $review['rating']; ?>/5</div>
                                        <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="btn-view-more" onclick="toggleReviews(<?php echo $package['id']; ?>)">View More</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No packages found.</p>
    <?php endif; ?>
</div>

<!-- Freelancer Posts -->
<div class="container">
    <h2>My Posts</h2>
    <?php if ($result_posts->num_rows > 0): ?>
        <?php while ($post = $result_posts->fetch_assoc()): ?>
            <div class="package-card">
                

                <?php if (!empty($post['media_path'])): ?>
                    <?php if ($post['media_type'] == 'image'): ?>
                        <img src="../<?php echo $post['media_path']; ?>" class="package-image" alt="Post Image">
                    <?php elseif ($post['media_type'] == 'video'): ?>
                        <video controls class="package-image">
                            <source src="../<?php echo $post['media_path']; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                <?php endif; ?>
                <p> <?php echo nl2br(htmlspecialchars($post['caption'])); ?></p>
                
                <br>
                <a href="edit_freelancer_post.php?id=<?php echo $post['id']; ?>" class="btn-view">Edit</a>
                <a href="delete_freelancer_post.php?id=<?php echo $post['id']; ?>" class="btn-view" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts yet. <a href="create_freelancer_post.php">Create your first post</a>.</p>
    <?php endif; ?>
</div>

<script>
function toggleReviews(packageId) {
    const container = document.getElementById('more-reviews-' + packageId);
    const btn = event.target;

    if (container.style.display === 'none') {
        container.style.display = 'block';
        btn.textContent = "View Less";
    } else {
        container.style.display = 'none';
        btn.textContent = "View More";
    }
}
</script>

</body>
</html>
