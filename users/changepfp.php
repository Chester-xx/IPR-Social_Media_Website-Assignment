<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();


    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["photo-form"])) {

        if (isset($_FILES["pfp"]) && $_FILES["pfp"]["error"] !== UPLOAD_ERR_NO_FILE) {

            $file = $_FILES["pfp"];

            if ($file["error"] !== UPLOAD_ERR_OK) {
                header("Location: /dashboard/options.php?error=fileupload");
                exit();
            }

            if ($file["size"] > 10 * 1024 * 1024) {
                header("Location: /dashboard/options.php?error=filesize");
                exit();
            }

            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

            $name = $_SESSION["UserID"] . "." . $ext;

            if (!in_array($ext, ACCEPTED_PFP)) {
                header("Location: /dashboard/options.php?error=imageext");
                exit();
            }
            
            $conn = DBSesh();

            $result = RunQuery(
                $conn,
                "Select `PFP` From `tblUsers` Where `UserID` = ?",
                "None",
                "",
                "i",
                $_SESSION["UserID"]
            );
            CatchDBError($result);

            $oldfile = $result->fetch_assoc()["PFP"];
            if ($oldfile && $oldfile !== "default.jpg" && file_exists("../content/profiles/" . $oldfile)) {
                unlink(PFP . $oldfile);
            }

            if (!move_uploaded_file($file["tmp_name"], PFP . $name)) {
                 header("Location: /dashboard/options.php?error=fileupload");
                exit();
            }
            // Update users pfp file name in db
            $tmp = RunQuery(
                null,
                "Update `tblUsers` Set `PFP` = ? Where `UserID` = ?",
                "None",
                "",
                "si",
                $name, $_SESSION["UserID"]
            );
            CatchDBError($tmp);
        }

        header("Location: /dashboard/profile.php");
        exit();

    } else {
        header("Location: /dashboard/options.php");
        exit();
    }
?>