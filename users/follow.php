<?php
    // Process a new follow to another users account
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    // Get userid and state (requested/follow/unfollow)
    $uid = isset($_GET["uid"]) ? intval($_GET["uid"]) : 0;
    $state = isset($_GET["state"]) ? strtolower(htmlspecialchars($_GET["state"], ENT_QUOTES, "UTF-8")) : "";
    // Check vars arent empty or 0
    if (empty($state) || $uid === 0) {
        header("Location: ../dashboard/error.php?error=nofollow");
        exit();
    }
    // Check state has been included in url/get method
    if ($state !== "follow" && $state !== "unfollow" && $state !== "requested") {
        header("Location: ../dashboard/error.php?error=nostate");
        exit();
    }
    // Depending on what needs to be done (Unfollow account, Follow account, Unrequest to follow)
    switch ($state) {
        case "follow":
            // Insert follow request into table
            $result = RunQuery(
                null,
                "Insert Into `tblFollowRequests` (`FromID`, `ToID`) Values (?, ?)",
                "Change",
                "",
                "ii",
                $_SESSION["UserID"], $uid
            );
            break;
        case "unfollow":
            // Delete follow relationships from table - remember i have a dual relationship so uid: 2-6 and 6-2 are linked
            $result = RunQuery(
                null,
                "Delete From `tblFollowers` Where (`UserID` = ? And `UserFollowerID` = ?) Or (`UserID` = ? And `UserFollowerID` = ?)",
                "Change",
                "",
                "iiii",
                $_SESSION["UserID"], $uid, $uid, $_SESSION["UserID"]
            );
            break;
        case "requested":
            // Delete a follow request
            $result = RunQuery(
                null,
                "Delete From `tblFollowRequests` Where `FromID` = ? And `ToID` = ?",
                "Change",
                "",
                "ii",
                $_SESSION["UserID"], $uid
            );
            break;
        default:
            header("Location: ../dashboard/error.php?error=nostate");
            exit();
    }
    // Catch any db errors
    CatchDBError($result);
    // Go back to the previous url
    header("Location: " . $_SERVER["HTTP_REFERER"]);
?>