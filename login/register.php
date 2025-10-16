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
        
        // prepare sql statements for secure querying | here i am making sure the users email doesnt already exist in the db
        $stmt = $conn->prepare("Select 1 From `tblUsers` Where `Email` = ? Limit 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // if email exists
        CheckNoQueryResult($result, $stmt, $conn, "/login/regform.php?error=emailexists");

        // query if the username already exists
        $stmt = $conn->prepare("Select 1 From `tblUsers` Where `Username` = ? Limit 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        CheckNoQueryResult($result, $stmt, $conn, "/login/regform.php?error=usernameexists");
        
//      // NEED TO implement pfp insertion with file management
        
        // hash password with default algo, insert into db
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("Insert Into `tblUsers` (`Email`, `Username`, `FullName`, `Password`) Values (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $username, $name, $hash);
        $stmt->execute();

        CheckChangeFail($stmt, $conn, "/login/regform.php?error=dbfail");

        $uid = $conn->insert_id;

        $stmt->close();
        mysqli_close($conn);

        // one time access flag for createsuccess.php
        $_SESSION["acc_created"] = true;
        // call successful creation page
        header("Location: /login/success.php?id=$uid");
        exit();

    } else {
        
        // user tried to access page via get/entering the url specifically
        header("Location: /login/index.php");
        exit();
    
    }

?>