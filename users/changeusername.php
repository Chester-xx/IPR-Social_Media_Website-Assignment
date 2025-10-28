<?php
    // Change a users usernames
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    // Get username input
    $username = htmlspecialchars(trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS)), ENT_QUOTES, "UTF-8");
    // Check empty and length
    if (empty($username)) {
        header("Location: /dashboard/options.php?error=empty");
        exit();
    }
    // Check the new username is 3 or more characters
    if (strlen($username) < 3) {
        header("Location: /dashboard/options.php?error=usernameshort");
        exit();
    }
    $conn = DBSesh();
    // Query all usernames that are the same
    $result = RunQuery(
        $conn,
        "Select 1 From `tblUsers` Where `Username` = ?",
        "None",
        "",
        "s",
        $username
    );
    // Catch any db errors
    CatchDBError($result);
    // Check no usernames are the same
    if ($result->num_rows > 0) {
        $conn->close();
        header("Location: /dashboard/options.php?error=userexists");
        exit();
    }
    // Change username in db
    $result = RunQuery(
        $conn,
        "Update `tblUsers` Set `Username` = ? Where `UserID` = ?",
        "None",
        "",
        "si",
        $username, $_SESSION["UserID"]
    );
    // Catch any db errors
    CatchDBError($result);
    $conn->close();
    header("Location: ../dashboard/profile.php");
    exit();
?>