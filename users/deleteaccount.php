<?php
    // Process account deletion
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    $conn = DBSesh();
    // Get the users profile photo to delete
    $result = RunQuery(
        $conn,
        "Select `PFP` From `tblUsers` Where `UserID` = ?",
        "Query",
        "/dashboard/options.php?error=dbfail",
        "i",
        $_SESSION["UserID"]
    );
    // Catch any db errors
    CatchDBError($result);
    // Delete the profile photo stored, unless its the default profile image
    $pfp = $result->fetch_assoc()["PFP"];
    if ($pfp && $pfp !== "default.jpg" && file_exists(PFP . $pfp)) {
        unlink(PFP . $pfp);
    }
    // Get all images/videos/gifs the user has ever uploaded
    $result = RunQuery(
        $conn,
        "Select `Image` From `tblPosts` Where `UserID` = ?",
        "Query",
        "/dashboard/options.php?error=dbfail",
        "i",
        $_SESSION["UserID"]
    );
    // Catch any db errors
    CatchDBError($result);
    // Delete each and all media stored server side
    while ($row = $result->fetch_assoc()) {
        $img = $row["Image"];
        if ($img && file_exists(PFP . $img)) {
            unlink(PFP . $img);
        }
    }
    // Delete account from table
    // -- ON DELETE CASCADE -- Only have to delete user, as i included constraints and on delete cascade for all other related tables
    $tmp = RunQuery(
        $conn,
        "Delete From `tblUsers` Where `UserID` = ?",
        "Change",
        "/dashboard/options.php?error=deletefail",
        "i",
        $_SESSION["UserID"]
    );
    // Unset user id
    unset($_SESSION["UserID"]);
    // Destroy session
    session_destroy();
    header("Location: /login/");
    exit();
?>