<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();

    // NEED TO ALLOW GET WITH RESPECT TO USERNAME AND THEN OVERWRITE UID WITH THE USERNAMES UserID
    $uid = $_SESSION["UserID"];

    $conn = DBSesh();

    $stmt = $conn->prepare("Select * From `tblUsers` Where `UserID` = ?");
    $stmt->bind_param("i", $_SESSION["UserID"]);
    $stmt->execute();
    $result = $stmt->get_result();

    CheckQueryResult($result, $stmt, $conn, "/dashboard/profile.php?error=dbfail");

    $data = $result->fetch_assoc();
    $path = "../content/profiles/" . $data["PFP"];

    if (!file_exists(__DIR__ . "/../content/profiles/" . $data["PFP"])) {
        $path = "../content/profiles/default.jpg";
    }

    $result->free();
    $stmt->close();
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>@Connect | Profile</title>
</head>
<body>
    
    <?php
        PrintHeader();
        Error("dbfail", "Failed to communicate with the database");
    ?>

    <div class="profile-page-cont">
        <div class="profile-cont">
            <div class="profile-info">
                <img src="<?php echo($path); ?>" alt="Profile Photo">
                <div class="profile-details">
                    <h2>Username</h2>
                    <p>75 Friends</p>
                </div>
            </div>
            <div class="profile-btn">
                <a href="../dashboard/options.php"><button>Edit Profile</button></a>
                <a href="../dashboard/"><button>Home</button></a>
            </div>
        </div>
            
    
        <!-- Create Post -->
        <!-- create a post -->
        <form action="createpost.php" method="post" enctype="multipart/form-data">
            <div class="post-box" style="border-top: 1px solid #333;">
                <div class="preview" id="img_preview"></div>
                <textarea name="post_text" id="post_text" placeholder="What's New?" ><?php Error("notext", "Please fill in this field", true); ?></textarea>
                <div class="row" style="justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                    <div style="margin-left: 0.2rem;">
                        <img class="action" src="../content/assets/upload.png" alt="Upload Image" id="post_upload_image" name="post_upload_image">
                        <img class="action" src="../content/assets/gif.png" alt="Upload Gif" id="post_upload_gif" name="post_upload_gif">
                        <img class="action" src="../content/assets/video.png" alt="Upload Video" id="post_upload_video" name="post_upload_video">
                    </div>
                    <input type="file" id="post_img" name="post_img" accept=".jpg, .jpeg, .png, .webp, .mp4, .gif, .web, .mov" style="display: none;">
                    <button type="submit" name="post_submit" id="post_submit">Post</button>
                </div>
            </div>
        </form>
        <!-- Posts Summary -->
        <div class="posts" id="posts"></div>
        <div id="loading" class="loader">Loading</div>
    </div>

    <script src="../includes/functions.js"></script>
    <script>
        // --- Var Declerations ---
        let uid = <?php echo($uid); ?>;
        let offset = 0;
        let loading = false;
        let cont = true;
        const loadlimit = 10;

        // --- Event Bindings ---
        // Clicks for each file upload action
        const actions = ["post_upload_image", "post_upload_gif", "post_upload_video"];
        actions.forEach(id => {
            document.getElementById(id).addEventListener("click", function() {
                document.getElementById("post_img").click();
            })
        });
        // Preview handler
        document.getElementById("post_img").addEventListener("change", PreviewUpload);
        
        // --- Function Calls ---
        GetNewPosts(1, uid);
    </script>

</body>
</html>