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
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        /* Post Section */
        .post-container {
            margin-top: 20px;
        }

        .post {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .post-caption {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .post-media {
            margin-top: 10px;
        }

        .post-media img, .post-media video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .post p small {
            font-size: 12px;
            color: #777;
        }

        /* Existing Styles */
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

        .package-image {
            width: 100%;
            max-height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .back-btn {
            display: inline-block;
            margin: 20px;
            text-decoration: none;
            color: #333;
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

        /* Search Bar Styling */
.search-container {
    margin: 20px 0;
    text-align: center;
}

#searchBar {
    width: 50%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

    <!-- Freelancer Details -->
    <div class="container">
        <h2>Freelancer Profile</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($freelancer['name']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($freelancer['description'])); ?></p>
        <p><strong>Profession:</strong> <?php echo htmlspecialchars($freelancer['profession']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($freelancer['contact']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($freelancer['email']); ?></p>
        <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($freelancer['minimum_fee'], 2); ?></p>
    </div>

 

    <!-- Packages List -->
    <div class="container">
        <h2>Available Packages</h2>
        <div class="search-container">
    <input type="text" id="searchBar" onkeyup="filterPackages()" placeholder="Search for a package...">
</div>
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
                    <a href="../booking/freelancer_book.php?freelancer_id=<?php echo $freelancer_id; ?>&package_id=<?php echo $package['id']; ?>" class="btn-book">Book Now</a>
                

                     <!-- Show average rating and number of reviews -->
                <?php 
                    $pkg_id = $package['id'];
                    $avg = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['average_rating'] : "No ratings yet";
                    $total = isset($reviews_data[$pkg_id]) ? $reviews_data[$pkg_id]['total_reviews'] : 0;
                ?>
                <p><strong>Rating:</strong> <?= is_numeric($avg) ? "$avg ★ ($total reviews)" : $avg ?></p>

                <?php
                        $user_id = $_SESSION['user_id'];

                        // Check if the user already left a review for this package
                        $sql_check_review = "SELECT id FROM freelancers_review_ratings WHERE user_id = ? AND freelancer_id = ? AND package_id = ?";
                        $stmt_check = $conn->prepare($sql_check_review);
                        $stmt_check->bind_param("iii", $user_id, $freelancer_id, $pkg_id);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();
                        $already_reviewed = $result_check->num_rows > 0;
                        ?>

                    <div class="review-section">

                    <?php if (!$already_reviewed): ?>
                        <h3>Leave a Review</h3>
                        <form action="freelancer_details.php?id=<?php echo $freelancer_id; ?>" method="POST">
                            <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                            
                            <!-- Star Rating -->
                            <div class="star-rating">
                                <input type="radio" id="star5-<?php echo $package['id']; ?>" name="rating" value="5"><label for="star5-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star4-<?php echo $package['id']; ?>" name="rating" value="4"><label for="star4-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star3-<?php echo $package['id']; ?>" name="rating" value="3"><label for="star3-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star2-<?php echo $package['id']; ?>" name="rating" value="2"><label for="star2-<?php echo $package['id']; ?>">★</label>
                                <input type="radio" id="star1-<?php echo $package['id']; ?>" name="rating" value="1"><label for="star1-<?php echo $package['id']; ?>">★</label>
                            </div>
                            
                            <textarea name="review" placeholder="Write your review here" required></textarea><br>
                            <button type="submit" name="submit_review" class="review-btn">Submit Review</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <p><em>You have already left a review for this package.</em></p>
                <?php endif; ?>

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


       <!-- Freelancer Posts -->
       <div class="container post-container">
        <h2>Freelancer Posts</h2>
        <?php if ($result_posts->num_rows > 0): ?>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <div class="post">
                    <div class="post-caption"><?php echo nl2br(htmlspecialchars($post['caption'])); ?></div>
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
                    <p><small>Posted on: <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts available.</p>
        <?php endif; ?>
    </div>
    <a href="freelancers.php" class="back-btn">Back to Dashboard</a>

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

<script>
    function filterPackages() {
        // Get the search input value and convert it to lowercase for case-insensitive search
        let searchInput = document.getElementById('searchBar').value.toLowerCase();
        
        // Get all package cards
        let packageCards = document.querySelectorAll('.package-card');
        
        // Loop through all package cards and hide those that don't match the search input
        packageCards.forEach(function(packageCard) {
            // Get the package name and details, convert to lowercase for case-insensitive comparison
            let packageName = packageCard.querySelector('.package-title').textContent.toLowerCase();
            let packageDetails = packageCard.querySelector('.package-details').textContent.toLowerCase();
            
            // Check if the search input matches any part of the package name or details
            if (packageName.includes(searchInput) || packageDetails.includes(searchInput)) {
                packageCard.style.display = ''; // Show package
            } else {
                packageCard.style.display = 'none'; // Hide package
            }
        });
    }
</script>

</body>
</html>
