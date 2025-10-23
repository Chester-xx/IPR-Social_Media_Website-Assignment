<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // Get all friend data from sed user
    $result = RunQuery(
        null,
        "Select u.UserID, u.Username, u.PFP From `tblUsers` u  Inner Join `tblFollowers` f On u.UserID = f.UserFollowerID Where f.UserID = ?",
        "None",
        "",
        "i",
        $_SESSION["UserID"]
    );
    // Catch exceptions
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Could not establish a connection with the database", "code" => 500]]));
        exit();
    }
    $list = [];
    // append each user, xss prevent username as it will be outputted
    while ($row = $result->fetch_assoc()) {
        $list[] = ["Username" => htmlspecialchars($row["Username"], ENT_QUOTES, "UTF-8"), "PFP" => $row["PFP"]];
    }
    // Send user list
    echo(json_encode(["success" => true, "users" => $list]));
?>