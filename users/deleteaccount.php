<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();

    $conn = DBSesh();

    $result = RunQuery(
        $conn,
        "Select `PFP` From `tblUsers` Where `UserID` = ?",
        "Query",
        "/dashboard/options.php?error=dbfail",
        "i",
        $_SESSION["UserID"]
    );
    CatchDBError($result);

    $pfp = $result->fetch_assoc()["PFP"];
    if ($pfp && $pfp !== "default.jpg" && file_exists(PFP . $pfp)) {
        unlink(PFP . $pfp);
    }

    $result = RunQuery(
        $conn,
        "Select `Image` From `tblPosts` Where `UserID` = ?",
        "Query",
        "/dashboard/options.php?error=dbfail",
        "i",
        $_SESSION["UserID"]
    );
    CatchDBError($result);

    while ($row = $result->fetch_assoc()) {
        $img = $row["Image"];
        if ($img && file_exists(PFP . $img)) {
            unlink(PFP . $img);
        }
    }

    // Only have to delete user, as i included constraints and on delete cascade for all other related tables
    $tmp = RunQuery(
        $conn,
        "Delete From `tblUsers` Where `UserID` = ?",
        "Change",
        "/dashboard/options.php?error=deletefail",
        "i",
        $_SESSION["UserID"]
    );

    unset($_SESSION["UserID"]);
    session_destroy();

    header("Location: /login/");
    exit();
?>