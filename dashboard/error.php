<?php
    // DASHBOARD ERROR PAGE
    // Here i display general errors that could occur as a seperate html page to the user
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
    <title>@Connect | Error</title>
</head>
<body style="text-align: center;">
    <?php 
        PrintHeader();
        // Specific to this line below: i get the username that was searched for and state that it does not exist
        if (isset($_GET["Username"])) Error("nouser", "User '" . htmlspecialchars($_GET["Username"], ENT_QUOTES, "UTF-8") . "' Does Not Exist");
        // Call helper on all specific errors outlined as the first parameter
        Error("dbfail", "Failed to communicate with the database, database connection failure");
        Error("file", "Failed to access uploaded file, please try again");
        Error("filesize", "Uploaded file is too large, please ensure the image is less than 15MB");
        Error("extension", "Unacceptable file format uploaded. 'jpg', 'jpeg', 'png', 'webp', 'gif', 'webm', 'mov' and 'mp4' only accepted");
        Error("filetransfer", "Failed to transfer file to the server, please try again");
        Error("nofollow", "User or follow action invalid");
        Error("nostate", "Invalid follow action");
    ?>
    <br><br>
    <!-- Redirect button back to dashboard -->
    <a href="../dashboard/" style="color: white;">Menu</a>
</body>
</html>