<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get user id to unfollow from call
    $uid = isset($_GET["uid"]) ? intval($_GET["uid"]) : 0;
    // no id specified, throw
    if ($uid === 0) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "User ID not set: ", "code" => 403]]));
        exit();
    }
    // Delete BOTH relationships in table
    $result = RunQuery(
        null,
        "Delete From `tblFollowers` Where (`UserID` = ? And `UserFollowerID` = ?) Or (`UserID` = ? And `UserFollowerID` = ?)",
        "Change",
        "",
        "iiii",
        $_SESSION["UserID"], $uid, $uid, $_SESSION["UserID"]
    );
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Friend removal failed", "code" => 500]]));
        exit();
    }
    // Send json
    echo(json_encode(["success" => true]));
?>