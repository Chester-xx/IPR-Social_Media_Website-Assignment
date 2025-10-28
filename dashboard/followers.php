<?php
    // USER FRIENDS LIST AND FOLLOW REQUESTS PAGE
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
    <div class="followers-page">
        <!-- Follow requests -->
        <section class="follow-requests">
            <h2>Follow Requests</h2>
            <div class="follow-list" id="follow-requests"></div>
        </section>
        <!-- Friends list -->
        <section class="friends-section">
            <h2>Your Friends</h2>
            <div class="friends-list" id="friends-list"></div>
        </section>
    </div>
    <script src="../includes/functions.js"></script>
    <script>
        // Get userid from php on load
        uid = <?php echo($_SESSION["UserID"]); ?>;
        // Call helper functions to AJAX (API CALL - No refresh needed) load follow requests and friends list
        GetFollowRequests();
        GetFriendList();
    </script>
</body>
</html>