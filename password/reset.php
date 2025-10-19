<?php
    include_once("../includes/functions.php");
    StartSesh();
    $conn = DBSesh();
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        // sustain state even on refresh or input errors
        if (isset($_GET["token"]) && isset($_GET["user"])) {
            $_SESSION["token"] = $_GET["token"];
            $_SESSION["uid"] = trim(filter_input(INPUT_GET, "user", FILTER_SANITIZE_NUMBER_INT));
            $result = RunQuery(
                $conn,
                "Select `Email` From `tblUsers` Where `UserID` = ?",
                "Query",
                "/password/email.php?error=noemail",
                "i",
                $_SESSION["uid"]
            );
            $_SESSION["email"] = $result->fetch_assoc()["Email"];
            $result = RunQuery(
                $conn,
                "Select `Token`, `ResetID` From `tblPasswordResets` Where `Email` = ? And `Expires` >= Now() Order By `Expires` Desc",
                "Query",
                "/password/email.php?error=expired",
                "s",
                $_SESSION["email"]
            );
            $val = $result->fetch_assoc();
            $_SESSION["resid"] = $val["ResetID"];
            $_SESSION["hash"] = $val["Token"];
        }
        $result = RunQuery(
            $conn,
            "Select `Email` From `tblPasswordResets` Where `ResetID` = ?",
            "Query",
            "/password/email.php?error=noemail",
            "s",
            $_SESSION["resid"]
        );
        $db_email = $result->fetch_assoc()["Email"];
        if ((!password_verify($_SESSION["token"], $_SESSION["hash"])) || ($_SESSION["email"] != $db_email)) {
            $result->free();
            $conn->close();
            header("Location: /password/email.php?error=noemail");
            exit();
        }
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
        if (!isset($_SESSION["token"], $_SESSION["uid"], $_SESSION["email"], $_SESSION["resid"], $_SESSION["hash"])) {
            header("Location: /password/email.php?error=noemail");
            exit();
        }
        $password = trim(filter_input(INPUT_POST, "change_password", FILTER_DEFAULT));
        $check = trim(filter_input(INPUT_POST, "change_confirm", FILTER_DEFAULT));
        if (empty($password) || empty($check)) {
            header("Location: /password/reset.php?error=empty");
            exit();
        }
        // are the passwords atleast 8 characters in length
        if (strlen($password) < 8 || strlen($check) < 8) {
            header("Location: /password/reset.php?error=passwordshort");
            exit();
        }
        // are the passwords equivelant
        if ($password !== $check) {
            header("Location: /password/reset.php?error=passwordmatch");
            exit();
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $tmp = RunQuery(
            $conn,
            "Update `tblUsers` Set `Password` = ? Where `UserID` = ?",
            "Change",
            "/password/email.php?error=dbfail",
            "si",
            $hash, $_SESSION["uid"]
        );
        $tmp = RunQuery(
            $conn,
            "Delete From `tblPasswordResets` Where `ResetID` = ?",
            "Change",
            "None",
            "i",
            $_SESSION["resid"]
        );
        $conn->close();
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
    <link rel="stylesheet" href="/style/style.css">
    <title>Connect | Reset Password</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Reset Password</h1>
            <p>This will change your old password</p>
            <form action="reset.php" method="post">
                <input type="password" name="change_password" id="change_password" placeholder="&#128274; New Password" required>
                <input type="password" name="change_confirm" id="change_confirm" placeholder="&#10003; Confirm New Password" required> 
                <?php
                    Error("passwordshort", "New password must be at least 8 characters long");
                    Error("passwordmatch", "New passwords don't match");
                    Error("empty", "Please fill in all fields");
                ?> <br>
                <button type="submit" id="submit" name="submit">Confirm</button>
            </form>
        </div>
    </div>
</body>
</html>