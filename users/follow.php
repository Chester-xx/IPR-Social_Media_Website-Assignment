<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();

    $uid = isset($GET["uid"]) ? intval($GET["uid"]) : 0;
    
?>