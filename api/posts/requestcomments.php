<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get post id from Get method
    $pid = $_GET["PostID"] ? intval($_GET["PostID"]) : 0;
    // Check if the post id is valid or not
    if ($pid === 0) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "Post ID not set: ", "code" => 403]]));
        exit();
    }
    // Get all comments on specific post
    $result = RunQuery(
        null,
        "Select u.Username, u.PFP, c.Content, c.CreateTime
                From tblComments c
                Join tblUsers u On c.UserID = u.UserID
                Where c.PostID = ?
                Order By c.CommentID Asc",
        "None",
        "",
        "i",
        $pid
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
    // XSS prevention
    while ($row = $result->fetch_assoc()) {
        $safe = [];
        foreach ($row as $key => $value) {
            // Escape characters to prevent xss if its a string
            if (is_string($value)) {
                $safe[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
            } else {
                $safe[$key] = $value;
            }
        }   
        $list[] = $safe;
    }
    // send data
    echo(json_encode(["success" => true, "comments" => $list, "none" => empty($list)]));
    $result->free();
?>