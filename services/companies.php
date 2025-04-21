<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

// Updated SQL query: Only show companies with status = 'Available' and approval = 'Approved'
$sql = "SELECT c.id, c.name, c.description, c.minimum_fee, c.status, p.profile_photo
        FROM companies c
        LEFT JOIN company_profiles p ON c.id = p.company_id
        WHERE c.status = 'Available' AND c.approval = 'Approved'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
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

        .search-bar {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }

        .company-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .company {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            width: 250px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .company:hover {
            transform: translateY(-5px);
        }

        .company img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .company h2 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .company p {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 10px;
        }

        .company .view-more,
        .company .book-now {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 10px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .company .view-more:hover,
        .company .book-now:hover {
            background-color: #45a049;
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

    <h1>Event Planning Companies</h1>

    <input type="text" id="search-bar" class="search-bar" placeholder="Search for a company..." onkeyup="filterCompanies()">

    <div class="company-container" id="company-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="company" data-name="<?php echo htmlspecialchars($row['name']); ?>">
                <?php
                $profile_photo = !empty($row['profile_photo']) ? "../" . $row['profile_photo'] : "../assets/default-profile.png";
                ?>
                <img src="<?php echo $profile_photo; ?>" alt="Company Profile">

                <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                <p><strong>Minimum Fee:</strong> ₱<?php echo number_format($row['minimum_fee'], 2); ?></p>

                <a href="company_details.php?id=<?php echo $row['id']; ?>" class="view-more">View More</a>
            </div>
        <?php endwhile; ?>
    </div>

    <a href="../dashboard/dashboard.php" class="back-btn">Back to Dashboard</a>

    <script>
        function filterCompanies() {
            const query = document.getElementById('search-bar').value.toLowerCase();
            const companies = document.querySelectorAll('.company');

            companies.forEach(company => {
                const companyName = company.getAttribute('data-name').toLowerCase();
                if (companyName.includes(query)) {
                    company.style.display = '';
                } else {
                    company.style.display = 'none';
                }
            });
        }
    </script>

</body>
</html>
