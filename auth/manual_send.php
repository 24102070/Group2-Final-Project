<?php
session_start();

if (!isset($_SESSION['reset_email'], $_SESSION['reset_role'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = htmlspecialchars($_SESSION['reset_email']);
$role = htmlspecialchars($_SESSION['reset_role']);
$message = htmlspecialchars($_SESSION['reset_message'] ?? '');

$admin_email = "24102070@usc.edu.ph";

$subject_text = "Password Reset Request from $email";
$body_text = "A $role account with email $email has requested a password reset.\n\n";
if ($message) {
    $body_text .= "Message from user:\n$message\n\n";
}
$body_text .= "Please verify and assist with the password reset.";

$subject = urlencode($subject_text);
$body = urlencode($body_text);

$mailto_link = "mailto:$admin_email?subject=$subject&body=$body";
$gmail_link = "https://mail.google.com/mail/?view=cm&fs=1&to=$admin_email&su=$subject&body=$body";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manual Password Reset Request</title>
    <link rel="stylesheet" href="../assets/styles.css" />
    <link rel="stylesheet" href="../assets/login.css" />
    <style>
        .email-form {
            max-width: 600px;
            margin: 1em auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 1.2em;
            margin-bottom: 0.5em;
            color: #222;
        }
        textarea {
            width: 100%;
            padding: 0.6em 0.8em;
            font-size: 1rem;
            border: 1px solid #bbb;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: Consolas, Monaco, 'Courier New', monospace;
            background-color: #fafafa;
            color: #333;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
            resize: none;
            overflow: hidden;
            line-height: 1.4;
            position: relative;
        }
       textarea#subject {
    height: auto;
    overflow-y: auto;
    resize: none;
    white-space: normal;
    text-overflow: unset;
}

        textarea#body {
            max-height: 120px; /* limit height */
            overflow: hidden;
        }
        /* fade out gradient to show more content is hidden */
        textarea#body::after {
            content: "";
            pointer-events: none;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2em;
            background: linear-gradient(transparent, #fafafa 90%);
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Manual Password Reset Request</h2>

    <p>Your account was found as a <strong><?php echo $role; ?></strong> with email <strong><?php echo $email; ?></strong>.</p>

    <p>To request a password reset, please click one of the links below to open your email client or Gmail with a pre-filled email to the admin:</p>

    <p>
      <a href="<?php echo $mailto_link; ?>">Open mail client</a> | 
      <a href="<?php echo $gmail_link; ?>" target="_blank" rel="noopener noreferrer">Open Gmail compose</a>
    </p>

    <p>If neither link works, please manually send an email to <strong><?php echo $admin_email; ?></strong> with the following details:</p>

    <div class="email-form">
        <label for="subject">Subject:</label>
        <textarea id="subject" rows="2" readonly><?php echo $subject_text; ?></textarea>

        <label for="body">Email Body Preview:</label>
        <textarea id="body" rows="6" readonly><?php echo $body_text; ?></textarea>
    </div>

    <p><a href="forgot_password.php">Back to Forgot Password</a></p>
    <p><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>

<?php
unset($_SESSION['reset_email'], $_SESSION['reset_role'], $_SESSION['reset_message']);
?>
