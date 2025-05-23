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
    <style>
  
#sidebar {
    background-color: rgba(230, 123, 123, 0.1);
    color: #E67B7B !important;
    padding: 20px;
    border-radius: 35px;
    min-height: 80%;
    color: white;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border: none;
}


#sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}


#sidebar .sidebar-btn {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 10px;
    background: none;
    border: none;
    color: white;
    font-weight: 600;
    text-align: left;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    transition: background 0.3s ease, transform 0.2s;
    cursor: pointer;
}


#sidebar .sidebar-btn.active {
    background-color: rgba(255, 255, 255, 0.2);
}


#sidebar .sidebar-btn:hover {
    background-color: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
    color: white;
}

@media screen and (max-width: 768px) {
    #sidebar {
        flex: 0 0 60px !important; /* smaller width */
        padding: 10px 5px;
    }

    #sidebar .sidebar-btn {
        text-align: center;
        padding: 10px 0;
        font-size: 16px;
    }

    #sidebar .sidebar-btn i {
        display: block;
        font-size: 20px;
    }

    #sidebar .sidebar-btn::after,
    #sidebar .sidebar-btn span,
    #sidebar .sidebar-btn .text {
        display: none !important; /* hide text */
    }
}


        </style>
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
         <a href="../messaging/messaging.php"><i class="fas fa-comment"></i>Messages</a>
        <a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
    </div>
</div>

<hr>
<div class="container" id="sidebar-content-wrapper" style="display: flex; gap: 20px; margin-top: 30px;">
    
   <nav id="sidebar" style="flex: 0 0 200px; border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
    <div style="text-align: center; margin-bottom: 20px;">
    <img src="<?php echo $profile_photo; ?>" alt="Company Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #E67B7B;">
</div>
<p style="color: #E67B7B; font-size: 14px; margin-top: 10px; text-align: center;"><i class="fa-regular fa-calendar"></i>
    <?php echo date("l, F j"); ?>
</p>
<hr style="border-top: 1px solid #E67B7B; margin: 20px 0;">
<p style="text-align: center; color: #E67B7B; font-size: 12px;">Quick Access</p>

        <ul style="list-style: none; padding: 0; margin: 0;">
            <li><button class="sidebar-btn active" data-target="profile-section" style="width: 100%; padding: 10px; border: none; background: none; cursor: pointer; font-weight: 600; font-size: 20px; text-align: left; color: #E67B7B "> <i class="fa-solid fa-user"></i> <span class="text">Profile</button></li>
            <li><button class="sidebar-btn" data-target="package-section" style="width: 100%; padding: 10px; border: none; background: none; cursor: pointer; font-weight: 600; font-size: 20px; text-align: left; color: #E67B7B "><i class="fa-solid fa-box-open"></i><span class="text"> Package</button></li>
            <li><button class="sidebar-btn" data-target="post-section" style="width: 100%; padding: 10px; border: none; background: none; cursor: pointer; font-weight: 600; font-size: 20px;text-align: left; color: #E67B7B "> <i class="fa-solid fa-pen-to-square"></i><span class="text">Post</button></li>
        </ul>
<br>
<br>
        <hr style="border-top: 1px solid #E67B7B; margin: 20px 0;">
        <blockquote style="font-style: italic; color: #E67B7B; font-size: 12px; margin: 20px 0;">
    “Creativity is intelligence having fun.” – Albert Einstein
</blockquote>

    </nav>

  <!-- START: Content Area -->
    <div id="content-area" style="flex: 1; padding: 15px; border-radius: 5px; max-width: 100%; overflow-x: auto;">

        <!-- START: Profile Content -->
        <div id="profile-section" class="sidebar-content" style="display: block;">

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
</div>
<!-- Packages Section -->
 <div id="package-section" class="sidebar-content" style="display: none;">
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
 <div id="post-section" class="sidebar-content" style="display: none;">
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

document.querySelectorAll('.sidebar-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.sidebar-btn').forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        button.classList.add('active');

        // Hide all content divs
        document.querySelectorAll('.sidebar-content').forEach(content => content.style.display = 'none');

        // Show the targeted content div
        const target = button.getAttribute('data-target');
        const targetDiv = document.getElementById(target);
        if (targetDiv) {
            targetDiv.style.display = 'block';
        }
    });
});
</script>

<button id="scrollToggleBtn" title="Scroll" style="position: fixed; bottom: 30px; right: 30px; background-color: #E67B7B; color: white; border: none; border-radius: 50%; padding: 15px; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); font-size: 18px; z-index: 999;">
    <i class="fas fa-arrow-down"></i>
</button>
<script>
    const scrollBtn = document.getElementById('scrollToggleBtn');
    const targetHr = document.querySelector('hr'); // first <hr> element
    let isAtBottom = false;

    scrollBtn.addEventListener('click', () => {
        if (!isAtBottom) {
            // Scroll down to <hr>
            targetHr.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            // Scroll up to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Toggle state after a short delay to allow scroll to complete
        setTimeout(() => {
            isAtBottom = !isAtBottom;
            scrollBtn.innerHTML = isAtBottom
                ? '<i class="fa-solid fa-arrow-up"></i>'
                : '<i class="fa-solid fa-arrow-down"></i>';
        }, 500);
    });
</script>


</body>
</html> 
</script>
</body>
</html>
