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
    <title>Browse Freelancers</title>
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
        .freelancer-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .freelancer-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 20px;
        }
        .freelancer-info {
            flex: 1;
        }
        .freelancer-actions {
            text-align: right;
        }
        .freelancer-card button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-left: 10px;
        }
        .freelancer-card button:hover {
            background-color: #0056b3;
        }
        .freelancer-card h3 {
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Browse Freelancers</h2>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search name or profession...">
        
        <select id="professionFilter">
            <option value="">-- Filter by Profession --</option>
            <?php foreach ($professions as $profession): ?>
                <option value="<?php echo htmlspecialchars($profession); ?>">
                    <?php echo htmlspecialchars($profession); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button onclick="filterByProfession()">Filter</button>
    </div>

    <div id="freelancerList">
        <?php foreach ($freelancers as $freelancer): ?>
            <div class="freelancer-card" data-name="<?php echo strtolower($freelancer['name']); ?>" data-profession="<?php echo strtolower($freelancer['profession']); ?>">
                <?php
                    $profile_photo = !empty($freelancer['profile_photo']) 
                        ? "../" . $freelancer['profile_photo'] 
                        : "../assets/default-profile.png";
                ?>
                <img src="<?php echo htmlspecialchars($profile_photo); ?>" 
                     alt="Profile Photo" 
                     class="freelancer-photo">

                <div class="freelancer-info">
                    <h3><?php echo htmlspecialchars($freelancer['name']); ?></h3>
                    <p><strong>Profession:</strong> <?php echo htmlspecialchars($freelancer['profession']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($freelancer['description']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($freelancer['status']); ?></p>
                </div>

                <div class="freelancer-actions">
                    <button onclick="window.location.href='browseFreelance_profile.php?id=<?php echo $freelancer['id']; ?>'">View Profile</button>
                    <button onclick="connectWithFreelancer(<?php echo $freelancer['id']; ?>)">Connect</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<a href="dashboard.php"><button>Back to Dashboard</button></a>

<script>
    const searchInput = document.getElementById('searchInput');
    const professionSelect = document.getElementById('professionFilter');
    const freelancerCards = document.querySelectorAll('.freelancer-card');

    // 🔍 Live search (name or profession)
    searchInput.addEventListener('input', () => {
        const keyword = searchInput.value.toLowerCase().trim();

        freelancerCards.forEach(card => {
            const name = card.dataset.name;
            const profession = card.dataset.profession;
            const matches = name.includes(keyword) || profession.includes(keyword);
            card.style.display = matches ? 'flex' : 'none';
        });
    });

    // 🎯 Filter by profession button
    function filterByProfession() {
        const selectedProfession = professionSelect.value.toLowerCase();

        freelancerCards.forEach(card => {
            const profession = card.dataset.profession;
            card.style.display = (selectedProfession === '' || profession === selectedProfession) ? 'flex' : 'none';
        });

        // Clear search input so it doesn’t conflict with filter
        searchInput.value = '';
    }

    function connectWithFreelancer(freelancerId) {
        if (confirm("Do you want to connect with this freelancer?")) {
            alert("Connection request sent to freelancer ID: " + freelancerId);
        }
    }
</script>


</body>
</html>
