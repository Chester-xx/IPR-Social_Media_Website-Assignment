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

            <!-- Toggle button for mobile -->
            <button class="toggle-list" id="toggleList">☰ Conversations</button>

            <!-- Left: Conversations -->
            <div class="conversations-list" id="conversationsList">
                <div class="conversation-header">
                    <input type="text" id="searchBar" placeholder="Search conversations...">
                </div>

                <div class="conversation-item">
                    <img src="../uploads/default.png" alt="User PFP">
                    <div>
                        <strong>@RetroStar</strong><br>
                        <small>Last message preview...</small>
                    </div>
                </div>
                <div class="conversation-item">
                    <img src="../uploads/default.png" alt="User PFP">
                    <div>
                        <strong>@NovaUser</strong><br>
                        <small>Hey, how’s it going?</small>
                    </div>
                </div>
            </div>

            <!-- Right: Chat -->
            <div class="chat-box" id="chatBox">
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-message received">Hey! How are you?</div>
                    <div class="chat-message sent">I'm good, just working on a new feature.</div>
                    <div class="chat-message received">Nice! Can’t wait to see it.</div>
                </div>

                <div class="chat-input-area">
                    <input type="text" id="chatInput" placeholder="Type your message">
                    <button id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const toggleList = document.getElementById("toggleList");
        const convList = document.getElementById("conversationsList");
        toggleList.addEventListener("click", () => {
            convList.classList.toggle("active");
        });

        const searchBar = document.getElementById("searchBar");
        searchBar.addEventListener("input", () => {
            const query = searchBar.value.toLowerCase();
            const items = document.querySelectorAll(".conversation-item");
            items.forEach(item => {
                const name = item.querySelector("strong").textContent.toLowerCase();
                item.style.display = name.includes(query) ? "flex" : "none";
            });
        });
    </script>
</body>
</html>
