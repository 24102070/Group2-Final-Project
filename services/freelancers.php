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
    <title>Available Freelancers</title>   
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global Styling */
        body {
            font-family: 'Poppins', serif;
            font-weight: 300;
            margin: 0;
            padding: 0;
            color: #5A4A42;
            line-height: 1.6;
            background: url('https://images.unsplash.com/photo-1589243853654-393fcf7c870b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: 200% 200%;
            animation: gradient 55s ease infinite;
            min-height: 100vh;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .overlay-container {
            position: relative;
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(255, 173, 153, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
        }

        /* Cloudy effect */
        .overlay-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><filter id="cloudy-effect"><feTurbulence type="fractalNoise" baseFrequency="0.05" numOctaves="5" /><feDisplacementMap in="SourceGraphic" scale="10" /></filter><rect width="100" height="100" filter="url(%23cloudy-effect)" opacity="0.15" fill="white"/></svg>');
            opacity: 0.4;
            z-index: -1;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            text-align: center;
            color: #E67B7B;
            font-size: 2.8em;
            margin-bottom: 40px;
            font-weight: 100;
            letter-spacing: 1px;
            position: relative;
            padding-bottom: 20px;
        }

        h1:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #E67B7B, transparent);
        }

        .search-container {
            position: relative;
            max-width: 800px;
            margin: 30px auto 40px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-bar {
            flex: 1;
            padding: 16px 60px 16px 30px;
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 12px rgba(255, 183, 161, 0.1);
            transition: all 0.3s ease;
            color: #5A4A42;
            border: 1px solid rgba(230, 123, 123, 0.3);
            font-family: 'Poppins', serif;
            font-weight: 300;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .search-bar:focus {
            outline: none;
            box-shadow: 0 8px 24px rgba(255, 183, 161, 0.2);
            border-color: rgba(230, 123, 123, 0.5);
        }

        .filter-dropdown {
            flex: 0.6;
            padding: 16px 30px;
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 12px rgba(255, 183, 161, 0.1);
            transition: all 0.3s ease;
            color: #5A4A42;
            border: 1px solid rgba(230, 123, 123, 0.3);
            font-family: 'Poppins', serif;
            font-weight: 300;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23E67B7B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 1em;
        }

        .filter-dropdown:focus {
            outline: none;
            box-shadow: 0 8px 24px rgba(255, 183, 161, 0.2);
            border-color: rgba(230, 123, 123, 0.5);
        }

        .search-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            color: #E67B7B;
            font-size: 1.2rem;
        }

        .freelancer-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .freelancer {
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(255, 183, 161, 0.1);
            transition: all 0.4s ease;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .freelancer:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 24px rgba(255, 183, 161, 0.2);
            background-color: rgba(255, 255, 255, 0.9);
        }

        .freelancer-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(230, 123, 123, 0.9);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 400;
            z-index: 2;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .profession-badge {
            display: inline-block;
            background-color: rgba(255, 183, 161, 0.9);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 15px;
            font-weight: 400;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .freelancer-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .freelancer:hover .freelancer-image {
            transform: scale(1.05);
        }

        .image-container {
            overflow: hidden;
            position: relative;
        }

        .image-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40%;
            background: linear-gradient(to top, rgba(255,255,255,0.9) 0%, transparent 100%);
        }

        .freelancer-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .freelancer h2 {
            font-size: 1.4rem;
            color: #E67B7B;
            margin-bottom: 12px;
            font-family: 'Playfair Display', serif;
            line-height: 1.3;
            font-weight: 400;
        }

        .freelancer p {
            font-size: 0.95rem;
            color: #7A6A65;
            margin-bottom: 20px;
            line-height: 1.6;
            flex-grow: 1;
        }

        .freelancer .fee {
            font-weight: 400;
            color: #E67B7B;
            font-size: 1.1rem;
            margin: 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .freelancer .fee i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .freelancer .actions {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }

        .freelancer .view-more {
            text-decoration: none;
            background-color: rgba(230, 123, 123, 0.9);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 300;
            border: none;
            text-align: center;
            width: 100%;
            font-family: 'Poppins', serif;
            letter-spacing: 0.5px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            box-shadow: 0 2px 8px rgba(230, 123, 123, 0.2);
        }

        .freelancer .view-more:hover {
            background-color: rgba(212, 106, 106, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(230, 123, 123, 0.3);
        }

        .back-btn {
            font-family: 'Poppins', 'Serif';
            display: block;
            width: 200px;
            margin: 30px auto 0;
            text-align: center;
            padding: 12px 0;
            background-color: #E67B7B;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 300;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 183, 161, 0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .back-btn:hover {
            background-color: rgba(255, 163, 138, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 183, 161, 0.4);
        }

        .no-results {
            text-align: center;
            color: #7A6A65;
            font-size: 1.3rem;
            margin: 80px 0;
            width: 100%;
            grid-column: 1 / -1;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 30px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .no-results i {
            font-size: 2rem;
            color: #E67B7B;
            margin-bottom: 15px;
            display: block;
        }

        /* Decorative elements */
        .peach-blob {
            position: fixed;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,183,161,0.3) 0%, rgba(255,183,161,0) 70%);
            border-radius: 50%;
            z-index: -1;
            filter: blur(20px);
        }

        .peach-blob-1 {
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
        }

        .peach-blob-2 {
            bottom: -150px;
            left: -150px;
            width: 500px;
            height: 500px;
        }

        @media (max-width: 768px) {
            .overlay-container {
                width: 95%;
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .search-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-bar,
            .filter-dropdown {
                width: 100%;
            }
            
            .freelancer-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="peach-blob peach-blob-1"></div>
    <div class="peach-blob peach-blob-2"></div>

    <div class="overlay-container">
        <h1>Available Freelancers</h1>
         <a href="../dashboard/dashboard.php" class="back-btn">
            Back to Dashboard
        </a>
        <!-- Search Bar and Filter Dropdown -->
        <div class="search-container">
            <input type="text" id="searchBar" class="search-bar" placeholder="Search by name or profession...">
            <div class="search-icon"><i class="fas fa-search"></i></div>
            <select id="professionFilter" class="filter-dropdown">
                <option value="">All Professions</option>
                <?php while ($row = $professions_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['profession']); ?>"><?php echo htmlspecialchars($row['profession']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="freelancer-container" id="freelancerList">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="freelancer" data-name="<?php echo htmlspecialchars(strtolower($row['name'])); ?>" data-profession="<?php echo htmlspecialchars(strtolower($row['profession'])); ?>">
                        <div class="freelancer-badge">Available</div>
                        <div class="image-container">
                            <?php
                            $profile_photo = !empty($row['profile_photo']) ? "../" . $row['profile_photo'] : "../assets/default-profile.png";
                            ?>
                            <img src="<?php echo $profile_photo; ?>" alt="Freelancer Profile" class="freelancer-image">
                        </div>
                        <div class="freelancer-content">
                            <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                            <span class="profession-badge"><?php echo htmlspecialchars($row['profession']); ?></span>
                            <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                            <div class="fee">
                                <i class="fas fa-tag"></i>
                                ₱<?php echo number_format($row['minimum_fee'], 2); ?>
                            </div>
                            <div class="actions">
                                <a href="freelancer_details.php?id=<?php echo $row['id']; ?>" class="view-more">
                                    View Details <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="far fa-calendar-times"></i>
                    No freelancers available at the moment.<br>Please check back later.
                </div>
            <?php endif; ?>
        </div>

       
    </div>

    <script>
        function filterFreelancers() {
            let searchInput = document.getElementById("searchBar").value.toLowerCase();
            let professionFilter = document.getElementById("professionFilter").value.toLowerCase();
            let freelancers = document.querySelectorAll('.freelancer');
            let hasResults = false;

            freelancers.forEach(freelancer => {
                let name = freelancer.getAttribute("data-name");
                let profession = freelancer.getAttribute("data-profession");

                if ((name.includes(searchInput) || profession.includes(searchInput)) &&
                    (profession.includes(professionFilter) || professionFilter === "")) {
                    freelancer.style.display = "flex";
                    hasResults = true;
                } else {
                    freelancer.style.display = "none";
                }
            });

            // Show "no results" message if needed
            let noResultsMsg = document.getElementById("noResults");
            if (!hasResults && (searchInput !== '' || professionFilter !== '')) {
                if (!noResultsMsg || noResultsMsg.textContent.includes('check back later')) {
                    const freelancerList = document.getElementById("freelancerList");
                    const message = document.createElement("div");
                    message.className = "no-results";
                    message.innerHTML = `
                        <i class="fas fa-search-minus"></i>
                        No matching freelancers found.<br>Try different search terms.
                    `;
                    freelancerList.appendChild(message);
                }
            } else if (noResultsMsg && noResultsMsg.textContent.includes('matching')) {
                noResultsMsg.remove();
            }
        }

        // Add event listeners for search and filter
        document.getElementById("searchBar").addEventListener('keyup', filterFreelancers);
        document.getElementById("professionFilter").addEventListener('change', filterFreelancers);
    </script>
</body>
</html>

