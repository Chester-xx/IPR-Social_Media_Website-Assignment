<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get request id from call
    $rid = isset($_GET["rid"]) ? intval($_GET["rid"]) : 0;
    // no id specified, throw
    if ($rid === 0) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "Request ID not set: ", "code" => 403]]));
        exit();
    }
    // Get requester ID for insertion
    $result = RunQuery(
        null,
        "Delete From `tblFollowRequests` Where `RequestID` = ?",
        "Change",
        "",
        "i",
        $rid
    );
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Request deletion failed", "code" => 500]]));
        exit();
    }
    // Send json
    echo(json_encode(["success" => true]));
?>