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
    <title>@Connect | Error</title>
</head>
<body style="text-align: center;">
    <?php 
        PrintHeader();
        Error("nouser", "User '" . htmlspecialchars($_GET["Username"], ENT_QUOTES, "UTF-8") . "' Does Not Exist");
        Error("dbfail", "Failed to communicate with the database, database connection failure");
        Error("file", "Failed to access uploaded file, please try again");
        Error("filesize", "Uploaded file is too large, please ensure the image is less than 15MB");
        Error("extension", "Unacceptable file format uploaded. 'jpg', 'jpeg', 'png', 'webp', 'gif', 'webm', 'mov' and 'mp4' only accepted");
        Error("filetransfer", "Failed to transfer file to the server, please try again");
    ?>
    <br><br>
    <a href="../dashboard/" style="color: white;">Go Back</a>
</body>
</html>