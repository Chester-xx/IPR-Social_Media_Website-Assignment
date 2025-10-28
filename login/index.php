<?php
    // LOGIN PAGE
    include_once("../includes/functions.php");
    StartSesh();
    CheckLogIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Connect | Log In</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Log In</h1> <br>
            <!-- Form for email and password input -->
            <form action="login.php" method="post">
                <input type="email" name="login_email" id="login_email" placeholder="&#9993; Email" required>
                <input type="password" name="login_password" id="login_password" placeholder="&#x1F512; Password" required> <br>
                <?php
                    // Error outputs
                    // i chose to only send a mixed signal of either or for password + email creds
                    Error("invalid", "Password or email is incorrect");
                    Error("empty", "Please fill in all fields");
                ?>
                <!-- Sign in option, sign up or reset an account password -->
                <button type="submit">Sign In</button> <br>
                <span>Don't have an account? <a href="/login/regform.php">Sign Up</a></span> <br>
                <span><a href="/password/email.php">Forgot Password?</a></span>
            </form>
        </div>
    </div>
</body>
</html>