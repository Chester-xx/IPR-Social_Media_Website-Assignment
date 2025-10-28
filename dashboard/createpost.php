<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    // Check post access and post button was pressed
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_submit"])) {
        // Get important processing variables
        $uid = $_SESSION["UserID"];
        // trim white space and spacing at the front and end of strings, also filter input via sanitization method (depricated as i realized later)
        $text = trim(filter_input(INPUT_POST, "post_text", FILTER_SANITIZE_STRING));
        $name = null;
        // Check that the post text is not null
        if (empty($text)) {
            // redirect back to the page where they were posting from and throw a display error
            header("Location: " . $_SERVER["HTTP_REFERER"] . "?error=notext");
            exit();
        }
        // Check if a file has been uploaded and there is no error in its transmission
        if (isset($_FILES["post_img"]) && $_FILES["post_img"]["error"] !== UPLOAD_ERR_NO_FILE) {
            // Get file from Files method
            $file = $_FILES["post_img"];
            // Check for file errors
            if ($file["error"] !== UPLOAD_ERR_OK) {
                header("Location: /dashboard/error.php?error=file");
                exit();
            }
            // Check that the file is not too large (15MB)
            if ($file["size"] > 15 * 1024 * 1024) {
                header("Location: /dashboard/error.php?error=filesize");
                exit();
            }
            // Get the file extension from pathinfo
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            // File naming (uid_timestamp.ext) - name example: 12_20251005134516.png, meaning the file process time, in the database it may be a different time stamp due to computation
            $name = $uid . "_" . date("YmdHis") . "." . $ext; 
            // Check that the extension is in my allowed extensions list defined in functions.php
            if (!in_array($ext, ACCEPTED_FILES)) {
                header("Location: /dashboard/error.php?error=extension");
                exit();
            }
            // Did we successfully move the uploaded image from temporary to our dedicated file location
            if (!move_uploaded_file($file["tmp_name"], POST . $name)) {
                header("Location: /dashboard/error.php?error=filetransfer");
                exit();
            }
        }
        // Insert a new post into the db
        $tmp = RunQuery(
            null,
            "Insert Into `tblPosts` (`UserID`, `Content`, `Image`) Values (?, ?, ?)",
            "Change",
            "/dashboard/error.php?error=dbfail",
            "iss",
            $uid, $text, $name
        );
        // Catch any db errors
        CatchDBError($tmp);
        header("Location: " . $_SERVER["HTTP_REFERER"]);
        exit();
    } else {
        // Deny post creation if the user is not logged in or the correct posting method, rq by get/enter url denial
        header("Location: /login/");
        exit();
    }
?>