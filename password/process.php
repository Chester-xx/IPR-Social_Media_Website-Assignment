<?php
    // at one point this actually caused a 3 hour debug session, because i didnt start a session
    include_once("../includes/functions.php");
    StartSesh();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // sanitize and trim email input
        $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
        
        // filter validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /password/email.php?error=invalid");
            exit();
        }
        
        $conn = DBSesh();

        // check email exists in db
        $stmt = $conn->prepare("Select `UserID` From `tblUsers` Where `Email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // no user found
        if ($result->num_rows < 1) {
            $result->free();
            $stmt->close();
            mysqli_close($conn);
            header("Location: /password/email.php?error=noemail");
            exit();
        }
        
        // Get ID for querying in reset.php
        $uid = $result->fetch_assoc()["UserID"];
        
        // generate a token for url, hash and an expr date for db 
        // this contained unreadable text so i used binary to hexidecimal
        $token = bin2hex(random_bytes(6));
        $hash = password_hash($token, PASSWORD_DEFAULT);
        $exp = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        
        // Insert password change attempt to db
        $stmt = $conn->prepare("Insert Into `tblPasswordResets` (`Email`, `Token`, `Expires`) Values (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hash, $exp);
        $stmt->execute();
        
        CheckChangeFail($stmt, $conn, "/password/email.php?error=dbfail");
        
        // get the newly entered ResetID from execution
        $res = $conn->insert_id;
        
        //close db connection
        $stmt->close();
        mysqli_close($conn);
        
        // send email with link - WILL MOST LIKELY NOT WORK WITH NO PORT FORWARDING as localhosted server
        $reset = "http://localhost/password/reset.php?token=$token";
        $msg = "You requested to reset your @Connect password. Click on the link below to reset it\n" . $reset . "\nIf you did not request this, ignore this email.";
        mail($email, "Password Reset Request", $msg, "From: no-reply@connect.co.za\r\n");
        
        // redirect
        header("Location: /password/confirm.php?token=$token&user=$uid&reset=$res");
        exit();

    } else {

        // user tried to access page via get/entering the url specifically
        header("Location: /login/index.php");
        exit();

    }
?>