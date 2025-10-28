<?php
    // PASSWORD RESET PAGE | INPUT NEW PASSWORD
    include_once("../includes/functions.php");
    StartSesh();
    $conn = DBSesh();
    // Only allow via get for token parsing
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        // Sustain state even on refresh or input errors
        if (isset($_GET["token"]) && isset($_GET["user"])) {
            // Set session token so that the page can be reloaded
            $_SESSION["token"] = $_GET["token"];
            // Get the users ID
            $_SESSION["uid"] = trim(filter_input(INPUT_GET, "user", FILTER_SANITIZE_NUMBER_INT));
            // Get the users email for comparison
            $result = RunQuery(
                $conn,
                "Select `Email` From `tblUsers` Where `UserID` = ?",
                "Query",
                "/password/email.php?error=noemail",
                "i",
                $_SESSION["uid"]
            );
            // Catch any db errors
            CatchDBError($result);
            // Set the email in session for page reloads
            $_SESSION["email"] = $result->fetch_assoc()["Email"];
            // Get the unhashed token and reset id from the db
            $result = RunQuery(
                $conn,
                "Select `Token`, `ResetID` From `tblPasswordResets` Where `Email` = ? And `Expires` >= Now() Order By `Expires` Desc",
                "Query",
                "/password/email.php?error=expired",
                "s",
                $_SESSION["email"]
            );
            // Catch any db errors
            CatchDBError($result);
            // Set session reset id and hash
            $val = $result->fetch_assoc();
            $_SESSION["resid"] = $val["ResetID"];
            $_SESSION["hash"] = $val["Token"];
        }
        // Get email from the reset table to compare
        $result = RunQuery(
            $conn,
            "Select `Email` From `tblPasswordResets` Where `ResetID` = ?",
            "Query",
            "/password/email.php?error=noemail",
            "s",
            $_SESSION["resid"]
        );
        // Catch any db errors
        CatchDBError($result);
        // Set database email for comparison
        $db_email = $result->fetch_assoc()["Email"];
        // Check that the tokens match via password verify and also that the emails match, double checks to prevent easy password resets
        if ((!password_verify($_SESSION["token"], $_SESSION["hash"])) || ($_SESSION["email"] != $db_email)) {
            $result->free();
            $conn->close();
            header("Location: /password/email.php?error=noemail");
            exit();
        }
    }
    // Process on Post method and check that the submit button was pressed
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
        // Check that session vars are set
        if (!isset($_SESSION["token"], $_SESSION["uid"], $_SESSION["email"], $_SESSION["resid"], $_SESSION["hash"])) {
            header("Location: /password/email.php?error=noemail");
            exit();
        }
        // Get user input and filter
        $password = trim(filter_input(INPUT_POST, "change_password", FILTER_DEFAULT));
        $check = trim(filter_input(INPUT_POST, "change_confirm", FILTER_DEFAULT));
        // Check that they are not empty
        if (empty($password) || empty($check)) {
            header("Location: /password/reset.php?error=empty");
            exit();
        }
        // Are the passwords atleast 8 characters in length
        if (strlen($password) < 8 || strlen($check) < 8) {
            header("Location: /password/reset.php?error=passwordshort");
            exit();
        }
        // Are the passwords equivelant
        if ($password !== $check) {
            header("Location: /password/reset.php?error=passwordmatch");
            exit();
        }
        // Hash the new password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Update the users password in the db
        $tmp = RunQuery(
            $conn,
            "Update `tblUsers` Set `Password` = ? Where `UserID` = ?",
            "Change",
            "/password/email.php?error=dbfail",
            "si",
            $hash, $_SESSION["uid"]
        );
        // Catch any db errors
        CatchDBError($tmp);
        // Delete the password reset request from the table so it cannot be used again to reset the users password, even if not expired
        $tmp = RunQuery(
            $conn,
            "Delete From `tblPasswordResets` Where `ResetID` = ?",
            "Change",
            "None",
            "i",
            $_SESSION["resid"]
        );
        // Catch any db errors
        CatchDBError($tmp);
        $conn->close();
        // Unset session vars that we dont need anymore
        unset($_SESSION["token"], $_SESSION["uid"], $_SESSION["email"], $_SESSION["resid"], $_SESSION["hash"]);
        header("Location: /password/success.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Connect | Reset Password</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Reset Password</h1>
            <p>This will change your old password</p>
            <!-- Form for password and repeat input -->
            <form action="reset.php" method="post">
                <input type="password" name="change_password" id="change_password" placeholder="&#128274; New Password" required>
                <input type="password" name="change_confirm" id="change_confirm" placeholder="&#10003; Confirm New Password" required> 
                <?php
                    // Error output to user
                    Error("passwordshort", "New password must be at least 8 characters long");
                    Error("passwordmatch", "New passwords don't match");
                    Error("empty", "Please fill in all fields");
                ?> <br>
                <!-- Button to submit form -->
                <button type="submit" id="submit" name="submit">Confirm</button>
            </form>
        </div>
    </div>
</body>
</html>