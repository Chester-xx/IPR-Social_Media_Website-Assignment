<?php
    // PASSWORD HAS SUCCESSFULLY BEEN RESET PAGE
    include_once("../includes/functions.php");
    StartSesh();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Connect | Reset Successful</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <!-- Successful password reset -->
        <div class="auth">
            <h1>Success</h1>
            <p>Password changed Successfully</p>
            <span>Continue to | <a href="/login/">Log In</a></span>
        </div>
    </div>
</body>
</html>