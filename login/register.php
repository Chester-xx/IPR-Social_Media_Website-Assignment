<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckLogIn();
    // did the user get redirected to this page via regform post
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // passed values are not set
        if (!isset($_POST["reg_email"], $_POST["reg_username"], $_POST["reg_password"], $_POST["reg_confirm"], $_POST["reg_name"])) {
            header("Location: /login/regform.php?error=nocredentials");
            exit();
        }
        // passed values are set but empty strings
        else if (empty($_POST["reg_email"]) || empty($_POST["reg_username"]) || empty($_POST["reg_password"]) || empty($_POST["reg_confirm"]) || empty($_POST["reg_name"])) {
            header("Location: /login/regform.php?error=nocredentials");
            exit();
        }
        // declare vars
        $email = trim(filter_input(INPUT_POST, "reg_email", FILTER_SANITIZE_EMAIL));
        $username = trim(filter_input(INPUT_POST, "reg_username", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $name = trim(filter_input(INPUT_POST, "reg_name", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $password = trim(filter_input(INPUT_POST, "reg_password", FILTER_DEFAULT));
        $check = trim(filter_input(INPUT_POST, "reg_confirm", FILTER_DEFAULT));
        // is the email valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /login/regform.php?error=invalidemail");
            exit();
        }
        // are the passwords atleast 8 characters in length
        if (strlen($password) < 8 || strlen($check) < 8) {
            header("Location: /login/regform.php?error=passwordshort");
            exit();
        }
        // are the passwords equivelant
        if ($password !== $check) {
            header("Location: /login/regform.php?error=passwordmatch");
            exit();
        }
        $conn = DBSesh();
        // Get unique email for checking
        $result = RunQuery(
            $conn,
            "Select 1 From `tblUsers` Where `Email` = ? Limit 1",
            "NoQuery",
            "/login/regform.php?error=emailexists",
            "s",
            $email
        );
        // Get unique username for checking
        $result = $result = RunQuery(
            $conn,
            "Select 1 From `tblUsers` Where `Username` = ? Limit 1",
            "NoQuery",
            "/login/regform.php?error=usernameexists",
            "s",
            $username
        );
        // hash password with default algo, insert into db
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // insert new user data
        $tmp = RunQuery(
            $conn,
            "Insert Into `tblUsers` (`Email`, `Username`, `FullName`, `Password`) Values (?, ?, ?, ?)",
            "Change",
            "/login/regform.php?error=dbfail",
            "ssss",
            $email, $username, $name, $hash
        );
        $uid = $conn->insert_id;
        // one time access flag for createsuccess.php
        $_SESSION["acc_created"] = true;
        $conn->close();
        header("Location: /login/success.php?id=$uid");
        exit();
    } else {
        // user tried to access page via get/entering the url specifically
        header("Location: /login/index.php");
        exit();
    }
?>