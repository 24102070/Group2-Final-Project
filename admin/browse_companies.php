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
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        .container {
            width: 80%;
            margin: auto;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-bar input,
        .search-bar select,
        .search-bar button {
            padding: 10px;
            font-size: 16px;
        }
        .company-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .company-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-actions {
            text-align: right;
        }
        .company-card button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-left: 10px;
        }
        .company-card button:hover {
            background-color: #1e7e34;
        }
        .company-card h3 {
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Browse Companies</h2>

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

        <button onclick="filterByStatus()">Filter</button>
    </div>

    <div id="companyList">
        <?php foreach ($companies as $company): ?>
            <div class="company-card" 
                data-name="<?php echo strtolower($company['name']); ?>" 
                data-status="<?php echo strtolower($company['status']); ?>">
                
                <?php
                    $profile_photo = !empty($company['profile_photo']) 
                        ? "../" . $company['profile_photo'] 
                        : "../assets/default-profile.png";
                ?>
                <img src="<?php echo htmlspecialchars($profile_photo); ?>" 
                     alt="Profile Photo" 
                     class="company-photo">

                <div class="company-info">
                    <h3><?php echo htmlspecialchars($company['name']); ?></h3>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($company['status']); ?></p>
                    <p><strong>Minimum Fee:</strong> ₱<?php echo htmlspecialchars(number_format($company['minimum_fee'], 2)); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($company['description']); ?></p>
                </div>

                <div class="company-actions">
                    <button onclick="window.location.href='browseCompany_profile.php?id=<?php echo $company['id']; ?>'">View Profile</button>
                    <button onclick="connectWithCompany(<?php echo $company['id']; ?>)">Connect</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="freelancer_dashboard.php"><button>Back to Dashboard</button></a>
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
            card.style.display = matches ? 'flex' : 'none';
        });
    });

    // 🎯 Filter by status button
    function filterByStatus() {
        const selectedStatus = statusSelect.value.toLowerCase();

        companyCards.forEach(card => {
            const status = card.dataset.status;
            card.style.display = (selectedStatus === '' || status === selectedStatus) ? 'flex' : 'none';
        });

        // Clear search input so it doesn’t conflict with filter
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
