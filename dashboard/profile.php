<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();
    // conn as we have multiple queries
    $conn = DBSesh();
    $IS_OWN_PAGE = true;
    $DBUSER = null;
    // Get viewer info from db
    $result1 = RunQuery(
        $conn,
        "Select `UserID`, `PFP`, `Username` From `tblUsers` Where `UserID` = ?",
        "Query",
        "/dashboard/profile.php?error=dbfail",
        "i",
        $_SESSION["UserID"]
    );
    CatchDBError($result1);
    // Profile via search
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["Username"])) {
        // Get username to view
        $username = htmlspecialchars(trim($_GET["Username"]), ENT_QUOTES, "UTF-8");
        // get user data specific to username
        $result2 = RunQuery(
            $conn,
            "Select `UserID`, `Username`, `PFP` From `tblUsers` Where `Username` = ?",
            "None",
            "",
            "s",
            $username
        );
        // No user was found with that username
        if ($result2->num_rows < 1) {
            header("Location: /dashboard/error.php?Username=$username&error=nouser");
            exit();
        }
        // Check if the viewer is looking at their own account, overwrite uid
        $DBUSER = $result2->fetch_assoc();
        $IS_OWN_PAGE = ($_SESSION["UserID"] === $DBUSER["UserID"]);
        $uid = $DBUSER["UserID"];
    }
    $followstate = "";
    // set USER DATA (for viewing) & VIEWER DATA (for comments and likes)
    if ($IS_OWN_PAGE) {
        $udata = $result1->fetch_assoc();
        $vdata = $udata;
    } else {
        $udata = $DBUSER;
        $vdata = $result1->fetch_assoc();
        // Check if viewer is friends with user
        $result = RunQuery(
            $conn,
            "Select 1 From `tblFollowers` Where `UserID` = ? And `UserFollowerID` = ? Limit 1",
            "None",
            "",
            "ii",
            $udata["UserID"], $vdata["UserID"]
        );
        CatchDBError($result);
        // If friend row found
        if ($result->num_rows == 1) {
            $followstate = "Unfollow";
            $btncolor = "#333333";
        }
        // If request row found
        if ($result->num_rows < 1) {
            // Check if viewer has requested to follow user
            $result = RunQuery(
                $conn,
                "Select 1 From `tblFollowRequests` Where `FromID` = ? And `ToID` = ?",
                "None",
                "",
                "ii",
                $vdata["UserID"], $udata["UserID"]
            );
            CatchDBError($result);
            // User either requested to follow or doesnt follow at all
            if ($result->num_rows == 1) {
                $followstate = "Requested";
                $btncolor = "#2c2c2cff";
            } else {
                $followstate = "Follow";
                $btncolor = "#498bd6";
            }
        }
    }
    // Get user friend count
    $result = RunQuery(
        $conn,
        "Select Count(*) As `Friends` From `tblFollowers` Where `UserFollowerID` = ?",
        "None",
        "",
        "i",
        $udata["UserID"]
    );
    CatchDBError($result);
    // Get friend count
    $friendcount = $result->fetch_assoc()["Friends"];
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>@Connect | <?php echo($udata["Username"]); ?></title>
</head>
<body>
    <?php
        PrintHeader();
        Error("nouser", "User Does Not Exist");
        Error("dbfail", "Failed to communicate with the database");
    ?>
    <div class="profile-page-cont">
        <div class="profile-cont">
            <div class="profile-info">
                <img src="<?php echo("../content/profiles/" . (file_exists(__DIR__ . "/../content/profiles/" . $udata["PFP"]) ? $udata["PFP"] : "default.jpg")); ?>" alt="Profile Photo">
                <div class="profile-details">
                    <h2><?php echo($udata["Username"]); ?></h2>
                    <p><a href="../dashboard/followers.php" class="noline"><?php 
                            if ($friendcount < 1) {
                                echo("No Friends");
                            } else if ($friendcount == 1) {
                                echo("1 Friend");
                            } else {
                                echo($friendcount . " Friends");
                            }
                        ?>
                    </a></p>
                </div>
            </div>
            <div class="profile-btn">
                <?php 
                echo($IS_OWN_PAGE ? 
                    "<a href='../dashboard/options.php'><button>Edit Profile</button></a>" : 
                    "<a href='../users/follow.php?uid=" . $udata["UserID"] . "&state=" . $followstate . "'>
                        <button style='Background: " . $btncolor . "'>" . $followstate . "</button>
                    </a>"); ?>
                <a href="../dashboard/"><button>Home</button></a>
            </div>
        </div>
        <!-- Create Post -->
        <?php
            if ($IS_OWN_PAGE) {
                echo('
                    <form action="createpost.php" method="post" enctype="multipart/form-data">
                        <div class="post-box" style="border-top: 1px solid #333;">
                            <div class="preview" id="img_preview"></div>
                            <textarea name="post_text" id="post_text" placeholder="What\'s New?" >' . Error("notext", "Please fill in this field", true) . '</textarea>
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
                '
                );
            }
        ?>
        <!-- Posts Summary -->
        <div class="posts" id="posts" style="border-top: 1px solid #333;"></div>
        <div id="loading" class="loader">Loading</div>
    </div>
    <script src="../includes/functions.js"></script>
    <script>
        // --- Var Declerations ---
        const upfp = <?php echo("\"" . $vdata["PFP"] . "\"");?>;
        const username = <?php echo("\"" . $vdata["Username"] . "\""); ?>;
        uid = <?php echo($udata["UserID"]); ?>;
        APICALL = 1;

        // --- Event Bindings ---
            // Clicks for each file upload action && Preview handler only if IS_OWN_PAGE
        <?php
            if ($IS_OWN_PAGE) {
                echo('
                    const actions = ["post_upload_image", "post_upload_gif", "post_upload_video"];
                    actions.forEach(id => {
                        document.getElementById(id).addEventListener("click", function() {
                            document.getElementById("post_img").click();
                        })
                    });
                    document.getElementById("post_img").addEventListener("change", PreviewUpload);'
                );
            }
        ?>
            // Post Loading
        window.addEventListener("scroll", function() {
            Scroll();
        });

        // --- Function Calls ---
        GetNewPosts(1, uid);
    </script>
</body>
</html>