<?php
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
        <a href="../dashboard/followers.php" style="text-decoration: none; color: inherit;">
            <section class="friend-link">
                <h4>View Friends And Follow Requests</h4>
            </section>
        </a>
    </div>
    <div class="messages-page">
        <div class="messaging-area">
            <button class="toggle-list" id="toggleList">☰ Conversations</button>
            <div class="conversations-list" id="conversationsList"></div>
            <div class="chat-box" id="chatBox">
                <div class="chat-messages" id="chatMessages">
                    <!-- <div class="chat-message received">Hey! How are you?</div>
                    <div class="chat-message sent">I'm good, just working on a new feature.</div>
                    <div class="chat-message received">Nice! Can’t wait to see it.</div> -->
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chatInput" placeholder="Type your message">
                    <button id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../includes/functions.js"></script>
    <script>
    uid = <?php echo $_SESSION["UserID"]; ?>;
    const send = document.getElementById("sendMessage");
    const chat = document.getElementById("chatMessages");
    GetConversations();
    send.onclick = async () => {
        const inp = document.getElementById("chatInput");
        const msg = inp.value.trim();
        if (!msg || !currentUserID) return;
        try {
            const form = new FormData();
            form.append("ToID", currentUserID);
            form.append("Message", msg);
            const response = await fetch(`/api/messages/insertnewmessage.php`, {
                method: "POST",
                body: form
            });
            const data = await response.json();
            if (!data.success) {
                console.error(data.error.message, data.error.code);
                return;
            }
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
