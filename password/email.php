<?php
    include_once("../includes/functions.php");
    StartSesh();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/style.css">
    <title>Connect | Forgot Password</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Forgot Your Password?</h1> <br>
            <form action="/password/process.php" method="post">
                <input type="email" name="email" id="email" placeholder="&#9993; Enter your email" required> <br>
                <?php 
                    Error("invalid", "Invalid email entered");
                    Error("noemail", "Email is not linked to a user account");
                    Error("dbfail", "Failed to connect to the database");
                    Error("expired", "Your request has expired, please try again");
                ?>
                <button type="submit">Reset Password</button> <br> <br>
                <span><a href="/login/index.php">Log In</a> | <a href="/login/regform.php">Sign Up</a></span>
            </form>
        </div>
    </div>
</body>
</html>