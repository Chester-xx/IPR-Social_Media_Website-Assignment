<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckLogIn();
    
    // request method post for processing login
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        
        // sanitize input and trim
        $email = trim(filter_input(INPUT_POST, "login_email", FILTER_SANITIZE_EMAIL));
        $password = trim(filter_input(INPUT_POST, "login_password", FILTER_DEFAULT));
        
        // validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /login/index.php?error=invalid");
            exit();
        }
        
        // check if input is empty
        if (empty($email) || empty($password)) {
            header("Location: /login/index.php?error=empty");
            exit();
        }
        
        $conn = DBSesh();

        $stmt = $conn->prepare("Select `UserID`, `Password` From `tblUsers` Where `Email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        CheckQueryResult($result, $stmt, $conn, "/login/index.php?error=invalid");

        // get user ID and hashed password
        $data = $result->fetch_assoc();
        $uid = $data["UserID"];
        $hash = $data["Password"];
        
        // if passwords dont match verification
        if (!password_verify($password, $hash)) {
            $stmt->close();
            mysqli_close($conn);
            header("Location: /login/index.php?error=invalid");
            exit();
        }
        
        // close db connection | free result mem | close statement
        $result->free();
        $stmt->close();
        mysqli_close($conn);
        
        // set session ID stating logged in
        $_SESSION["UserID"] = $uid;
        
        // redirect finally
        header("Location: /dashboard/");
        exit();

    } else {
        
        // user tried to access page via get/entering the url specifically
        header("Location: /login/");
        exit();
        
    }

?>