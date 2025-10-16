<?php
    include_once("../includes/functions.php");
    StartSesh();
    CheckNotLoggedIn();

    $conn = DBSesh();

    $stmt = $conn->prepare("Select `PFP` From `tblUsers` Where `UserID` = ?");
    $stmt->bind_param("i", $_SESSION["UserID"]);
    $stmt->execute();
    $result = $stmt->get_result();

    CheckQueryResult($result, $stmt, $conn, "/dashboard/error.php?error=dbfail");

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
            <!-- header -->
            <div class="feed-head">
                <button class="active">Recents</button>
                <button>Following</button>
            </div>
            <!-- create a post -->
            <form action="createpost.php" method="post" enctype="multipart/form-data">
                <div class="post-box">
                    <div class="preview" id="img_preview"></div>
                    <div class="row">
                        <img class="ico pfp" src="<?php echo($path); ?> " alt="Profile">
                        <textarea name="post_text" id="post_text" placeholder="What's New?" ></textarea>
                    </div>
                    <div class="row" style="justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <div style="">
                            <img class="action" src="../content/assets/upload.png" alt="Upload Image" id="post_upload_image" name="post_upload_image">
                            <img class="action" src="../content/assets/gif.png" alt="Upload Gif" id="post_upload_gif" name="post_upload_gif">
                            <img class="action" src="../content/assets/video.png" alt="Upload Video" id="post_upload_video" name="post_upload_video">
                        </div>
                        <input type="file" id="post_img" name="post_img" accept=".jpg, .jpeg, .png, .webp, .mp4, .gif, .web, .mov" style="display: none;">
                        <button type="submit" name="post_submit" id="post_submit">Post</button>
                    </div>
                </div>
            </form>
            <!-- feed DYNAMIC CONTENT WILL BE LOADED INTO HERE -->
            <div class="posts" id="posts"></div>
            <div id="loading" class="loader">Loading</div>
        </div>
    </div>
    <!-- SCRIPTS -->
    <script>
        // SCROLL TO TOP OF PAGE
        function ToTop() {
            window.scrollTo({top : 0, behavior : "smooth"});
            setTimeout(() => { document.getElementById("post_text").focus(); }, 300);
        }
        // ACTION EVENTS
            // images
        document.getElementById("post_upload_image").addEventListener("click", function() {
            document.getElementById("post_img").click();
        });
            // gifs
        document.getElementById("post_upload_gif").addEventListener("click", function() {
            document.getElementById("post_img").click();
        }); 
            // videos
        document.getElementById("post_upload_video").addEventListener("click", function() {
            document.getElementById("post_img").click();
        });
        // IMAGE PREVIEW LOADER
        document.getElementById("post_img").addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);
                img.classList.add("preview");
                document.getElementById("img_preview").innerHTML = "";
                document.getElementById("img_preview").appendChild(img);
            }
        });
        // API ENDPOINT
        let offset = 0;
        let loading = false;
        const loadlimit = 10;
        let cont = true;
        // REQUESTS DATA FROM API AND DISPLAYS
        async function GetNewPosts() {
            // dont run if its currently loading data, or if there are no more posts to display
            if (loading || !cont) return;
            loading = true;
            // set placeholder
            document.getElementById("loading").innerText = "Loading";
            // catch errors
            try {
                // async get json from api
                const response = await fetch(`/api/posts/request.php?offset=${offset}`);
                const data = await response.json();
                // check if succession of data transmission
                if (!data.success) {
                    console.error("API Error: ", data.error);
                    document.getElementById("loading").innerText = "Error loading posts";
                    loading = false;
                    return;
                }
                // get posts element
                const box = document.getElementById("posts");
                // for each row of data, create a new post element and append information
                data.posts.forEach(post => {
                    const div = document.createElement("div");
                    div.classList.add("post");
                    let media = "";
                    if (post.Image) {
                        const ext = post.Image.split(".").pop().toLowerCase();
                        if (["mp4", "webm", "mov"].includes(ext)) {
                            media = `<video src="../content/posts/${post.Image}" controls></video>`;
                        } else {
                            media = `<img src="../content/posts/${post.Image}" alt="Post Image">`;
                        }
                    }
                    div.innerHTML = `
                        <div class="post-head">
                            <p><strong>${post.Username}</strong></p>
                        </div>
                        <div class="post-body">
                            <p>${post.Content}</p>
                            ${media}
                        </div>
                        <small>${post.CreateTime}</small>
                    `;
                    box.appendChild(div);
                });
                // update the offset for next load
                offset += data.posts.length;
                cont = data.continue;
                // either empty text or no elements based on data.continue
                document.getElementById("loading").innerText = cont ? "" : "No more posts";
            } catch (err) {
                // debug
                console.error("Fetch Error:", err);
                document.getElementById("loading").innerText = "Network error";
            }
            // reset function for next load
            loading = false;
        }
        // CALLS THE API IF THE USER REACHES END OF PAGE
        function Scroll() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
                GetNewPosts();
            }
        }
        // link scroll event to API
        window.addEventListener("scroll", Scroll);
        // CALL REQUEST ON PAGE LOAD
        GetNewPosts();
    </script>
</body>
</html>