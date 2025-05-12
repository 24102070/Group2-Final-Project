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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
</head>
<body>

<div class="container">
    <h1 style="font-family: 'Playfair Display', serif; font-weight: 90; color: black; margin-bottom: 0;">
        Welcome,
    </h1>
    <h2 style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 40px; font-weight: 110; color: salmon; margin-top: -10px;">
        <?php echo htmlspecialchars($company['name']); ?>
    </h2>

    <p>Manage your bookings and update your profile.</p>

    <div class="nav-links">
        <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i>Manage Bookings</a>
        <a href="manage_schedule.php"><i class="fas fa-clock"></i>Manage Schedule</a>
        <a href="update_profile.php"><i class="fa-solid fa-user-pen"></i> Update Profile</a>
        <a href="add_package.php"><i class="fa-solid fa-box-open"></i>Add Package</a>
        <a href="create_post.php"><i class="fa-solid fa-pen-to-square"></i>Create Post</a>
        <a href="browse_freelancers.php"><i class="fa-solid fa-right-from-bracket"></i>Connect and Browse</a>
        <a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
    </div>
</div>

<hr>

<!-- Cover Photo -->
<div class="container">
    <img src="<?php echo $cover_photo; ?>" class="cover-photo">
</div>

<!-- Profile Section -->
<div class="container">
    <div class="profile-section">
        <img src="<?php echo $profile_photo; ?>" class="profile-photo">
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($company['name']); ?></h2>
            <p><?php echo htmlspecialchars($company['contact']); ?></p>
            <p><strong>Minimum Fee:</strong> PHP <?php echo number_format($company['minimum_fee'], 2); ?></p>
        </div>
    </div>

    <div class="profile-details">
        <p style="max-width: 80%; text-align: justify; margin: 0 auto;"><?php echo htmlspecialchars($company['about']); ?></p>
    </div>
</div>

<!-- Packages Section -->
<div class="container">
    <h2 style="margin-top: 60px; font-family: 'Playfair Display', serif; color: #E67B7B;">Your Packages</h2>
    
    <div class="packages-container">
        <?php if ($result_packages->num_rows == 0): ?>
            <div class="no-content">
                <i class="fas fa-box-open"></i>
                <p>No packages available yet. Create your first package to get started!</p>
                <a href="add_package.php"><i class="fas fa-plus"></i> Add Package</a>
            </div>
        <?php else: ?>
            <?php while ($package = $result_packages->fetch_assoc()): ?>
                <div class="package-card" onclick="openPackageModal(
                    '<?php echo addslashes(htmlspecialchars($package['name'])); ?>',
                    '<?php echo addslashes(nl2br(htmlspecialchars($package['details']))); ?>',
                    '<?php echo number_format($package['price'], 2); ?>',
                    '<?php echo !empty($package['image']) ? '../' . htmlspecialchars($package['image']) : ''; ?>'
                )">
                    <?php if (!empty($package['image'])): ?>
                        <div class="package-image-container">
                            <img src="../<?php echo htmlspecialchars($package['image']); ?>" class="package-image">
                        </div>
                    <?php endif; ?>
                    <div class="package-content">
                        <h3 class="package-title"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p class="package-details"><?php echo nl2br(htmlspecialchars($package['details'])); ?></p>
                        <p class="package-price">PHP <?php echo number_format($package['price'], 2); ?></p>
                        <div class="package-actions">
                            <a href="edit_package.php?id=<?php echo $package['id']; ?>" class="btn-edit" onclick="event.stopPropagation()">Edit</a>
                            <a href="delete_package.php?id=<?php echo $package['id']; ?>" class="btn-delete" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this package?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Posts Section -->
<div class="container">
    <h2 style="margin-top: 60px; font-family: 'Playfair Display', serif; color: #E67B7B;">Your Posts</h2>
    
    <div class="post-grid">
        <?php if ($result_posts->num_rows == 0): ?>
            <div class="no-content">
                <i class="fas fa-newspaper"></i>
                <p>No posts available yet. Create your first post to engage with your audience!</p>
                <a href="create_post.php"><i class="fas fa-plus"></i> Create Post</a>
            </div>
        <?php else: ?>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <div class="post-card" onclick="openPostModal(
                    '<?php echo addslashes(nl2br(htmlspecialchars($post['caption']))); ?>',
                    '<?php echo htmlspecialchars($post['created_at']); ?>',
                    '<?php echo $post['media_type']; ?>',
                    '<?php echo !empty($post['media_path']) ? '../' . htmlspecialchars($post['media_path']) : ''; ?>'
                )">
                    <?php if ($post['media_type'] == 'image' && !empty($post['media_path'])): ?>
                        <div class="post-media-container">
                            <img src="../<?php echo htmlspecialchars($post['media_path']); ?>" class="post-media">
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
                        <p class="post-date">Posted on: <?php echo htmlspecialchars($post['created_at']); ?></p>
                        <div class="package-actions">
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn-edit" onclick="event.stopPropagation()">Edit</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn-delete" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this post?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Package Modal -->
<div id="packageModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="packageModalImageContainer"></div>
        <h2 id="packageModalTitle" class="modal-title"></h2>
        <p id="packageModalPrice" class="package-price"></p>
        <p id="packageModalDetails" class="modal-details"></p>
    </div>
</div>

<!-- Post Modal -->
<div id="postModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="postModalMediaContainer"></div>
        <p id="postModalDate" class="post-date"></p>
        <p id="postModalCaption" class="modal-details"></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Add any necessary JavaScript here
});

function openPackageModal(name, details, price, image) {
    const modal = document.getElementById('packageModal');
    document.getElementById('packageModalTitle').textContent = name;
    document.getElementById('packageModalDetails').innerHTML = details;
    document.getElementById('packageModalPrice').textContent = 'PHP ' + price;
    
    const imageContainer = document.getElementById('packageModalImageContainer');
    imageContainer.innerHTML = '';
    if (image) {
        const img = document.createElement('img');
        img.src = image;
        img.className = 'modal-image';
        imageContainer.appendChild(img);
    }
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function openPostModal(caption, date, mediaType, mediaPath) {
    const modal = document.getElementById('postModal');
    document.getElementById('postModalCaption').innerHTML = caption;
    document.getElementById('postModalDate').textContent = 'Posted on: ' + date;
    
    const mediaContainer = document.getElementById('postModalMediaContainer');
    mediaContainer.innerHTML = '';
    if (mediaPath) {
        if (mediaType === 'image') {
            const img = document.createElement('img');
            img.src = mediaPath;
            img.className = 'modal-image';
            mediaContainer.appendChild(img);
        } else if (mediaType === 'video') {
            const video = document.createElement('video');
            video.controls = true;
            video.className = 'modal-image';
            const source = document.createElement('source');
            source.src = mediaPath;
            source.type = 'video/mp4';
            video.appendChild(source);
            mediaContainer.appendChild(video);
        }
    }
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('packageModal').style.display = 'none';
    document.getElementById('postModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const packageModal = document.getElementById('packageModal');
    const postModal = document.getElementById('postModal');
    
    if (event.target == packageModal) {
        packageModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    if (event.target == postModal) {
        postModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
</script>

</body>
</html>