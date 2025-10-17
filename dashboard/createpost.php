<?php
    echo $_SERVER["HTTP_REFERER"];
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();

    // check post access and post button was pressed
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_submit"])) {
        
        $uid = $_SESSION["UserID"];
        $text = trim(filter_input(INPUT_POST, "post_text", FILTER_SANITIZE_STRING));
        $name = null;

        if (empty($text)) {
            header("Location: " . $_SERVER["HTTP_REFERER"] . "?error=notext");
            exit();
        }

        echo "<pre>";
        print_r($_FILES);
        echo "</pre>";
        // exit();

        if (isset($_FILES["post_img"]) && $_FILES["post_img"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES["post_img"];

            if ($file["error"] !== UPLOAD_ERR_OK) {
                header("Location: /dashboard/error.php?error=file");
                exit();
            }

            if ($file["size"] > 15 * 1024 * 1024) {
                header("Location: /dashboard/error.php?error=filesize");
                exit();
            }

            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            // name example: 12_20251005134516.png, meaning the file process time, in the database it may be a different time stamp
            $name = $uid . "_" . date("YmdHis") . "." . $ext; 

            if (!in_array($ext, ACCEPTED_FILES)) {
                header("Location: /dashboard/error.php?error=extension");
                exit();
            }

            // did we successfully move the uploaded image from temporary to our dedicated file location
            if (!move_uploaded_file($file["tmp_name"], POST . $name)) {
                header("Location: /dashboard/error.php?error=filetransfer");
                exit();
            }
        }

        $conn = DBSesh();
        $stmt = $conn->prepare("Insert Into `tblPosts` (`UserID`, `Content`, `Image`) Values (?, ?, ?)");
        $stmt->bind_param("iss", $uid, $text, $name);
        $stmt->execute();

        CheckChangeFail($stmt, $conn, "/dashboard/error.php?error=dbfail");

        $stmt->close();
        mysqli_close($conn);

        header("Location: " . $_SERVER["HTTP_REFERER"]);
        exit();

    } else {
        // rq by get/enter url denial
        header("Location: /login/");
        exit();
    }
?>