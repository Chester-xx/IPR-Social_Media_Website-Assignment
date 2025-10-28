<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    // Specify packet header for json access
    header("Content-Type: application/json");
    // Get uid
    $uid = isset($_POST["ToID"]) ? intval($_POST["ToID"]) : 0;
    $msg = isset($_POST["Message"]) ? trim(htmlspecialchars($_POST["Message"], ENT_QUOTES, "UTF-8")) : "";
    // Check that uid has been set
    if ($uid === 0 || empty($msg)) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "User ID or Message not specified", "code" => 403]]));
        exit();
    }
    // Get all messages from both respective users to one another
    $result = RunQuery(
        null,
        "Insert Into `tblMessages` (`SenderID`, `ReceiverID`, `Message`) Values (?, ?, ?)",
        "None",
        "",
        "iis",
        $_SESSION["UserID"], $uid, $msg
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Message insertion failed", "code" => 500]]));
        exit();
    }
    // Send response
    echo(json_encode(["success" => true]));
?>