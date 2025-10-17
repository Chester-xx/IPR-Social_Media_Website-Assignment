<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    $conn = DBSesh();
    // Get profile image name
    $stmt = $conn->prepare("Select `PFP` From `tblUsers` Where `UserID` = ?");
    $stmt->bind_param("i", $_SESSION["UserID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    CheckQueryResult($result, $stmt, $conn, "/dashboard/error.php?error=dbfail");
    // create web path for loading into src
    $data = $result->fetch_assoc();
    $path = "../content/profiles/" . $data["PFP"];
    // check if the file exists, otherwise display default
    if (!file_exists(__DIR__ . "/../content/profiles/" . $data["PFP"])) {
        $path = "../content/profiles/default.jpg";
    }
    // close memory objects
    $result->free();
    $stmt->close();
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="/style/style.css">
    <title>@Connect</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <div class="feed-page-cont">
        <!-- Access -->
        <div class="acc-bar">
            <!-- Profile -->
            <a href="../dashboard/profile.php" class="noline">
                <button class="ico-btn" type="submit">
                    <img class="ico pfp" src="<?php echo($path); ?> " alt="Profile">
                </button>
            </a>
            <!-- Messages -->
            <a href="../dashboard/messages.php" class="noline">
                <button class="ico-btn" type="submit">
                    <img class="ico" src="../content/assets/message.svg" alt="Messages">
                </button>
            </a>
            <!-- Create Post -->
            <a href="javascript:void(0);" onclick="ToTop()" class="noline">
                <button class="ico-btn" type="submit">
                    <img class="ico" src="../content/assets/create.svg" alt="Create">
                </button>
            </a>
            <!-- Search -->
            <a href="../dashboard/search.php" class="noline">
                <button class="ico-btn" type="submit">
                    <img class="ico" src="../content/assets/search.svg" alt="Search">
                </button>
            </a>
            <!-- Settings -->
            <a href="../dashboard/options.php" class="noline">
                <button class="ico-btn" type="submit">
                    <img class="ico" src="../content/assets/settings.svg" alt="Settings">
                </button>
            </a>
        </div>
        <!-- Feed -->
        <div class="feed">            
            <!-- Header -->
            <div class="feed-head">
                <button class="active">Recents</button>
                <button>Following</button>
            </div>
            <!-- Create a post -->
            <form action="createpost.php" method="post" enctype="multipart/form-data">
                <div class="post-box">
                    <div class="preview" id="img_preview"></div>
                    <textarea name="post_text" id="post_text" placeholder="What's New?" ><?php Error("notext", "Please fill in this field", true); ?></textarea>
                    <div class="row" style="justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <div style="margin-left: 0.2rem;">
                            <img class="action" src="../content/assets/upload.png" alt="Upload Image" id="post_upload_image" name="post_upload_image">
                            <img class="action" src="../content/assets/gif.png" alt="Upload Gif" id="post_upload_gif" name="post_upload_gif">
                            <img class="action" src="../content/assets/video.png" alt="Upload Video" id="post_upload_video" name="post_upload_video">
                        </div>
                        <input type="file" id="post_img" name="post_img" accept=".jpg, .jpeg, .png, .webp, .mp4, .gif, .webm, .mov" style="display: none;">
                        <button type="submit" name="post_submit" id="post_submit">Post</button>
                    </div>
                </div>
            </form>
            <!-- Feed DYNAMIC CONTENT WILL BE LOADED INTO HERE -->
            <div class="posts" id="posts"></div>
            <div id="loading" class="loader">Loading</div>
        </div>
    </div>
    <!-- SCRIPTS -->
    <script src="../includes/functions.js"></script>
    <script>
        // --- Var Declerations ---
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
        // Post Loading
        window.addEventListener("scroll", function() {
            Scroll();
        });
        // Preview handler
        document.getElementById("post_img").addEventListener("change", PreviewUpload);
        
        // --- Function Calls ---
        // Get 10 Newest Posts
        GetNewPosts(0);
    </script>
</body>
</html>