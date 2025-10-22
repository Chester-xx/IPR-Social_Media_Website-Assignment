<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get post id and content from post method
    $pid = isset($_POST["PostID"]) ? intval($_POST["PostID"]) : 0;
    $content = isset($_POST["Content"]) ? htmlspecialchars($_POST["Content"], ENT_QUOTES, "UTF-8") : "";
    // no id specified, throw
    if ($pid === 0 || empty($content)) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "Post ID not set: ", "code" => 403]]));
        exit();
    }
    // Insert comment into db
    $tmp = RunQuery(
        null,
        "Insert Into `tblComments` (`PostID`, `UserID`, `Content`) Values (?, ?, ?)",
        "Change",
        "",
        "iis",
        $pid, $_SESSION["UserID"], $content
    );
    // Check for errors
    if (!is_int($tmp) && (is_array($tmp) && array_key_exists("error", $tmp) && CatchDBError($tmp, true))) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Comment insertion failed", "code" => 500]]));
        exit();
    }
    // Send json
    echo(json_encode(["success" => true]));
?>