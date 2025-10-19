<?php
    include_once("../../includes/functions.php");
    // specify packet header for json access
    header("Content-Type: application/json");
    // errors
    try {
        StartSesh();
        CheckNotLoggedIn();
        $conn = DBSesh();
        // failed to connect database to server
        if (!$conn) throw new Exception("Failed to connect to the database");
        // how many posts we will send at once
        $loadlimit = 10;
        // the position of posts in the db
        $offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;
        $stmt = $conn->prepare("Select u.Username, u.PFP, p.Content, p.Image, p.CreateTime From tblPosts p Join tblUsers u On p.UserID = u.UserID Order By p.PostID Desc Limit ? Offset ?");
        // failed to prep qry
        if (!$stmt) throw new Exception("Failed to prepare query: " . $conn->error);
        $stmt->bind_param("ii", $loadlimit, $offset);
        // failed to bind and exec qry
        if (!$stmt->execute()) throw new Exception("Query execution failed: " . $conn->error);
        $result = $stmt->get_result();
        // failed to fetch res
        if (!$result) throw new Exception("Result failed to fetch: " . $conn->error);
        $list = [];
        // append fetch to array
        // XSS PREVENTION ASWELL HERE
        while ($row = $result->fetch_assoc()) {
            $safe = [];
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    $safe[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
                } else {
                    $safe[$key] = $value;
                }
            }
            $list[] = $safe;
        }
        // send data - count < loadlimit means theres no more posts
        echo(json_encode(["success" => true, "posts" => $list, "continue" => count($list) === $loadlimit]));
        $result->free();
        $stmt->close();
        mysqli_close($conn);
    } catch (Exception $e) {
        // response 500 refers to a server side http response code, access failure
        http_response_code(500);
        // send failed request as a json with the errors
        echo(json_encode(["success" => false, "error" => $e->getMessage()]));
    }
?>