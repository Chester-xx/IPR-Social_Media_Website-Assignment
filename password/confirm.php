<?php
    // We dont check login here because i want a logged in user to be able to use these page to reset their password
    // the function CheckLogIn redirects users to dashboard if a session ID has been set, which i dont want
    include_once("../includes/functions.php");
    StartSesh();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Connect | Confirm Reset</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Confirm Reset</h1> <br>
            <p>A link has been sent to your email.<br>Click on it to reset your password.<br>Time left: <span id="cd"></span></p>
            <!-- here i added a link for testing, as a mailing server would not work in a localhost instance due to no port forwarding -->
            <p>For testing purposes | <a href="/password/reset.php?token=<?php echo(htmlspecialchars($_GET['token']) . "&user=" . htmlspecialchars($_GET["user"])); ?>">Reset Link</a></p>
            <span><a href="/login/index.php">Log In</a> | <a href="/login/regform.php">Sign Up</a></span>
        </div>
    </div>
    <!-- Getting expiry time -->
    <?php
        // Inp + sanitize
        $res = trim(filter_input(INPUT_GET, "reset", FILTER_SANITIZE_NUMBER_INT));
        // Fetch timestamp from db
        $result = RunQuery(
            null,
            "Select `Expires` From `tblPasswordResets` Where `ResetID` = ?",
            "Query",
            "/password/email.php?error=dbfail",
            "i",
            $res
        );
        CatchDBError($result);
        // Calc remaining time by subtracting current from expiry
        $expr = strtotime($result->fetch_assoc()["Expires"]) - time();
        if ($expr < 0) $expr = 0;
    ?>
    <!-- Countdown from 5 minutes -->
    <script>
        let time = <?php echo $expr ?>;
        let cd = setInterval(function() {
            let min = Math.floor(time / 60); let sec = time % 60;
            if (sec < 10) sec = "0" + sec;
            document.getElementById("cd").textContent = min + ":" + sec; time--;
            if (time < 0) { clearInterval(cd); document.getElementById("cd").textContent = "Expired"; }
        }, 1000);
    </script>
</body>
</html>