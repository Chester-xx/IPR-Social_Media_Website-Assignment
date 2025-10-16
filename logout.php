<?php
    // this file manages how the user logs out, creating a session, checking if there is an active session, destroying it and unsetting the users ID
    include_once("./includes/functions.php");
    StartSesh();
    if (session_status() === PHP_SESSION_ACTIVE) {
        unset($_SESSION["UserID"]);
        session_destroy();
    }
    header("Location: /login/");
    exit();
?>