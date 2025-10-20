<?php
    include_once("../../includes/functions.php");
    // specify packet header for json access
    header("Content-Type: application/json");
    StartSesh();
    CheckNotLoggedIn();
    $BadReq = false;
    // Secure post - others cant like on behalf of other users
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get post input
        $pid = isset($_POST["PostID"]) ? intval($_POST["PostID"]) : 0;
        // No values
        if ($pid === 0) $BadReq = true;
        // Insert 
        if (!$BadReq) {
            $tmp = RunQuery(
                null,
                "Insert Into `tblLikes` (`PostID`, `UserID`) Values (?, ?)",
                "None",
                "",
                "ii",
                $pid, $_SESSION["UserID"]
            );
        }
        // Catch exceptions
        if (CatchDBError($tmp, true) || $BadReq) {
            // Response 500 refers to a server side http response code, access failure
            http_response_code($BadReq ? 400 : 500);
            // Send failed request as a json with the errors
            echo(json_encode($BadReq ? ["success" => false, "error" => ["message" => "User ID or Post ID not specified - Bad request", "code" => 400]] : $result));
            exit();
        }
        // Send data
        echo(json_encode(["success" => true]));
    }
?>