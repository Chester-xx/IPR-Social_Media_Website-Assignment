<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>@Connect | Account Options</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="options-page">
        <div class="options-container">
            <h1>Account Options</h1>
            <?php Error("dbfail", "Failed to connect to the database"); ?>
            <form class="option-form" action="../users/changeusername.php" method="post">
                <label for="username"><strong>Change Username</strong></label>
                <input type="text" id="username" name="username" placeholder="Enter new username" required>
                <?php
                    Error("usernameshort", "Username must include 3 or more characters");
                    Error("userexists", "Username is already taken");
                    Error("empty", "Please fill in the username field");
                ?>
                <button type="submit">Save</button>
            </form>
            <form id="photo-form" class="option-form" method="post" action="../users/changepfp.php" enctype="multipart/form-data">
                <input type="hidden" name="photo-form" value="1">
                <label for="pfp">Change Profile Photo</label>
                <input type="file" id="pfp" name="pfp" accept=".jpg, .jpeg, .png, .webp" required hidden>
                <?php
                    Error("fileupload", "Failed to change profile photo, please try again");
                    Error("filesize", "Please upload an image smaller than 10MB");
                    Error("imageext", "Please upload a valid image");
                ?>
                <button id="upload-img" type="button">Upload</button>
            </form>
            <hr class="divider">
            <form class="option-form" action="../logout.php" method="post">
                <label>Log Out</label>
                <button type="submit">Log Out</button>
            </form>
            <form class="option-form danger" action="../users/deleteaccount.php" method="post" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone')">
                <?php Error("deletefail", "Failed to delete your account, please try again"); ?>
                <label>Delete Account</label>
                <button type="submit">Delete Account</button>
            </form>
        </div>
    </div>
    <script>
        const btn = document.getElementById("upload-img");
        const fileh = document.getElementById("pfp");
        btn.addEventListener("click", () => {
            fileh.click();
        });
        fileh.addEventListener("change", () => {
            const file = fileh.files[0];
            if (!file) return;
            if (!["image/jpeg", "image/png", "iamge/webp"].includes(file.type)) {
                alert("Invalid file uploaded, only jpg, jpeg, png or webp accepted");
                fileh.value = "";
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert("Uploaded image is too big, please upload an image smaller than 10MB");
                fileh.value = "";
                return;
            }
            document.getElementById("photo-form").submit();
        });
    </script>
</body>
</html>
