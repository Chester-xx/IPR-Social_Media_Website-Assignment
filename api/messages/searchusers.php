<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get the query from get method via api call
    $qry = isset($_GET["query"]) ? htmlspecialchars(trim($_GET["query"]), ENT_QUOTES, "UTF-8") : "";
    // Check empty query
    if (empty($qry)) {
        echo(json_encode(["success" => false, "error" => ["message" => "No query tags specified", "code" => 403]]));
        exit();
    }
    // Get the top result for a user in the database matching query
    $result = RunQuery(
        null,
        "Select `UserID`, `Username`, `PFP` From `tblUsers` Where `Username` Like ? Limit 1",
        "None",
        "",
        "s",
        "%$qry%"
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Could not establish a connection with the database", "code" => 500]]));
        exit();
    }
    // Check that there is an actual result
    if ($result->num_rows < 1) {
        echo(json_encode(["success" => false, "error" => ["message" => "No user found", "code" => 404]]));
        exit();
    }
    // Ensure the user cant converse with themselves lol
    $user = $result->fetch_assoc();
    if ($user["UserID"] === $_SESSION["UserID"]) {
        echo(json_encode(["success" => false, "error" => ["message" => "No user found", "code" => 404]]));
        exit();
    }
    // Send data
    echo(json_encode(["success" => true, "user" => $user]));
?>