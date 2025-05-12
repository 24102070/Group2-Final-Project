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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/dashStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .container a {
    text-decoration: none;
    background-color: rgba(230, 123, 123, 0.9);
    color: white;
    padding: 10px 15px;
    border-radius: 30px;
    display: inline-block;
    transition: all 0.3s ease;
    font-weight: 300;
    margin: 5px;
    font-family: 'Poppins', serif;
    letter-spacing: 0.5px;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    box-shadow: 0 2px 8px rgba(230, 123, 123, 0.2);
    font-size: 0.9rem;
}

.container a:hover {
    background-color: rgba(212, 106, 106, 0.9);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(230, 123, 123, 0.3);
}

.container a i {
    margin-right: 8px;
}

.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin: 20px;
    align-items: start;
}


/* Packages and Posts Grid */
.packages-grid, .posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    padding: 20px 0;
}
.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.package-card {
    background-color: #fff;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    font-size: 0.9rem; /* smaller text */
    line-height: 1.3;
}

.package-card h3 {
    font-size: 1rem;
    margin: 8px 0;
}

.package-card p {
    font-size: 0.85rem;
    margin: 4px 0;
}

.package-image {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 8px;
}

.review-container h4 {
    font-size: 0.95rem;
    margin: 10px 0 5px;
}

.reviewer-name {
    font-size: 0.8rem;
    font-weight: 500;
}

.review-card p {
    font-size: 0.8rem;
    margin-bottom: 6px;
}

.btn-view, .btn-view-more {
    font-size: 0.8rem;
    padding: 4px 8px;
    margin-top: 6px;
    display: inline-block;
}

.packages-section .packages-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}


.btn-view:hover, .btn-view-more:hover {
    background-color: rgba(212, 106, 106, 0.9);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(230, 123, 123, 0.3);
    color: white;
}

/* No Content Messages */
.no-content {
    text-align: center;
    color: #7A6A65;
    font-size: 1.1rem;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.7);
    border-radius: 12px;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    grid-column: 1 / -1;
}

.no-content i {
    font-size: 2rem;
    color: #E67B7B;
    margin-bottom: 15px;
    display: block;
}

/* Decorative elements */
.peach-blob {
    position: fixed;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,183,161,0.3) 0%, rgba(255,183,161,0) 70%);
    border-radius: 50%;
    z-index: -1;
    filter: blur(20px);
}

.peach-blob-1 {
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
}

.peach-blob-2 {
    bottom: -150px;
    left: -150px;
    width: 500px;
    height: 500px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        width: 95%;
        padding: 20px;
    }
    
    h1 {
        font-size: 2em;
    }
    
    .profile-section {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-photo {
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .packages-grid, .posts-grid {
        grid-template-columns: 1fr;
    }
    
    .container a {
        display: block;
        width: 100%;
        text-align: center;
        margin: 5px 0;
    }
}

.post-caption-container {
margin: 15px 0;
}

.post-caption-short, .post-caption-full {
margin-bottom: 10px;
word-wrap: break-word;
}

.btn-toggle-caption {
background: none;
border: none;
color: #E67B7B;
cursor: pointer;
padding: 5px 0;
font-size: 0.9em;
text-decoration: underline;
}

.btn-toggle-caption:hover {
color: #d46a6a;

}


    </style>
</head>
<body>

 <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

   <div class="container">
    <h1 style="font-family: 'Playfair Display', serif; font-weight: 90; color: black; margin-bottom: 0;">
        Welcome,
    </h1>
    <h2 style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 40px; font-weight: 110; color: salmon; margin-top: -10px;">
        <?php echo htmlspecialchars($freelancer['name']); ?>
    </h2>

    <p>Manage your bookings and update your profile.</p>

        <div class="dashboard-links">
            <a href="manage_freelancer_bookings.php"><i class="fas fa-calendar-check"></i>Manage Bookings</a>
            <a href="manage_freelancer_schedules.php"><i class="fas fa-clock"></i>Manage Schedules</a>
            <a href="update_freelancer_profile.php"><i class="fa-solid fa-user-pen"></i> Update Profile</a>
            <a href="add_package_freelancer.php"><i class="fa-solid fa-box-open"></i>Add Package</a>
            <a href="create_freelancer_post.php"><i class="fa-solid fa-pen-to-square"></i>Create Post</a>
            <a href="browse_companies.php"><i class="fa-solid fa-right-from-bracket"></i>Connect and Browse</a>
            <a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
        </div>

<hr>

<!-- Cover Photo -->
<div>
    <img src="<?php echo $cover_photo; ?>" class="cover-photo">
</div>

<!-- Profile Photo -->
<div class="profile-section">
    <img src="<?php echo $profile_photo; ?>" class="profile-photo">
    <div class="profile-details">
    <h2><?php echo htmlspecialchars($freelancer['name']); ?></h2>
    <p><?php echo htmlspecialchars($freelancer['contact']); ?></p>
    <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($freelancer['minimum_fee'], 2); ?></p>
    </div>
</div>

<!-- Freelancer Details -->
<div class="profile-details">
   
    <p style = "max-width: 50%; text-align: justify; margin-left: 12%;"><?php echo nl2br(htmlspecialchars($freelancer['about'])); ?></p>
    </div>
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
