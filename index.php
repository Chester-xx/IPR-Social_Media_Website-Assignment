<?php
	// This file manages direct access to the domain name,
	// in this context 'localhost', or specific reference to a port or 'localhost/index.php'
	include_once("./includes/functions.php");
	// we create a session and check if a session id variable has been set, which will redirect the user to the dashboard if they are 'logged in'
	StartSesh();
	CheckLogIn();
	// otherwise we redirect them to the login page, which is the same as '/login/index.php'
	header("Location: /login/");
	exit();
?>