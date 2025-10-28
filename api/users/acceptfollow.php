<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get request id from Get method
    $rid = isset($_GET["rid"]) ? intval($_GET["rid"]) : 0;
    // No id specified, throw response code and escape
    if ($rid === 0) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "Request ID not set: ", "code" => 403]]));
        exit();
    }
    // Here i declare a connection instead of built in method from my function as i send many queries to the database here
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
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Requesting ID failed", "code" => 500]]));
        exit();
    }
    // Get the follow requester id and the follow accepter id
    $data = $result->fetch_assoc();
    $from = $data["FromID"];
    $to = $data["ToID"];
    // Check that it is infact the logged in user accepting the follow requester
    if ($to !== $_SESSION["UserID"]) {
        http_response_code(403);
        echo(json_encode(["success" => false, "error" => ["message" => "Unauthorized action", "code" => 403]]));
        exit();
    }
    // Delete the follow request row from other table
    $result = RunQuery(
        $conn,
        "Delete From `tblFollowRequests` Where `RequestID` = ?",
        "Change",
        "",
        "i",
        $rid
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Request deletion failed", "code" => 500]]));
        exit();
    }
    // Insert BOTH friend records into the table, i have a multi relation here, so there is for instance uid: 1-4 and 4-1, for friend list querying simplicity
    $result = RunQuery(
        $conn,
        "Insert Into `tblFollowers` (`UserID`, `UserFollowerID`) Values (?, ?)",
        "Change",
        "",
        "ii",
        $from, $to
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Insertion failed", "code" => 500]]));
        exit();
    }
    // Insert next friend record, but swap variables around
    $result = RunQuery(
        $conn,
        "Insert Into `tblFollowers` (`UserID`, `UserFollowerID`) Values (?, ?)",
        "Change",
        "",
        "ii",
        $to, $from
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Insertion failed", "code" => 500]]));
        exit();
    }
    // Send json
    echo(json_encode(["success" => true]));
?>