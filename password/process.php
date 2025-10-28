<?php
    // Process password reset email input
    include_once("../includes/functions.php");
    StartSesh();
    // Only allow post
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // sanitize and trim email input
        $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
        // Filter validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /password/email.php?error=invalid");
            exit();
        }
        $conn = DBSesh();
        // Get matching emails
        $result = RunQuery(
            $conn,
            "Select `UserID` From `tblUsers` Where `Email` = ?",
            "None",
            "",
            "s",
            $email
        );
        // Catch any db errors
        CatchDBError($result);
        // No user found - custom function from ExpectedResult
        if ($result->num_rows < 1) {
            $conn->close();
            header("Location: /password/email.php?error=noemail");
            exit();
        }
        // Get ID for querying in reset.php
        $uid = $result->fetch_assoc()["UserID"];
        // Generate a token for url, hash and an expr date for db - this contained unreadable text so i used binary to hexidecimal
        $token = bin2hex(random_bytes(6));
        $hash = password_hash($token, PASSWORD_DEFAULT);
        $exp = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        // Insert password change attempt to db
        $result = RunQuery(
            $conn,
            "Insert Into `tblPasswordResets` (`Email`, `Token`, `Expires`) Values (?, ?, ?)",
            "Change",
            "/password/email.php?error=dbfail",
            "sss",
            $email, $hash, $exp
        );
        // Catch any db errors
        CatchDBError($result);
        // Get the newly entered ResetID from execution
        $res = $conn->insert_id;
        $conn->close();
        // Send email with link - WILL MOST LIKELY NOT WORK WITH NO PORT FORWARDING as localhosted server
        $reset = "http://localhost/password/reset.php?token=$token";
        $msg = "You requested to reset your @Connect password. Click on the link below to reset it\n" . $reset . "\nIf you did not request this, ignore this email.";
        mail($email, "Password Reset Request", $msg, "From: no-reply@connect.co.za\r\n");
        // Redirect
        header("Location: /password/confirm.php?token=$token&user=$uid&reset=$res");
        exit();
    } else {
        // User tried to access page via get
        header("Location: /login/index.php");
        exit();
    }
?>