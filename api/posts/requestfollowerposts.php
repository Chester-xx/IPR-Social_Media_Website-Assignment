<?php
    include_once("../../includes/functions.php");
    // specify packet header for json access
    header("Content-Type: application/json");
    // errors
    StartSesh();
    CheckNotLoggedIn();
    // how many posts we will send at once
    $loadlimit = 10;
    // the position of posts in the db
    $offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;
    // Get posts from offset for some limit defined
    $result = RunQuery(
        null,
        "Select u.Username, u.PFP, p.Content, p.Image, p.CreateTime, p.PostID, If(l.UserID Is Not Null, 1, 0) As `Liked`
        From `tblPosts` p 
        Join `tblUsers` u On p.UserID = u.UserID 
        Left Join `tblLikes` l On l.PostID = p.PostID And l.UserID = ?
        Where p.UserID In (Select `UserFollowerID` From `tblFollowers` Where `UserID` = ?)
        Order By p.PostID Desc 
        Limit ? 
        Offset ?",
        "None",
        "",
        "iiii",
        $_SESSION["UserID"], $_SESSION["UserID"], $loadlimit, $offset
    );
    // Check for exceptions
    if ((is_array($result) && array_key_exists("error", $result) && CatchDBError($result, true))) {
        // response 500 refers to a server side http response code, access failure
        http_response_code(500);
        // send failed request as a json with the errors
        echo(json_encode($result));
        exit();
    }

    $list = [];
    // append fetch to array
    // XSS PREVENTION ASWELL HERE
    while ($row = $result->fetch_assoc()) {
        $safe = [];
        foreach ($row as $key => $value) {
            if ($key == "Liked") {
                $safe[$key] = (bool)$value;
            }
            else if (is_string($value)) {
                $safe[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
            } else {
                $safe[$key] = $value;
            }
        }   
        $list[] = $safe;
    }
    // send data - count < loadlimit means theres no more posts
    echo(json_encode(["success" => true, "posts" => $list, "continue" => count($list) === $loadlimit]));
    $result->free();
?>