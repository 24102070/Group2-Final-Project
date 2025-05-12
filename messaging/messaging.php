<?php
//TASK FOR THURS:
//the user starts off with no recipient ID when newly logged in by default(show start a message and search for a user) 
//whole search thing will be on the side search bar
//onclick the receipeint_id will set to be whoever the account that gets clicked


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$recipient_id = isset($_GET['recipient_id']) ? $_GET['recipient_id'] : null;
$recipient_name = isset($_GET['name']) ? $_GET['name'] : null;
$recipient_type = isset($_GET['type']) ? $_GET['type'] : null;


if (!empty($searchTerm)) {
    $searchWildcard = '%' . $searchTerm . '%';

    // Prepare the SQL query using UNION to merge results from all three tables
    $sql = "
        (SELECT id, name, 'User' AS type, 'user' AS user_type FROM users WHERE name LIKE ?)
        UNION
        (SELECT id, name, 'Company' AS type, 'company' AS user_type FROM companies WHERE name LIKE ?)
        UNION
        (SELECT id, name, 'Freelancer' AS type, 'freelancer' AS user_type FROM freelancers WHERE name LIKE ?)
        ORDER BY name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchWildcard, $searchWildcard, $searchWildcard);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Search Results for: <em>" . htmlspecialchars($searchTerm) . "</em></h2>";

    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li class='search-result' data-id='" . $row['id'] . "' data-name='" . htmlspecialchars($row['name'])
            . "' data-type='" . $row['type'] . "' data-user-type='" . $row['user_type'] . "' onclick='openChatWindow(this)'>
            <strong>" . htmlspecialchars($row['name']) . "</strong> (" . $row['type'] . ")
            </li>";
        }
        echo "</ul>";
    } else {
        echo "No results found.";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface</title>
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="menubarcss.css">
    <link rel="stylesheet" href="messagescss.css">
    <link rel="stylesheet" href="mainchatcss.css">
</head>
<body>
    <div class="chat-container">
        <!--Side Menu-->
        <div class="menubar">
            <label class="container-menu" for="dropdownToggle">
                <input type="checkbox" id="dropdownToggle" class="dropdown-toggle">
                <div class="checkmark">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <div class="dropdown-menu">
                    <div class="dropdown-item">Dash Board</div>
                    <div class="dropdown-item">Settings</div>
                    <div class="dropdown-item"><a href="../auth/logout.php">Logout</a></div>
                </div>
            </label>
            <div class="profile-pic">
                <img src="ian_profile.jfif" class="profile">
            </div>
        </div>
        <!-- Sidebar -->
        <div class="chatbar">
            <div class="direct-messages">
                <form class="form" method="GET" action="">
                    <button>
                        <svg width="17" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="search">
                            <path d="M7.667 12.667A5.333 5.333 0 107.667 2a5.333 5.333 0 000 10.667zM14.334 14l-2.9-2.9" stroke="currentColor" stroke-width="1.333" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <input class="input" placeholder="Search users..." required="" type="text" name="search">

                    <button class="reset" type="reset">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </form>
                <div class="users-chats">

                </div>
            </div>
            

            <div class="separator"></div>
            <div class="direct-messages">
                <div class="freelancers-area">
                        <form class="form">
                            <button>
                                <svg width="17" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="search">
                                    <path d="M7.667 12.667A5.333 5.333 0 107.667 2a5.333 5.333 0 000 10.667zM14.334 14l-2.9-2.9" stroke="currentColor" stroke-width="1.333" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                            <input class="input" placeholder="Freelancers" required="" type="text" name="searchFreelancer" id="searchFreelancer">
                            <button class="reset" type="reset">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </form>
                    <div class="freelancer-chats">
                        <!-- <div class="conversation-card" onclick="">
                             <div class="profile-picture" style="background-image: url('ian_profile.jfif');"></div>
                            <div class="message-content">
                                <div class="search-results">
                                    <?= $searchResults = '' ?>
                                </div>
                                <div class="message">this is a test<span class="timestamp">3:00 AM</span></div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="separator"></div>
            <div class="group-chats">
                <form class="form">
                    <button>
                        <svg width="17" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="search">
                            <path d="M7.667 12.667A5.333 5.333 0 107.667 2a5.333 5.333 0 000 10.667zM14.334 14l-2.9-2.9" stroke="currentColor" stroke-width="1.333" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                    <input class="input" placeholder="Companies" required="" type="text">
                    <button class="reset" type="reset">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </form>
                <div class="cards_messages_container" style="height:75%;">
                    <!-- <div class="conversation-card" onclick="" style="margin: 0;">
                        <div class="profile-picture" style="background-image: url('op.jfif');"></div>
                        <div class="message-content">
                            <div class="username">
                                On Point
                            </div>
                            <div class="message">Lorem ipsum dolor sit a...<span class="timestamp">12:50 AM</span></div>
                        </div>
                    </div>
                    <div class="conversation-card" onclick="">
                        <div class="profile-picture" style="background-image: url('de.jfif');"></div>
                        <div class="message-content">
                            <div class="username">
                                Dion Events
                            </div>
                            <div class="message">Please reply to me.<span class="timestamp">9:07 PM</span></div>
                        </div>
                    </div>
                    <div class="conversation-card" onclick="">
                        <div class="profile-picture" style="background-image: url('cq.jfif');"></div>
                        <div class="message-content">
                            <div class="username">
                                CQ Events
                            </div>
                            <div class="message">Meeting tomorrow at...<span class="timestamp">6:49 AM</span></div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
        <!-- Main Chat Section -->
        <div class="main-chat">
            <div class="chats-top-container">
                <div class="chat-card">
                    <div class="profile-picture" style="background-image: url('ianprof.png');"></div>
                    <div class="chat-content">
                        <div class="username"><u>Ian Miguel Florentino</u><div class="on-status"></div></div>
                        <div class="status">Online</div>
                    </div>
                    <div class="container-icons">
                        <!--Voice Call-->
                        <button class="voice_call" id="voice_call">
                            <svg width="45px" height="30px" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.71922 0.5H2.5C1.39543 0.5 0.5 1.39543 0.5 2.5V4.5C0.5 10.0228 4.97715 14.5 10.5 14.5H12.5C13.6046 14.5 14.5 13.6046 14.5 12.5V11.118C14.5 10.7393 14.286 10.393 13.9472 10.2236L11.5313 9.01564C10.987 8.74349 10.3278 9.01652 10.1354 9.59384L9.83762 10.4871C9.64474 11.0658 9.05118 11.4102 8.45309 11.2906C6.05929 10.8119 4.18814 8.94071 3.70938 6.54691C3.58976 5.94882 3.93422 5.35526 4.51286 5.16238L5.62149 4.79284C6.11721 4.6276 6.40214 4.10855 6.2754 3.60162L5.68937 1.25746C5.57807 0.812297 5.17809 0.5 4.71922 0.5Z" stroke="#000000"/></svg>
                        </button>
                        <!--Video Call-->
                        <button class="vid-call" id="vid-call">
                            <svg fill="#000000" width="45px" height="30px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M17,8.38196601 L23,5.38196601 L23,18.618034 L17,15.618034 L17,17 C17,18.1045695 16.1045695,19 15,19 L3,19 C1.8954305,19 1,18.1045695 1,17 L1,7 C1,5.8954305 1.8954305,5 3,5 L15,5 C16.1045695,5 17,5.8954305 17,7 L17,8.38196601 Z M17,10.618034 L17,13.381966 L21,15.381966 L21,8.61803399 L17,10.618034 Z M3,7 L3,17 L15,17 L15,7 L3,7 Z"/></svg>
                        </button>
                        <!--More Info-->
                        <button class="more_info" id="more_info">
                            <svg width="45px" height="30px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#000000"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.568 1.031A6.8 6.8 0 0 1 12.76 3.05a7.06 7.06 0 0 1 .46 9.39 6.85 6.85 0 0 1-8.58 1.74 7 7 0 0 1-3.12-3.5 7.12 7.12 0 0 1-.23-4.71 7 7 0 0 1 2.77-3.79 6.8 6.8 0 0 1 4.508-1.149zM9.04 13.88a5.89 5.89 0 0 0 3.41-2.07 6.07 6.07 0 0 0-.4-8.06 5.82 5.82 0 0 0-7.43-.74 6.06 6.06 0 0 0 .5 10.29 5.81 5.81 0 0 0 3.92.58zM7.375 6h1.25V5h-1.25v1zm1.25 1v4h-1.25V7h1.25z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="line"></div>
            </div>
            <div class="chats-bottom-container">
                <div class="messages-display" id="displaymessage">
                    <?php if ($recipient_id): ?>
                        <div class="o-u-info">
                            <div class="profile-picture-display-message" style="background-image: url('default_profile.png');"></div>
                            <div class="username"><?= htmlspecialchars($recipient_name) ?></div>
                            <div class="email"><u><?= htmlspecialchars($recipient_type) ?></u></div>
                        </div>
                    <?php else: ?>
                        <div class="o-u-info">
                            <div class="profile-picture-display-message" style="background-image: url('default_profile.png');"></div>
                            <div class="username">Start a message</div>
                            <div class="email"><u>Select a user to begin chatting</u></div>
                        </div>
                    <?php endif; ?>
                    <div class="line"></div>
                </div>
                
                <div class="messages-input">
                    <input class="typemessage" type="text" id="messageInput" placeholder="Send A Message." />
                    <div class="moreOption">
                        <!--Send Location button-->
                        <button class="send_location" id="send_location">
                            <svg fill="#000000" width="20px" height="40px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18,4.48a8.45,8.45,0,0,0-12,12l5.27,5.28a1,1,0,0,0,1.42,0L18,16.43A8.45,8.45,0,0,0,18,4.48ZM16.57,15,12,19.59,7.43,15a6.46,6.46,0,1,1,9.14,0ZM9,7.41a4.32,4.32,0,0,0,0,6.1,4.31,4.31,0,0,0,7.36-3,4.24,4.24,0,0,0-1.26-3.05A4.3,4.3,0,0,0,9,7.41Zm4.69,4.68a2.33,2.33,0,1,1,.67-1.63A2.33,2.33,0,0,1,13.64,12.09Z"/></svg>
                        </button>
                        <!--Add a emoji button-->
                        <button class="add_emoji" id="add_emoji">
                            <svg fill="#000000" width="20px" height="40px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="M14.36,14.23a3.76,3.76,0,0,1-4.72,0,1,1,0,0,0-1.28,1.54,5.68,5.68,0,0,0,7.28,0,1,1,0,1,0-1.28-1.54ZM9,11a1,1,0,1,0-1-1A1,1,0,0,0,9,11Zm6-2a1,1,0,1,0,1,1A1,1,0,0,0,15,9ZM12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm0,18a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z"/></svg>
                        </button>
                        <!--Add a File or image button-->
                        <button class="add_image_file" id="add_image_file">
                            <svg fill="#000000" width="20px" height="40px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512 282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01 0-247.024 200.976-448 448-448s448 200.977 448 448-200.976 449.01-448 449.01zM736 480H544V288c0-17.664-14.336-32-32-32s-32 14.336-32 32v192H288c-17.664 0-32 14.336-32 32s14.336 32 32 32h192v192c0 17.664 14.336 32 32 32s32-14.336 32-32V544h192c17.664 0 32-14.336 32-32s-14.336-32-32-32z"/></svg>
                        </button>
                        <!--Send Button-->
                        <button class="send_message" id="send_message">
                            <svg fill="#000000" width="20px" height="40px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.10514201,11.8070619 L2.74013818,2.2520351 L22.236068,12 L2.74013818,21.7479649 L4.10514201,12.1929381 L4.87689437,12 L4.10514201,11.8070619 Z M5.25986182,5.7479649 L5.89485799,10.1929381 L13.1231056,12 L5.89485799,13.8070619 L5.25986182,18.2520351 L17.763932,12 L5.25986182,5.7479649 Z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="userjs/users.js" defer></script>
    <script src="userjs/usersFreelancers.js" defer></script>
    <script src="userjs/usersCompanies.js" defer></script>
    <script src="userjs/search.js" defer></script>
<script>
    let currentRecipientId = null;
    let currentRecipientType = null;
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
    const currentUserType = '<?php echo $_SESSION['role']; ?>';

    function openChatWindow(element) {
        // Get data attributes from the clicked result
        const recipientId = element.getAttribute('data-id');
        console.log (recipientId);
        const recipientName = element.getAttribute('data-name');
        console.log(recipientName);
        const recipientType = element.getAttribute('data-type');
        console.log(recipientType);
        const recipientUserType = element.getAttribute('data-user-type');

        // Store the selected recipient ID and type
        currentRecipientId = recipientId;
        currentRecipientType = recipientUserType;

        // Update the chat interface with the recipient's information
        const displayArea = document.getElementById('displaymessage');
        displayArea.innerHTML = `
            <div class="o-u-info">
                <div class="profile-picture-display-message" style="background-image: url('default_profile.png');"></div>
                <div class="username">${recipientName}</div>
                <div class="email"><u>${recipientType} (${recipientUserType})</u></div>
            </div>
            <div class="line"></div>
            <p class="time">Now</p>
            <div class="message-other-message">Start chatting with ${recipientName}!</div>
        `;

        // Optional: Scroll to top or bring chat window into view
        displayArea.scrollIntoView({ behavior: 'smooth' });

        console.log(`Selected recipient ID: ${currentRecipientId}, Type: ${currentRecipientType}`);

        // Load previous messages
        fetch(`load_messages.php?user_id=${currentUserId}&user_type=${currentUserType}&recipient_id=${recipientId}&recipient_type=${recipientUserType}`)
        .then(response => response.json())
        .then(messages => {
            displayArea.innerHTML = ''; // Clear previous messages
            messages.forEach(message => {
                // Check both ID and type to determine if message is from current user
                const isCurrentUser = message.user_id === currentUserId && message.user_type === currentUserType;
                const messageClass = isCurrentUser ? 'message-user-message' : 'message-other-message';
                displayArea.innerHTML += `
                    <div class="${messageClass}">${message.text_input}</div>
                    <p class="time">${new Date(message.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                `;
            });
        })
        .catch(error => console.error('Error loading messages:', error));
    }

    // Send message handler
    document.getElementById('messageInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            e.preventDefault();

            const messageText = this.value.trim();
            const messageContainer = document.getElementById('displaymessage');

            if (!currentRecipientId || !currentRecipientType) {
                alert("Please select a recipient first.");
                return;
            }

            // Save message to the database
            fetch('save_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: currentUserId,
                    user_type: currentUserType,
                    recipient_id: currentRecipientId,
                    recipient_type: currentRecipientType,
                    text_input: messageText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageContainer.innerHTML += `
                        <div class="message-user-message">${messageText}</div>
                        <p class="time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                    `;
                    this.value = '';
                } else {
                    alert("Error saving message: " + (data.error || "Unknown error"));
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
</script>

</body>
</html>

