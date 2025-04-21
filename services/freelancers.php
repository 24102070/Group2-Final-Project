<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Fetch available professions for the filter dropdown
$professions_sql = "SELECT DISTINCT profession FROM freelancers WHERE status = 'Available' AND approval = 'Approved'";
$professions_result = $conn->query($professions_sql);

// Query for freelancers with status = 'Available' and approval = 'Approved'
$sql = "SELECT f.id, f.name, f.profession, f.description, f.minimum_fee, f.status, p.profile_photo
        FROM freelancers f
        LEFT JOIN freelancer_profiles p ON f.id = p.freelancer_id
        WHERE f.status = 'Available' AND f.approval = 'Approved'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancers</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .freelancer-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .freelancer {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            width: 250px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .freelancer:hover {
            transform: translateY(-5px);
        }

        .freelancer img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .freelancer h2 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .freelancer p {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 10px;
        }

        .freelancer .view-more,
        .freelancer .book-now {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 10px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .freelancer .view-more:hover,
        .freelancer .book-now:hover {
            background-color: #45a049;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-bar {
            padding: 10px;
            width: 60%;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }

        .filter-dropdown {
            padding: 10px;
            width: 20%;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .back-btn {
            display: block;
            margin: 30px auto;
            text-align: center;
            font-size: 1.1rem;
            color: #333;
            text-decoration: none;
            padding: 8px 16px;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>

    <h1>Available Freelancers</h1>

    <!-- Search Bar and Filter Dropdown -->
    <div class="search-container">
        <input type="text" id="searchBar" class="search-bar" placeholder="Search by name or profession..." onkeyup="filterFreelancers()">
        <select id="professionFilter" class="filter-dropdown" onchange="filterFreelancers()">
            <option value="">Filter by Profession</option>
            <?php while ($row = $professions_result->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['profession']); ?>"><?php echo htmlspecialchars($row['profession']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="freelancer-container" id="freelancerList">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="freelancer" data-name="<?php echo htmlspecialchars(strtolower($row['name'])); ?>" data-profession="<?php echo htmlspecialchars(strtolower($row['profession'])); ?>">
                <?php
                $profile_photo = !empty($row['profile_photo']) ? "../" . $row['profile_photo'] : "../assets/default-profile.png";
                ?>
                <img src="<?php echo $profile_photo; ?>" alt="Freelancer Profile">
                <h2><?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['profession']); ?>)</h2>
                <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                <p><strong>Min. Fee:</strong> ₱<?php echo number_format($row['minimum_fee'], 2); ?></p>
                <a href="freelancer_details.php?id=<?php echo $row['id']; ?>" class="view-more">View More</a>
            </div>
        <?php endwhile; ?>
    </div>

    <a href="../dashboard/dashboard.php" class="back-btn">Back to Dashboard</a>

    <script>
        function filterFreelancers() {
            let searchInput = document.getElementById("searchBar").value.toLowerCase();
            let professionFilter = document.getElementById("professionFilter").value.toLowerCase();
            let freelancers = document.getElementsByClassName("freelancer");

            for (let i = 0; i < freelancers.length; i++) {
                let name = freelancers[i].getAttribute("data-name");
                let profession = freelancers[i].getAttribute("data-profession");

                if ((name.includes(searchInput) || profession.includes(searchInput)) &&
                    (profession.includes(professionFilter) || professionFilter === "")) {
                    freelancers[i].style.display = "block";
                } else {
                    freelancers[i].style.display = "none";
                }
            }
        }
    </script>

</body>
</html>
