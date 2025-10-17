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
        
    }
?>