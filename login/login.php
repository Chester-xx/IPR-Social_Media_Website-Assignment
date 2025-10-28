<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckLogIn();
    // Request method post for processing login
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Sanitize input and trim
        $email = trim(filter_input(INPUT_POST, "login_email", FILTER_SANITIZE_EMAIL));
        $password = trim(filter_input(INPUT_POST, "login_password", FILTER_DEFAULT));
        // Validate email
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
        // Catch any db errors
        CatchDBError($result);
        // Get user ID and hashed password
        $data = $result->fetch_assoc();
        $uid = $data["UserID"];
        $hash = $data["Password"];
        // If passwords dont match verification
        if (!password_verify($password, $hash)) {
            header("Location: /login/index.php?error=invalid");
            exit();
        }
        $result->free();
        // Set session ID stating logged in
        $_SESSION["UserID"] = $uid;
        // Redirect finally
        header("Location: /dashboard/");
        exit();
    } else {
        // User tried to access page via get/entering the url specifically
        header("Location: /login/");
        exit();
    }
?>