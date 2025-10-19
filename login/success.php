<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckLogIn();
    // Not allowed access without flag, this ensures that users cannot access this page or use this page to change someones profile photo
    if (!isset($_SESSION["acc_created"])) {
        header("Location: /login/index.php");
        exit();
    }
    // Post method and button clicked
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enter"])) {
        // check if a file was uploaded
        if (isset($_FILES["reg_pfp"]) && $_FILES["reg_pfp"]["error"] !== UPLOAD_ERR_NO_FILE) {
            // store the file information (array)
            $file = $_FILES["reg_pfp"];
            // was there an upload error
            if ($file["error"] !== UPLOAD_ERR_OK) {
                header("Location: /login/success.php?id=" . $_GET["id"] . "&error=file");
                exit();
            }
            // is the file size larger than 10MB, i wont allow any larger images
            if ($file["size"] > 10 * 1024 * 1024) {
                header("Location: /login/success.php?id=" . $_GET["id"] . "&error=size");
                exit();
            }
            // get the user id
            $uid = trim(filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));
            // get the extension of the file uploaded via pathinfo
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            // create a 'unique' name, its just the users ID passed via get, also filter input
            $name = $uid . "." . $ext;
            // if the extension is not in the below list of allowed extensions, 
            if (!in_array($ext, haystack: ACCEPTED_PFP)) {
                header("Location: /login/success.php?id=" . $_GET["id"] . "&error=image");
                exit();
            }
            // did we successfully move the uploaded image from temporary to our dedicated file location
            if (!move_uploaded_file($file["tmp_name"], PFP . $name)) {
                 header("Location: /login/success.php?id=" . $_GET["id"] . "&error=file");
                exit();
            }
            // Update users pfp file name in db
            $tmp = RunQuery(
                null,
                "Update `tblUsers` Set `PFP` = ? Where `UserID` = ?",
                "Change",
                "/login/success.php?id=" . $_GET["id"] . "&error=dbfail",
                "si",
                $name, $uid
            );
        }
        // no image was uploaded so just proceed - there is a default.jpg string for each new user account
        unset($_SESSION["acc_created"]);
        header("Location: /login/");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/style.css">
    <title>Connect | Account Creation Successful</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="page">
        <div class="auth">
            <h1>Success</h1>
            <p>Thanks for signing up.<br>Your account has been created successfully</p> <br>
            <p>Optionally, you can upload a profile photo now</p>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="reg_pfp" id="reg_pfp" accept=".jpg, .jpeg, .png, .webp" placeholder="Upload Image"> <br>
                <?php
                    Error("file", "File upload error, please try again");
                    Error("image", "Not an accepted image format");
                    Error("size", "Image is too large, please upload an image smaller than 10MB");
                    Error("dbfail", "Failed to upload image to database, database connection failure");
                ?> <br>
                <button type="submit" name="enter" id="enter">Log In</button>
            </form>
        </div>
    </div>
</body>
</html>