<?php
    // Process a profile photo change from user settings
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    // Check method via post and that the photo form has been submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["photo-form"])) {
        // check that the file has been uploaded and there is no transmission error
        if (isset($_FILES["pfp"]) && $_FILES["pfp"]["error"] !== UPLOAD_ERR_NO_FILE) {
            // Get the file
            $file = $_FILES["pfp"];
            // File upload error
            if ($file["error"] !== UPLOAD_ERR_OK) {
                header("Location: /dashboard/options.php?error=fileupload");
                exit();
            }
            // File size is too large (10MB)
            if ($file["size"] > 10 * 1024 * 1024) {
                header("Location: /dashboard/options.php?error=filesize");
                exit();
            }
            // new pfp file name and ext
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $name = $_SESSION["UserID"] . "." . $ext;
            // Check that the extension is in the allowed list
            if (!in_array($ext, ACCEPTED_PFP)) {
                header("Location: /dashboard/options.php?error=imageext");
                exit();
            }
            $conn = DBSesh();
            // Get the old pfp file name for deletion
            $result = RunQuery(
                $conn,
                "Select `PFP` From `tblUsers` Where `UserID` = ?",
                "None",
                "",
                "i",
                $_SESSION["UserID"]
            );
            // Catch any db errors
            CatchDBError($result);
            // Delete the old profile image, unless its the default photo
            $oldfile = $result->fetch_assoc()["PFP"];
            if ($oldfile && $oldfile !== "default.jpg" && file_exists("../content/profiles/" . $oldfile)) {
                unlink(PFP . $oldfile);
            }
            // Move from temp to content/profiles
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
            // Catch any db errors
            CatchDBError($tmp);
        }
        // redirect to profile page
        header("Location: /dashboard/profile.php");
        exit();
    } else {
        // Not allowed
        header("Location: /dashboard/options.php");
        exit();
    }
?>