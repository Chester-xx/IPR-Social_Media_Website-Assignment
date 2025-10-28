<?php
    include_once("../../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    header("Content-Type: application/json");
    // This query alone took me like 4 hours, the struggle is real - had to create a test environment just to get it working
    // I collect each individual conversation where either the user is a message sender or reciever
    // I get the latest message from either side for a preview
    // I return the other user info (not the logged in users) PFP and username
    // And then descending basically meaning the conversations are top to bottom newest, so all conversations
    $result = RunQuery(
        null,
        "Select u.UserID, u.Username, u.PFP, m.Message As `LastMessage`, m.SentAt As LastDate
                From `tblMessages` m
                Inner Join (
                    Select 
                        Least(`SenderID`, `ReceiverID`) As `u1`,
                        Greatest(`SenderID`, `ReceiverID`) As `u2`,
                        Max(SentAt) As `LatestMsg` 
                    From `tblMessages`
                    Group By Least(`SenderID`, `ReceiverID`), Greatest(`SenderID`, `ReceiverID`)
                )
                Latest On 
                    Least(m.SenderID, m.ReceiverID) = Latest.u1 
                    And Greatest(m.SenderID, m.ReceiverID) = Latest.u2 
                    And m.SentAt = Latest.LatestMsg
                Inner Join `tblUsers` u On (
                    Case
                        When m.SenderID = ? Then m.ReceiverID
                        Else m.SenderID
                    End
                ) = u.UserID
                Where ? In (m.SenderID, m.ReceiverID) 
                Order By m.SentAt Desc",
        "None",
        "",
        "ii",
        $_SESSION["UserID"], $_SESSION["UserID"]
    );
    // Catch any db errors
    if (CatchDBError($result, true)) {
        http_response_code(500);
        echo(json_encode(["success" => false, "error" => ["message" => "Comment insertion failed", "code" => 500]]));
        exit();
    }
    // Append conversation array with each individual data points
    $conv = [];
    while ($row = $result->fetch_assoc()) {
        $conv[] = [
            "UserID" => $row["UserID"],
            "Username" => htmlspecialchars($row["Username"], ENT_QUOTES, "UTF-8"),
            "PFP" => $row["PFP"],
            "LastMessage" => htmlspecialchars($row["LastMessage"], ENT_QUOTES, "UTF-8"),
            "LastDate" => $row["LastDate"]
        ];
    }
    // Check if there are no conversations from the query
    if (empty($conv)) {
        echo(json_encode(["success" => false, "error" => ["message" => "No conversations listed", "code" => 500]]));
        exit();
    }
    // Send data
    echo(json_encode(["success" => true, "conversations" => $conv]));
?>