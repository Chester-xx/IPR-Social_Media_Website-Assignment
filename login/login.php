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
        // Check if input is empty
        if (empty($email) || empty($password)) {
            header("Location: /login/index.php?error=empty");
            exit();
        }
        // Get user id + password, that matches email
        $result = RunQuery(
            null,
            "Select `UserID`, `Password` From `tblUsers` Where `Email` = ?",
            "Query",
            "/login/index.php?error=invalid",
            "s",
            $email
        );
        // get user ID and hashed password
        $data = $result->fetch_assoc();
        $uid = $data["UserID"];
        $hash = $data["Password"];
        // if passwords dont match verification
        if (!password_verify($password, $hash)) {
            header("Location: /login/index.php?error=invalid");
            exit();
        }
        $result->free();
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