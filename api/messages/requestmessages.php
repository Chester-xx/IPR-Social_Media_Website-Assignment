<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get uid
    $uid = isset($_POST["UserID"]) ? intval($_POST["UserID"]) : 0;
    // Check that uid has been set
    if ($uid === 0) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "User ID not specified", "code" => 403]]));
        exit();
    }
    // Get all messages from both respective users to one another
    $result = RunQuery(
        null,
        "Select `SenderID`, `ReceiverID`, `Message`, `SentAt` 
                From `tblMessages` 
                Where (`SenderID` = ? And `ReceiverID` = ?) Or (`SenderID` = ? And `ReceiverID` = ?)
                Order By `SentAt` Asc",
        "None",
        "",
        "iiii",
        $_SESSION["UserID"], $uid, $uid, $_SESSION["UserID"]
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Message fetch failed", "code" => 500]]));
        exit();
    }
    // get messages
    $msgs = [];
    while ($row = $result->fetch_assoc()) {
        $msgs[] = [
            "SenderID" => $row["SenderID"],
            "ReceiverID" => $row["ReceiverID"],
            "Message" => htmlspecialchars($row["Message"], ENT_QUOTES, "UTF-8"),
            "SentAt" => $row["SentAt"]
        ];
    }
    // if there are no messages js handles it
    if (empty($msgs)) {
        echo(json_encode(["success" => true, "messages" => []]));
        exit();
    }
    // Send data
    echo(json_encode(["success" => true, "messages" => $msgs]));
?>