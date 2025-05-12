<?php
session_start();
include '../config/db.php';

$companies_only = ($_SESSION['role'] == 'company') ? true : false;

if (!$companies_only) {
    header("Location: ../login.php");
    exit();
}

// Fetch freelancers
$sql = "
    SELECT f.id, f.name, f.profession, f.description, f.status,
           fp.profile_photo
    FROM freelancers f
    LEFT JOIN freelancer_profiles fp ON f.id = fp.freelancer_id
    WHERE f.approval = 'Approved'
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$freelancers = $result->fetch_all(MYSQLI_ASSOC);

// Collect professions for filter dropdown
$professions = array_unique(array_map(fn($f) => $f['profession'], $freelancers));
sort($professions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Talent | LoveStory Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/browse_freelancers.css">

</head>
<body>
<div class="container">
<header>
    <div class="heading-box">
        <h1>Find Wedding Professionals</h1>
    </div>
    <div class="subheading-box">
        <p>Discover talented vendors to make your special day perfect</p>
    </div>
</header>

        <section class="search-section">
            <div class="search-bar">
                <input type="text" id="searchInput" class="search-input" placeholder="Search by name, profession...">
                
                <select id="professionFilter" class="filter-select">
                    <option value="">All Wedding Services</option>
                    <?php foreach ($professions as $profession): ?>
                        <option value="<?php echo htmlspecialchars($profession); ?>">
                            <?php echo htmlspecialchars($profession); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-primary" onclick="filterByProfession()">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </section>

        <div class="freelancers-grid" id="freelancerList">
            <?php if (empty($freelancers)): ?>
                <div class="empty-state">
                    <i class="fas fa-heart-broken"></i>
                    <h3>No professionals available</h3>
                    <p>We couldn't find any wedding professionals matching your criteria</p>
                </div>
            <?php else: ?>
                <?php foreach ($freelancers as $freelancer): ?>
                    <div class="freelancer-card" 
                         data-name="<?php echo strtolower($freelancer['name']); ?>" 
                         data-profession="<?php echo strtolower($freelancer['profession']); ?>">
                        <div class="profile-photo-container">
                            <?php
                                $profile_photo = !empty($freelancer['profile_photo']) 
                                    ? "../" . $freelancer['profile_photo'] 
                                    : "https://ui-avatars.com/api/?name=" . urlencode($freelancer['name']) . "&background=e4816c&color=fff&size=200";
                            ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" 
                                 alt="<?php echo htmlspecialchars($freelancer['name']); ?>" 
                                 class="profile-photo">
                        </div>
                        
                        <div class="card-body">
                            <h3 class="freelancer-name"><?php echo htmlspecialchars($freelancer['name']); ?></h3>
                            <span class="freelancer-profession"><?php echo htmlspecialchars($freelancer['profession']); ?></span>
                            <p class="freelancer-description"><?php echo htmlspecialchars($freelancer['description']); ?></p>
                            
                            <span class="freelancer-status status-<?php echo strtolower($freelancer['status']) === 'available' ? 'available' : 'busy'; ?>">
                                <i class="fas fa-<?php echo strtolower($freelancer['status']) === 'available' ? 'check-circle' : 'pause-circle'; ?>"></i> 
                                <?php echo htmlspecialchars($freelancer['status']); ?>
                            </span>

                            <div class="card-actions">
                                <button class="btn btn-secondary" 
                                        onclick="window.location.href='browseFreelance_profile.php?id=<?php echo $freelancer['id']; ?>'">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-primary" 
                                        onclick="connectWithFreelancer(<?php echo $freelancer['id']; ?>)">
                                    <i class="fas fa-heart"></i> Connect
                                </button>
                            </div>
                        </div>
                    </div>
                    
                <?php endforeach; ?>
            <?php endif; ?>
        </div>


        <a href="dashboard.php" class="back-to-dashboard">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

    <script>
        const searchInput = document.getElementById('searchInput');
        const professionSelect = document.getElementById('professionFilter');
        const freelancerCards = document.querySelectorAll('.freelancer-card');

        // 🔍 Live search (name or profession)
        searchInput.addEventListener('input', () => {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasResults = false;

            freelancerCards.forEach(card => {
                const name = card.dataset.name;
                const profession = card.dataset.profession;
                const matches = name.includes(keyword) || profession.includes(keyword);
                card.style.display = matches ? 'block' : 'none';
                
                if (matches) hasResults = true;
            });

            showEmptyState(!hasResults);
        });

        // 🎯 Filter by profession button
        function filterByProfession() {
            const selectedProfession = professionSelect.value.toLowerCase();
            let hasResults = false;

            freelancerCards.forEach(card => {
                const profession = card.dataset.profession;
                const shouldShow = selectedProfession === '' || profession === selectedProfession;
                card.style.display = shouldShow ? 'block' : 'none';
                
                if (shouldShow) hasResults = true;
            });

            showEmptyState(!hasResults);
            searchInput.value = ''; // Clear search input
        }

        function showEmptyState(show) {
            const emptyState = document.querySelector('.empty-state');
            if (!emptyState) return;
            
            emptyState.style.display = show ? 'block' : 'none';
        }

        function connectWithFreelancer(freelancerId) {
            if (confirm("Would you like to connect with this wedding professional?")) {
                // In a real app, you would make an AJAX call here
                alert(`Connection request sent to professional #${freelancerId}`);
                
                // Visual feedback
                const connectBtn = document.querySelector(`button[onclick="connectWithFreelancer(${freelancerId})"]`);
                if (connectBtn) {
                    connectBtn.innerHTML = '<i class="fas fa-check"></i> Request Sent';
                    connectBtn.style.backgroundColor = '#8bc34a';
                    connectBtn.disabled = true;
                }
            }
        }

        function filterByProfession() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const professionFilter = document.getElementById('professionFilter').value.toLowerCase();
    const cards = document.querySelectorAll('.freelancer-card');

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const profession = card.getAttribute('data-profession');
        const matchesSearch = name.includes(searchInput) || profession.includes(searchInput);
        const matchesProfession = professionFilter === '' || profession === professionFilter;

        if (matchesSearch && matchesProfession) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

    </script>
</body>
</html>