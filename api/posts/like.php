<?php
    include_once("../../includes/functions.php");
    header("Content-Type: application/json");
    StartSesh();
    CheckNotLoggedIn();
    // Secure post - others cant like on behalf of other users
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get post input
        $pid = isset($_POST["PostID"]) ? intval($_POST["PostID"]) : 0;
        // No values
        if ($pid === 0) {
            http_response_code(403);
            echo(json_encode(["success" => false, "error" => ["message" => "Post ID not set: ", "code" => 403]]));
            exit();
        }
        // Insert like
        $result = RunQuery(
            null,
            "Insert Into `tblLikes` (`PostID`, `UserID`) Values (?, ?)",
            "Change",
            "",
            "ii",
            $pid, $_SESSION["UserID"]
        );
        // check if duplicate like - remove | OR | check if theres an error
        if (is_array($result) && array_key_exists("success", $result) && !$result["success"]) {
            // Remove like if duplicate
            if (strpos(strtolower($result["error"]["message"]), "duplicate") !== false) {
                // Delete like, "unlike"
                $result = RunQuery(
                    null,
                    "Delete From `tblLikes` Where `PostID` = ? And `UserID` = ?",
                    "Change",
                    "",
                    "ii",
                    $pid, $_SESSION["UserID"]
                );
                // Check error
                if (!is_int($result) && (is_array($result) && array_key_exists("error", $result) && CatchDBError($result, true))) {
                    http_response_code(500);
                    echo(json_encode($result));
                    exit();
                }
                // Send unlike
                echo(json_encode(["success" => true, "liked" => false]));
                exit();
            } else {
                // DB error
                http_response_code(500);
                echo(json_encode($result));
                exit();
            }

        }
        // Check error
        else if (!is_int($result) && (is_array($result) && array_key_exists("error", $result) && CatchDBError($result, true))) {
            http_response_code(500);
            echo(json_encode($result));
            exit();
        }
        // Send like
        echo(json_encode(["success" => true, "liked" => true]));
    }
?>