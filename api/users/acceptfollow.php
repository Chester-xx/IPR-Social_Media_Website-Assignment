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
    $conn = DBSesh();
    // Get requester ID for insertion
    $result = RunQuery(
        $conn,
        "Select `FromID`, `ToID` From `tblFollowRequests` Where `RequestID` = ?",
        "Query",
        "",
        "i",
        $rid
    );
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Requesting ID failed", "code" => 500]]));
        exit();
    }
    $data = $result->fetch_assoc();
    $from = $data["FromID"];
    $to = $data["ToID"];
    if ($to !== $_SESSION["UserID"]) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "Unauthorized action", "code" => 403]]));
        exit();
    }
    // Delete request
    $result = RunQuery(
        $conn,
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
    // Insert BOTH friend records
    $result = RunQuery(
        $conn,
        "Insert Into `tblFollowers` (`UserID`, `UserFollowerID`) Values (?, ?)",
        "Change",
        "",
        "ii",
        $from, $to
    );
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Insertion failed", "code" => 500]]));
        exit();
    }
    $result = RunQuery(
        $conn,
        "Insert Into `tblFollowers` (`UserID`, `UserFollowerID`) Values (?, ?)",
        "Change",
        "",
        "ii",
        $to, $from
    );
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Insertion failed", "code" => 500]]));
        exit();
    }
    // Send json
    echo(json_encode(["success" => true]));
?>