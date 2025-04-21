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
            padding: 15px;
            border-radius: 8px;
            width: 250px;
            height: 200px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .package-card:hover {
            height: auto;
            width: 100%;
            padding: 20px;
            z-index: 2;
        }

        .package-title,
        .package-price {
            font-size: 18px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .package-details,
        .package-price,
        .package-card p {
            opacity: 0;
            transition: opacity 0.3s ease;
            height: 0;
            overflow: hidden;
        }

        .package-card:hover .package-details,
        .package-card:hover .package-price,
        .package-card:hover p {
            opacity: 1;
            height: auto;
            margin-top: 10px;
        }

        .package-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .btn-book {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-book:hover {
            background-color: #0056b3;
        }

        .review-section {
            margin-top: 20px;
        }

        .review-section h3 {
            font-size: 24px;
        }

        .review-container {
            margin-top: 10px;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .review {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .rating {
            font-size: 18px;
            color: #ffcc00;
        }

        .star-rating {
            direction: rtl;
            font-size: 30px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #ddd;
            cursor: pointer;
        }

        .star-rating input:checked ~ label {
            color: #ffcc00;
        }

        .star-rating input:hover ~ label {
            color: #ffcc00;
        }

        .review-btn {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .review-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Welcome!</h1>
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

    <!-- Company Details -->
    <div class="container">
        <h2>Company Profile</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($company['name']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($company['about'])); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($company['contact']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($company['email']); ?></p>
        <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($company['minimum_fee'], 2); ?></p>
        <!--For Messaging Feature (not final)-->
        <a href="../booking/freelancer_book.php?freelancer_id=<?php echo $freelancer_id; ?>&package_id=<?php echo $package['id']; ?>" class="btn-book">Contact Now</a>
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

    <!-- Packages -->
    <div class="container">
        <h2>Available Packages</h2>
        <?php while ($package = $result_packages->fetch_assoc()): ?>
            <div class="package-card">
    <?php if (!empty($package['image'])): ?>
        <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image" alt="Package Image">
    <?php endif; ?>
    <div class="package-title"><?php echo htmlspecialchars($package['name']); ?></div>
    <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
    <p class="package-price">Price: PHP <?php echo number_format($package['price'], 2); ?></p>
    <a href="../booking/book.php?company_id=<?php echo $company_id; ?>&package_id=<?php echo $package['id']; ?>" class="btn-book">Book Now</a>

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

<?php if ($review_count > 0): ?>
    <?php for ($i = 0; $i < min(3, $review_count); $i++): ?>
        <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
            <strong><?php echo htmlspecialchars($reviews[$i]['name']); ?></strong>
            (<?php echo $reviews[$i]['rating']; ?>/5)<br>
            <small><?php echo date("F j, Y", strtotime($reviews[$i]['created_at'])); ?></small><br>
            <p><?php echo nl2br(htmlspecialchars($reviews[$i]['review'])); ?></p>
        </div>
    <?php endfor; ?>

    <?php if ($review_count > 3): ?>
        <button class="review-btn" onclick="toggleReviews(<?php echo $package['id']; ?>)">View More</button>

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

    
</div>

        <?php endwhile; ?>
    </div>

    <!-- Company Posts -->
    <div class="container">
        <h2>Company Posts</h2>
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

    <a href="browse_companies.php" class="back-btn">Back</a>


    <script>
function toggleReviews(packageId) {
    const moreBox = document.getElementById(`more-reviews-${packageId}`);
    if (moreBox.style.display === "none") {
        moreBox.style.display = "block";
    } else {
        moreBox.style.display = "none";
    }
}
</script>

</body>
</html>
