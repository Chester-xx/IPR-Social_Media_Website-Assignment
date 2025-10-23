<?php
    include_once("../../includes/functions.php");
    // specify packet header for json access
    header("Content-Type: application/json");
    StartSesh();
    CheckNotLoggedIn();

    $offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;
    
    $result = RunQuery(
        null,
        "",
        "",
        "",
        ""

    );

    

?>