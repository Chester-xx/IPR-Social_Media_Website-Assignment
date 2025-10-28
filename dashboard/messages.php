<?php
    // MESSAGES PAGE FOR LOGGED IN USERS
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>@Connect | Messages</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div style="justify-content: center; align-content: center;">
        <!-- Link to the friends and follow requests page -->
        <a href="../dashboard/followers.php" style="text-decoration: none; color: inherit;">
            <section class="friend-link">
                <h4>View Friends And Follow Requests</h4>
            </section>
        </a>
    </div>
    <!-- Messages -->
    <div class="messages-page">
        <div class="messaging-area">
            <!-- For smaller screens, i made it so that theres an option to open conversation list and select a different one -->
            <button class="toggle-list" id="toggleList">☰ Conversations</button>
            <div class="conversations-list" id="conversationsList"></div>
            <div class="chat-box" id="chatBox">
                <!-- All individual user specific messages loaded here -->
                <div class="chat-messages" id="chatMessages"></div>
                <!-- Typing and sending a message -->
                <div class="chat-input-area">
                    <input type="text" id="chatInput" placeholder="Type your message">
                    <button id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../includes/functions.js"></script>
    <script>
        // Get user id from php session
        uid = <?php echo $_SESSION["UserID"]; ?>;
        // Set consts
        const send = document.getElementById("sendMessage");
        const chat = document.getElementById("chatMessages");
        // Call for all open or active conversations
        GetConversations();
        // When the user clicks on send message
        send.onclick = async () => {
            const inp = document.getElementById("chatInput");
            const msg = inp.value.trim();
            if (!msg || !currentUserID) return;
            try {
                // Send via POST to php
                const form = new FormData();
                form.append("ToID", currentUserID);
                form.append("Message", msg);
                // API call
                const response = await fetch(`/api/messages/insertnewmessage.php`, {
                    method: "POST",
                    body: form
                });
                const data = await response.json();
                if (!data.success) {
                    console.error(data.error.message, data.error.code);
                    return;
                }
                // Dynamic loading
                const div = document.createElement("div");
                div.classList.add("chat-message", "sent");
                div.textContent = msg;
                const time = document.createElement("small");
                time.textContent = " Just now";
                div.appendChild(time);
                chat.appendChild(div);
                chat.scrollTop = chat.scrollHeight;
                inp.value = "";
            } catch (e) {
                console.error(e);
            }
        };
    </script>
</body>
</html>
