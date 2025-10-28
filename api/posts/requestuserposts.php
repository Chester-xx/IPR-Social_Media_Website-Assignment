<?php
    include_once("../../includes/functions.php");
    header("Content-Type: application/json");
    StartSesh();
    CheckNotLoggedIn();
    // Define bad request, as in user error (my own code faultiness) from API call
    $BadReq = false;
    // Get user id from Get method
    $uid = isset($_GET["uid"]) ? intval($_GET["uid"]) : 0;
    if ($uid === 0) $BadReq = true;
    // Define how many posts we will send at once
    $loadlimit = 10;
    // Get the offset position of user specific posts, ie 10 to 20
    $offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;
    // Get user specific posts of account - so long that its not a bad request
    if (!$BadReq) {
        $result = RunQuery(
            null,
            "Select u.Username, u.PFP, p.PostID, p.Content, p.Image, p.CreateTime, 
            If(l.UserID Is Not Null, 1, 0) As Liked 
            From `tblPosts` p 
            Join `tblUsers` u On p.UserID = u.UserID 
            Left Join `tblLikes` l On l.PostID = p.PostID And l.UserID = ? 
            Where p.UserID = ? 
            Order By p.PostID Desc 
            Limit ? 
            Offset ?",
            "None",
            "",
            "iiii",
            $_SESSION["UserID"], $uid, $loadlimit, $offset
        );
    }
    // Catch exceptions
    if ((is_array($result) && array_key_exists("error", $result) && CatchDBError($result, true)) || $BadReq) {
        // Response 500 refers to a server side http response code, access failure
        http_response_code($BadReq ? 400 : 500);
        // Send failed request as a json with the errors
        echo(json_encode($BadReq ? ["success" => false, "error" => ["message" => "User ID not specified - Bad request", "code" => 400]] : $result));
        exit();
    }
    $list = [];
    // Append fetch to array
    // Xss prevention
    while ($row = $result->fetch_assoc()) {
        $safe = [];
        foreach ($row as $key => $value) {
            // If the key pair is a string, Escape characters to prevent xss, otherwise just parse value itself
            $safe[$key] = is_string($value) ? htmlspecialchars($value, ENT_QUOTES, "UTF-8") : $value;
        }
        $list[] = $safe;
    }
    // Send data - count < loadlimit means theres no more posts
    echo(json_encode(["success" => true, "posts" => $list, "continue" => count($list) === $loadlimit]));
    $result->free();
?>
