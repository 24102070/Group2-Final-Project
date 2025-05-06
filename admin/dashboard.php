<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$company_id = $_SESSION['user_id'];

// Fetch company and profile details
$sql = "SELECT c.name, c.minimum_fee, 
               p.profile_photo, p.cover_photo, p.about, p.contact 
        FROM companies c 
        LEFT JOIN company_profiles p ON c.id = p.company_id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

// Fetch packages for the company
$sql_packages = "SELECT id, name, details, price, image FROM packages WHERE company_id = ?";
$stmt_packages = $conn->prepare($sql_packages);
$stmt_packages->bind_param("i", $company_id);
$stmt_packages->execute();
$result_packages = $stmt_packages->get_result();

// Fetch posts for the company
$sql_posts = "SELECT id, caption, media_path, media_type, created_at FROM company_posts WHERE company_id = ? ORDER BY created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $company_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

// Default images if no profile photo exists
$profile_photo = !empty($company['profile_photo']) ? "../" . $company['profile_photo'] : "../assets/default-profile.png";
$cover_photo = !empty($company['cover_photo']) ? "../" . $company['cover_photo'] : "../assets/default-cover.png";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/dashStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   



    </style>
</head>
<body>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($company['name']); ?>!</h1>
        <p>Manage your bookings and update your profile.</p>

        <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i>Manage Bookings</a> |
        <a href="manage_schedule.php"><i class="fas fa-clock"></i>Manage Schedule</a> |
        <a href="update_profile.php"><i class="fa-solid fa-user-pen"></i> Update Profile</a> |
        <a href="add_package.php"><i class="fa-solid fa-box-open"></i>Add Package</a> |
        <a href="create_post.php"><i class="fa-solid fa-pen-to-square"></i>Create Post</a> |
        <a href="browse_freelancers.php"><i class="fa-solid fa-right-from-bracket"></i>Connect and Browse</a> |
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
    <h2><?php echo htmlspecialchars($company['name']); ?></h2>
    <p><?php echo htmlspecialchars($company['contact']); ?></p>
        <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($company['minimum_fee'], 2); ?></p>
    </div>
</div>

    <div class="profile-details">
       
        <p style = "max-width: 50%; text-align: justify; margin-left: 12%;" ><?php echo htmlspecialchars($company['about']); ?></p>
        
    </div>

    <!-- Posts List -->
    <div class="container">
        <h2>Your Posts</h2>
        <?php while ($post = $result_posts->fetch_assoc()): ?>
            <div class="post-card">
                <?php if ($post['media_type'] == 'image' && !empty($post['media_path'])): ?>
                    <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" class = "post-media">

                <?php elseif ($post['media_type'] == 'video' && !empty($post['media_path'])): ?>
                    <video controls class="post-media">
                        <source src="../<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
                <div class="post-caption"><?php echo nl2br(htmlspecialchars($post['caption'])); ?></div>
                <p class="post-date">Posted on: <?php echo $post['created_at']; ?></p>

                <!-- Edit and Delete Buttons -->
                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn-edit" style = "color: white;">Edit</a>
                <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn-delete"  style = "color: white;">Delete</a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Packages List -->
<div class="container">
    <h2>Our Packages</h2>
    <?php
    // Fetch reviews with user names
    $sql_reviews = "
        SELECT r.package_id, r.rating, r.review, r.created_at, u.name AS user_name 
        FROM company_reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.company_id = ?
        ORDER BY r.created_at DESC";
    $stmt_reviews = $conn->prepare($sql_reviews);
    $stmt_reviews->bind_param("i", $company_id);
    $stmt_reviews->execute();
    $result_reviews = $stmt_reviews->get_result();

    $reviews_data = [];
    while ($row = $result_reviews->fetch_assoc()) {
        $reviews_data[$row['package_id']][] = $row;
    }

    // Rewind package result set
    $stmt_packages->execute();
    $result_packages = $stmt_packages->get_result();

    while ($package = $result_packages->fetch_assoc()):
        $pid = $package['id'];
        $package_reviews = $reviews_data[$pid] ?? [];
    ?>
        <div class="package-card">
            <?php if (!empty($package['image'])): ?>
                <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image" alt="Package Image">
            <?php else: ?>
                <img src="../assets/default-package.png" class="package-image" alt="Default Package Image">
            <?php endif; ?>

            <div class="package-title"><?php echo htmlspecialchars($package['name']); ?></div>
            <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
            <p class="package-price">Price: PHP <?php echo number_format($package['price'], 2); ?></p>
            <a href="view_packages.php?id=<?php echo $package['id']; ?>" class="btn-view">View Details</a>

            <!-- Reviews Section -->
            <div class="review-section">
                <h4>Reviews:</h4>
                <?php if (count($package_reviews) > 0): ?>
                    <?php foreach ($package_reviews as $index => $review): ?>
                        <div class="review-box" style="<?php echo $index > 2 ? 'display: none;' : ''; ?>" data-package-id="<?php echo $pid; ?>">
                            <strong><?php echo htmlspecialchars($review['user_name']); ?></strong> —
                            <span>Rating: <?php echo $review['rating']; ?>/5</span><br>
                            <em><?php echo nl2br(htmlspecialchars($review['review'])); ?></em>
                            <p style="font-size: 12px; color: gray;">Posted on: <?php echo $review['created_at']; ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($package_reviews) > 3): ?>
                        <a href="javascript:void(0);" class="btn-view" onclick="toggleReviews(<?php echo $pid; ?>)">View More Reviews</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
function toggleReviews(packageId) {
    const allReviews = document.querySelectorAll('.review-box[data-package-id="' + packageId + '"]');
    allReviews.forEach((review, index) => {
        if (index >= 3) {
            review.style.display = review.style.display === 'none' ? 'block' : 'none';
        }
    });

    const btn = event.target;
    btn.textContent = btn.textContent === "View More Reviews" ? "View Less Reviews" : "View More Reviews";
}
</script>
</body>
</html>
