<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckLogIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/style.css">
    <title>Connect | Register</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Create an Account</h1> <br>
            <form action="register.php" method="post">
                <input type="email" name="reg_email" id="reg_email" placeholder="&#9993; Email" required>
                <?php 
                    Error("invalidemail", "Invalid Email"); 
                    Error("emailexists", "An account is already registered with the provided email");
                ?>
                <input type="text" name="reg_name" id="reg_name" placeholder="&#129534; Full Name" required>
                <input type="text" name="reg_username" id="reg_username" placeholder="&#9906; Username" required>
                <?php Error("usernameexists", "An account is already using that username"); ?>
                <input type="password" name="reg_password" id="reg_password" placeholder="&#128274; Password" required>
                <?php
                    Error("passwordshort", "Password must contain atleast 8 characters");
                    Error("passwordmatch", "Passwords don't match");
                ?>
                <input type="password" name="reg_confirm" id="reg_confirm" placeholder="&#10003; Confirm Password" required>
                <?php Error("nocredentials", "Please fill in all fields"); ?>
                <?php Error("dbfail", "Failed to create account, database connection failure"); ?> <br>
                <button type="submit">Sign Up</button> <br>
                <span>Have an account? <a href="/login/index.php">Log In</a></span>
            </form>
        </div>
    </div>
</body>
</html>