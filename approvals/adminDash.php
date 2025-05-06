<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle file download request
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']); // Sanitize input
    $filepath = "../uploads/" . $filename;

    if (file_exists($filepath)) {
        // Set headers to force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush();
        readfile($filepath);
        exit();
    } else {
        echo "File not found.";
        exit();
    }
}

// Fetch all pending approvals
$sql = "SELECT 'company' AS type, id, name, email, description AS details, cert_file AS file FROM companies WHERE approval = 'Pending'
        UNION ALL
        SELECT 'freelancer' AS type, id, name, email, profession AS details, resume_file AS file FROM freelancers WHERE approval = 'Pending'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approval Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      /* Base Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f4f3 0%, #fdfcfc 100%);
            color: #333;
            padding: 40px;
            line-height: 1.6;
            background-attachment: fixed;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-image: url('../images/weddingadmin.jfif'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.1; /* Light opacity (adjust between 0.05 to 0.2 as needed) */
            z-index: -1;
        }

      
        
        h1 {
            text-align: center;
            font-size: 4rem;
            font-weight: 700;
            color: #fff; /* white */
            margin-bottom: 50px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6); /* stronger shadow */
            text-transform: uppercase; /* all caps */
        }


        /* Container */
        .approval-box {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 35px;
            transition: transform 0.3s ease;
        }

        .approval-box:hover {
            transform: translateY(-3px);
        }

        /* Left Content */
        .approval-box-content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .approval-box h2 {
            font-size: 1.5rem;
            color: #e4816c;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .approval-box p {
            font-size: 1rem;
            color: #555;
        }

        .approval-box a {
            margin-top: 10px;
            color: #e4816c;
            font-weight: 500;
            text-decoration: none;
        }

        .approval-box a:hover {
            text-decoration: underline;
        }

        /* Action Form */
        form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #e4816c;
            padding: 25px;
            gap: 12px;
            min-width: 160px;
            align-items: center;
        }

        form button {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        /* Approve Button */
        form button[name="approve"] {
            background-color: #2ecc71;
        }

        form button[name="approve"]:hover {
            background-color: #27ae60;
        }

        /* Reject Button */
        form button[name="reject"] {
            background-color: #e74c3c;
        }

        form button[name="reject"]:hover {
            background-color: #c0392b;
        }

        /* Download Button Example */
        .download-btn {
            background-color:rgb(250, 250, 250);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .download-btn:hover {
             background-color:rgb(179, 179, 179);
        }

        /* Logout Link */
        a {
            display: block;
            margin-top: 30px;
            text-align: center;
            font-weight: 500;
            text-decoration: none;
            background-color: #ccc;
            color: #333;
            padding: 12px 20px;
            border-radius: 8px;
            width: fit-content;
           
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #b3b3b3;
        }
        /* Responsive */
@media (min-width: 768px) {
    .approval-box {
        flex-direction: row;
    }

    .approval-box-content {
        flex: 1;
        border-bottom: none;
        border-right: 1px solid #eee;
    }

    form {
        flex-direction: column;
        padding: 30px 20px;
    }
}

    </style>
</head>
<body>

    <h1>PENDING APPROVALS</h1>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="approval-box">
    <div class="approval-box-content">
        <h2><?php echo ucfirst($row['type']); ?>: <?php echo $row['name']; ?></h2>
        <p>Email: <?php echo $row['email']; ?></p>
        <p><strong><?php echo ($row['type'] == 'company') ? 'Business description' : 'Profession'; ?>:</strong> <?php echo $row['details']; ?></p>
        <p><a href="adminDash.php?download=<?php echo urlencode($row['file']); ?>" class="download-btn"><i class="fas fa-download"></i>Download Document</a></p>
    </div>

    <form action="approve.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="type" value="<?php echo $row['type']; ?>">
        <button type="submit" name="approve">Approve</button>
        <button type="submit" name="reject">Reject</button>
    </form>
</div>
    <?php endwhile; ?>

    <a href="../auth/login.php">Logout</a>
</body>
</html>
