<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();

    $uid = isset($_GET["uid"]) ? intval($_GET["uid"]) : 0;
    $state = isset($_GET["state"]) ? strtolower(htmlspecialchars($_GET["state"], ENT_QUOTES, "UTF-8")) : "";

    if (empty($state) || $uid === 0) {
        header("Location: ../dashboard/error.php?error=nofollow");
        exit();
    }

    if ($state !== "follow" && $state !== "unfollow" && $state !== "requested") {
        header("Location: ../dashboard/error.php?error=nostate");
        exit();
    }

    switch ($state) {
        case "follow":
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
    CatchDBError($result);
    header("Location: " . $_SERVER["HTTP_REFERER"]);
?>