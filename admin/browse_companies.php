<?php
session_start();
include '../config/db.php';

$freelancers_only = ($_SESSION['role'] == 'freelancer') ? true : false;

if (!$freelancers_only) {
    header("Location: ../login.php");
    exit();
}

// Fetch approved companies
$sql = "
    SELECT c.id, c.name, c.status, c.description, c.minimum_fee, cp.profile_photo
    FROM companies c
    LEFT JOIN company_profiles cp ON c.id = cp.company_id
    WHERE c.approval = 'Approved'
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$companies = $result->fetch_all(MYSQLI_ASSOC);

// Get status options for filtering
$statuses = array_unique(array_map(fn($c) => $c['status'], $companies));
sort($statuses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Companies</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/browse_companies.css">
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h1>Browse Companies</h1>

        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search company or status...">
                
                <select id="statusFilter">
                    <option value="">-- Filter by Status --</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-filter" onclick="filterByStatus()">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <div class="company-list" id="companyList">
            <?php foreach ($companies as $company): ?>
                <div class="company-card" 
                    data-name="<?php echo strtolower($company['name']); ?>" 
                    data-status="<?php echo strtolower($company['status']); ?>">
                    
                    <div class="company-header">
                        <?php
                            $profile_photo = !empty($company['profile_photo']) 
                                ? "../" . htmlspecialchars($company['profile_photo']) 
                                : "../assets/default-profile.png";
                        ?>
                        <img src="<?php echo $profile_photo; ?>" 
                             alt="Profile Photo" 
                             class="company-photo">

                        <div>
                            <h3 class="company-name"><?php echo htmlspecialchars($company['name']); ?></h3>
                            <span class="company-status"><?php echo htmlspecialchars($company['status']); ?></span>
                        </div>
                    </div>

                    <div class="company-details">
                        <p><strong>Minimum Fee:</strong> ₱<?php echo htmlspecialchars(number_format($company['minimum_fee'], 2)); ?></p>
                        <p><?php echo htmlspecialchars($company['description']); ?></p>
                    </div>

                    <div class="company-actions">
                        <button class="btn btn-view" onclick="window.location.href='browseCompany_profile.php?id=<?php echo $company['id']; ?>'">
                            <i class="fas fa-eye"></i> View Profile
                        </button>
                        <button class="btn btn-connect" onclick= "window.location.href = '../messaging/messaging.php';">
                            <i class="fas fa-handshake"></i> Connect
                        </button>
                    </div>

                     
                </div>
            <?php endforeach; ?>
        </div>

        <a href="freelancer_dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusFilter');
        const companyCards = document.querySelectorAll('.company-card');

        // 🔍 Live search
        searchInput.addEventListener('input', () => {
            const keyword = searchInput.value.toLowerCase().trim();

            companyCards.forEach(card => {
                const name = card.dataset.name;
                const status = card.dataset.status;
                const matches = name.includes(keyword) || status.includes(keyword);
                card.style.display = matches ? 'block' : 'none';
            });
        });

        // 🎯 Filter by status button
        function filterByStatus() {
            const selectedStatus = statusSelect.value.toLowerCase();

            companyCards.forEach(card => {
                const status = card.dataset.status;
                card.style.display = (selectedStatus === '' || status === selectedStatus) ? 'block' : 'none';
            });

            // Clear search input so it doesn't conflict with filter
            searchInput.value = '';
        }

        function connectWithCompany(companyId) {
            if (confirm("Do you want to connect with this company?")) {
                alert("Connection request sent to company ID: " + companyId);
            }
        }
    </script>

</body>
</html>