<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get username query from Get method, this will be compared with %qry% so anything containing the query will be sent, limited to 5 results
    $qry = isset($_GET["query"]) ? htmlspecialchars(trim($_GET["query"]), ENT_QUOTES, "UTF-8") : "";
    // Check empty query
    if (empty($qry)) {
        echo(json_encode(["success" => false, "error" => ["message" => "No query tags specified", "code" => 403]]));
        exit();
    }
    // Get a limit of 5 users which are 'like' qry from % to %
    $result = RunQuery(
        null,
        "Select `Username`, `PFP` From `tblUsers` Where `Username` Like ? Limit 5",
        "None",
        "",
        "s",
        "%$qry%"
    );
    // Catch exceptions
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Could not establish a connection with the database", "code" => 500]]));
        exit();
    }
    $list = [];
    // Append each user, xss prevent username as it will be outputted
    while ($row = $result->fetch_assoc()) {
        $list[] = ["Username" => htmlspecialchars($row["Username"], ENT_QUOTES, "UTF-8"), "PFP" => $row["PFP"]];
    }
    // Send user list
    echo(json_encode(["success" => true, "users" => $list]));
?>