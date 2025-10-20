<?php
    include_once("../../includes/functions.php");
    // specify packet header for json access
    header("Content-Type: application/json");
    StartSesh();
    CheckNotLoggedIn();
    // uid - 400 err specifies bad request
    $BadReq = false;
    $uid = isset($_GET["uid"]) ? intval($_GET["uid"]) : 0;
    if ($uid === 0) $BadReq = true;
    // how many posts we will send at once
    $loadlimit = 10;
    // the position of posts in the db
    $offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;
    // Get user specific posts of account - so long that its not a bad request
    if (!$BadReq) {
        $result = RunQuery(
            null,
            "Select u.Username, u.PFP, p.Content, p.Image, p.CreateTime From tblPosts p Join tblUsers u On p.UserID = u.UserID Where p.UserID = ? Order By p.PostID Desc Limit ? Offset ?",
            "None",
            "",
            "iii",
            $uid, $loadlimit, $offset
        );
    }
    // catch exceptions
    if ((is_array($result) && array_key_exists("error", $result) && CatchDBError($result, true)) || $BadReq) {
        // response 500 refers to a server side http response code, access failure
        http_response_code($BadReq ? 400 : 500);
        // send failed request as a json with the errors
        echo(json_encode($BadReq ? ["success" => false, "error" => ["message" => "User ID not specified - Bad request", "code" => 400]] : $result));
        exit();
    }
    $list = [];
    // append fetch to array
    // XSS PREVENTION ASWELL HERE
    while ($row = $result->fetch_assoc()) {
        $safe = [];
        foreach ($row as $key => $value) {
            $safe[$key] = is_string($value) ? htmlspecialchars($value, ENT_QUOTES, "UTF-8") : $value;
        }
        $list[] = $safe;
    }
    // send data - count < loadlimit means theres no more posts
    echo(json_encode(["success" => true, "posts" => $list, "continue" => count($list) === $loadlimit]));
    $result->free();
?>
