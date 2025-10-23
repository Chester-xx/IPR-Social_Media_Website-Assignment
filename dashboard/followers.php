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
    <title>@Connect | Friends</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <header>
        <h1>Friends</h1>
    </header>

    <div class="followers-page">
        <section class="follow-requests">
            <h2>Follow Requests</h2>
            <div class="follow-list" id="follow-requests">
                <div class="follow-item">
                    <img src="../content/profiles/default.jpg" alt="Profile Photo">
                    <div class="follow-details">
                        <strong>Name</strong>
                    </div>
                    <div class="follow-actions">
                        <button class="accept">Accept</button>
                        <button class="decline">Decline</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="friends-section">
            <h2>Your Friends</h2>
            <div class="friends-list" id="friends-list">
                <div class="friend-item">
                    <img src="../content/profiles/default.jpg" alt="Profile Photo">
                    <div class="friend-details">
                        <strong>Username</strong>
                    </div>
                    <div class="follow-actions">
                        <button class="decline">Remove</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="../includes/functions.js"></script>
    <script>
        uid = <?php echo($_SESSION["UserID"]); ?>;
        GetFollowRequests();
        GetFriendList();
    </script>
</body>
</html>