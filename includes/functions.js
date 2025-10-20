// When the user wants to create a post, they click on the + icon
// This takes the user to the top of the page where the post-box is
// Focusing on the textarea for typing 
function ToTop() {
    window.scrollTo({top : 0, behavior : "smooth"});
    setTimeout(() => { document.getElementById("post_text").focus(); }, 350);
}

// API helper function
// When the user is some distance close to the end of the page, load more content
function Scroll() {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
        GetNewPosts(0);
    }
}

// Appends the uploaded file to the post window for user viewing
function PreviewUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.classList.add("preview");
        const preview = document.getElementById("img_preview");
        preview.innerHTML = "";
        preview.appendChild(img);
    }
}

// Requests data from API to get the next 10 posts from the database
async function GetNewPosts(APICALL, uid = null) {
    // hide general calls from user
    switch (APICALL) {
        case 0:
            APICALL = `/api/posts/request.php?offset=${offset}`;
            break;
        case 1:
            APICALL = `/api/posts/requestuserposts.php?uid=${uid}&offset=${offset}`;
            break;
    }
    // dont run if its currently loading data, or if there are no more posts to display
    if (loading || !cont) return;
    loading = true;
    // set placeholder
    document.getElementById("loading").innerText = "Loading";
    // catch errors
    try {
        // async get json from api
        const response = await fetch(APICALL);
        const data = await response.json();
        // check if succession of data transmission
        if (!data.success) {
            console.error("API Error: ", data.error.message, data.error.code);
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
            let time = new Date(post.CreateTime);
            time = time.toLocaleString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                hour12: false
            });
            div.innerHTML = `
                <div class="post-head">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <img src="../content/profiles/${post.PFP}" class="post-pfp" alt="Profile Photo">
                        <strong>${post.Username}</strong>
                    </div>
                </div>
                <div class="post-body">
                    <p>${post.Content}</p>
                    ${media}
                </div>
                <div class="row" style="justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <img src="../content/assets/like.png" alt="Like" class="ico" style="width: 1.8rem;">
                        <img src="../content/assets/comment.png" alt="Comment" class="ico" style="width: 1.8rem;">
                    </div>
                    <small>${time}</small>
                </div>
            `;
            box.appendChild(div);
        });
        // update the offset for next load
        offset += data.posts.length;
        cont = data.continue;
        // either empty text or no elements based on data.continue
        document.getElementById("loading").innerText = cont ? "" : "No posts";
    } catch (err) {
        // debug
        console.error("Fetch Error:", err);
        document.getElementById("loading").innerText = "Network error";
    }
    // reset function for next load
    loading = false;
}