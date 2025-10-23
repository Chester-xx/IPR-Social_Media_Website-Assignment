// --- GLOBALS ---

let offset = 0;
let loadlimit = 10;
let loading = false;
let cont = true;
let uid = null;
let feed = "recents";
let timer = null;
let following = false;
let APICALL = 0;

// --- FUNCTIONS ---

function ToTop() {
    // When the user wants to create a post, they click on the + icon
    // This takes the user to the top of the page where the post-box is
    // Focusing on the textarea for typing 
    window.scrollTo({top : 0, behavior : "smooth"});
    setTimeout(() => { document.getElementById("post_text").focus(); }, 350);
}

function Scroll() {
    // API helper function
    // When the user is some distance close to the end of the page, load more content
    if (loading || !cont) return;
    if (timer) clearTimeout(timer);
    if ((document.documentElement.scrollTop + window.innerHeight) >= ( document.documentElement.scrollHeight - 150)) {
        GetNewPosts(APICALL, uid, following);
        timer = setTimeout(() => {
            switch (feed) {
                case "recents":
                    GetNewPosts(0);
                    break;
                case "following":
                    GetNewPosts(2);
                    break;
                case "user":
                    if (currentUID) GetNewPosts(1, currentUID);
                    break;
            }
        }, 200);
    }
}

function PreviewUpload(event) {
    // Appends the uploaded file to the post window for user viewing
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

function timesince(date) {
    // Date formatter, just gives a nice preview like 5mins ago etc
    const s = Math.floor((new Date() - new Date(date)) / 1000);
    const u = [["year", 31536000], ["month", 2592000], ["day", 86400], ["hour", 3600], ["minute", 60]];
    for (const [name, sec] of u) {
        const n = Math.floor(s/sec);
        if (n > 0) return n + " " + name + (n > 1 ? "s" : "") + " ago";
    }
    return "just now";
}

async function GetNewPosts(APICALL, uid = null, following = false) {
    // Requests data from API to get the next 10 posts from the database
    // dont run if its currently loading data, or if there are no more posts to display
    if (loading || !cont) return;
    loading = true;
    document.getElementById("loading").innerText = "Loading";
    // catch errors
    try {
        let endp;
        // Correct endpoint call to api dependant on each specific request
        switch (APICALL) {
            case 0:
                endp = following ? `/api/posts/requestfollowerposts.php?offset=${offset}` : `/api/posts/request.php?offset=${offset}`;
                feed = "recents";
                break;
            case 1:
                if (!uid) throw "UID required for user posts";
                endp = `/api/posts/requestuserposts.php?uid=${uid}&offset=${offset}`;
                feed = "user";
                currentUID = uid;
                break;
            case 2:
                endp = `/api/posts/requestfollowerposts.php?offset=${offset}`;
                feed = "following";
                break;
            default:
                endp = `/api/posts/request.php?offset=${offset}`;
                feed = "recents";
        }
        const response = await fetch(endp);
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
            div.dataset.postid = post.PostID;
            let media = "";
            if (post.Image) {
                const ext = post.Image.split(".").pop().toLowerCase();
                if (["mp4", "webm", "mov"].includes(ext)) {
                    media = `<video src="../content/posts/${post.Image}" controls></video>`;
                } else {
                    media = `<img src="../content/posts/${post.Image}" alt="Post Image">`;
                }
            }
            let time = new Date(post.CreateTime).toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                hour12: false
            });
            const likestate = post.Liked ? "../content/assets/liked.png" : "../content/assets/unliked.png";
            div.innerHTML = `
                <div class="post-head">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <a class="noline" href="/dashboard/profile.php?Username=${post.Username}"><img src="../content/profiles/${post.PFP}" class="post-pfp" alt="Profile Photo"></a>
                        <a class="noline" href="/dashboard/profile.php?Username=${post.Username}"><strong>${post.Username}</strong></a>
                    </div>
                </div>
                <div class="post-body">
                    <p>${post.Content}</p>
                    ${media}
                </div>
                <div class="row" style="justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <img id="like-${post.PostID}" src="${likestate}" alt="Like" class="ico" style="width: 1.8rem;">
                        <img id="comment-${post.PostID}" src="../content/assets/comment.png" alt="Comment" class="ico" style="width: 1.8rem;">
                    </div>
                    <small>${time}</small>
                </div>
            `;
            box.appendChild(div);
            const like = document.getElementById(`like-${post.PostID}`);
            div.addEventListener("dblclick", async () => {
                try {
                    // send to PHP $_POST
                    const form = new FormData();
                    form.append("PostID", post.PostID);
                    const response = await fetch(`/api/posts/like.php`, {
                        method: "POST",
                        body: form
                    });
                    const data = await response.json();
                    if (data.success) {
                        like.src = data.liked ? "../content/assets/liked.png" : "../content/assets/unliked.png";
                    } else {
                        console.error(data.error.message, ", Code: ", data.error.code);
                    }
                } catch (e) {
                    console.error(e);
                }
            });
            like.addEventListener("click", async () => {
                try {
                    // send to PHP $_POST
                    const form = new FormData();
                    form.append("PostID", post.PostID);
                    const response = await fetch(`/api/posts/like.php`, {
                        method: "POST",
                        body: form
                    });
                    const data = await response.json();
                    if (data.success) {
                        if (data.liked) {
                            like.src = "../content/assets/liked.png";
                        } else {
                            like.src = "../content/assets/unliked.png";
                        }
                    } else {
                        console.error(data.error.message, ", Code: ", data.error.code);
                    }
                } catch (e) {
                    console.error(e);
                }
            });
            document.getElementById(`comment-${post.PostID}`).addEventListener("click", async () => {
                try {
                    let commentsection = document.getElementById(`comments-${post.PostID}`);
                    if (commentsection) {
                        commentsection.remove();
                        return;
                    }
                    commentsection = document.createElement("div");
                    commentsection.id = `comments-${post.PostID}`;
                    commentsection.classList.add("post-box");
                    commentsection.style.border = "none";
                    commentsection.innerHTML = `
                        <div class="row" style="margin-bottom: 0.5rem">
                            <textarea style="height: 1.2rem" id="input-${post.PostID}" placeholder="Add a comment"></textarea>
                            <button style="margin-top: 0rem" id="btn-${post.PostID}">Post</button>
                        </div>
                        <div id="list-${post.PostID}" class="posts"></div>
                    `;
                    div.appendChild(commentsection);
                    const response = await fetch(`/api/posts/requestcomments.php?PostID=${post.PostID}`);
                    const data = await response.json();
                    const list = document.getElementById(`list-${post.PostID}`);
                    if (data.none) {
                        list.innerHTML = `<small style="display: block; text-align: center; margin-top: 1rem;">No comments yet</small>`;
                    } else {
                        data.comments.forEach(comment => {
                            const commentdiv = document.createElement("div");
                            let time = timesince(comment.CreateTime);
                            commentdiv.innerHTML = `
                                <div class="row" style="align-items: center; justify-content: space-between; width: 100%">
                                    <div style="display: flex; align-items: center; gap: 0.5rem">
                                        <a class="noline" href="/dashboard/profile.php?Username=${comment.Username}"><img src="../content/profiles/${comment.PFP}" class="post-pfp" alt="Profile Photo"></a>
                                        <a class="noline" href="/dashboard/profile.php?Username=${comment.Username}"><strong>${comment.Username}</strong></a>
                                    </div>
                                    <small>${time}</small>
                                </div>
                                <div class="cmnt-body">
                                    <p>${comment.Content}</p>
                                </div>
                            `;
                            list.appendChild(commentdiv);
                        });
                    }
                    const postbtn = document.getElementById(`btn-${post.PostID}`);
                    const input = document.getElementById(`input-${post.PostID}`);
                    postbtn.addEventListener("click", async () => {
                        const content = input.value.trim();
                        if (!content) { return; }
                        const form = new FormData();
                        form.append("PostID", post.PostID);
                        form.append("Content", content);
                        const response2 = await fetch(`/api/posts/comment.php`, {
                            method: "POST",
                            body: form
                        });
                        const result = await response2.json();
                        if (result.success) {
                            const commentdivnew = document.createElement("div");
                            let time = timesince(Date());
                            commentdivnew.innerHTML = `
                                <div class="row" style="align-items: center; justify-content: space-between; width: 100%">
                                    <div style="display: flex; align-items: center; gap: 0.5rem">
                                        <img src="../content/profiles/${upfp}" class="post-pfp" alt="Profile Photo">
                                        <strong>${username}</strong>
                                    </div>
                                    <small>${time}</small>
                                </div>
                                <div class="cmnt-body">
                                    <p>${input.value.trim()}</p>
                                </div>
                            `;
                            list.prepend(commentdivnew);
                            input.value = "";
                        } else {
                            console.log("fail");
                        }
                    });
                } catch (e) {
                    console.error(e);
                }
            });
        });
        // update the offset for next load
        offset += data.posts.length;
        cont = data.continue;
        // either empty text or no elements based on data.continue
        document.getElementById("loading").innerText = cont ? "" : "No posts";
    } catch (e) {
        // debug
        console.error("Fetch Error:", e);
        document.getElementById("loading").innerText = "Network error";
    }
    // reset function for next load
    loading = false;
}

async function GetFollowRequests() {
    const list = document.getElementById("follow-requests");
    try {
        const response = await fetch(`/api/search/followrequests.php`);
        const data = await response.json();
        if (!data.success) {
            console.error("API Error: ", data.error.message, data.error.code);
            return;
        }
        if (data.users.length < 1) {
            const div = document.createElement("div");
            div.innerHTML = `<p style="text-align: center">No Follow Requests</p>`;
            list.appendChild(div);
            return;
        }
        data.users.forEach(user => {
            const div = document.createElement("div");
            div.classList.add("follow-item");
            div.innerHTML = `
                <img src="../content/profiles/${user.PFP}" alt="Profile Photo">
                <div class="follow-details">
                    <strong>${user.Username}</strong>
                </div>
                <div class="follow-actions">
                    <button id="follow-accept-${user.RequestID}" class="accept">Accept</button>
                    <button id="follow-decline-${user.RequestID}" class="decline">Decline</button>
                </div>
            `;
            list.appendChild(div);
            const accept = document.getElementById(`follow-accept-${user.RequestID}`);
            const decline = document.getElementById(`follow-decline-${user.RequestID}`);
            accept.addEventListener("click", async () => {
                try {
                    const response = await fetch(`/api/users/acceptfollow.php?rid=${user.RequestID}`);
                    const data = response.json();
                    if (!data.success) {
                        console.log(data.error.message, data.error.code);
                        return;
                    } else {
                        div.remove();
                        GetFriendList();
                    }
                } catch (e) {
                    console.log(e);
                }
            });
            decline.addEventListener("click", async () => {
                try {
                    const response = await fetch(`/api/users/declinefollow.php?rid=${user.RequestID}`);
                    const data = response.json();
                    if (!data.success) {
                        console.log(data.error.message, data.error.code);
                        return;
                    } else {
                        div.remove();
                    }
                } catch (e) {
                    console.log(e);
                }
            });
        });
    } catch (e) {
        console.error(e);
    }
}

async function GetFriendList() {
    const list = document.getElementById("friends-list");
    try {
        const response = await fetch(`/api/search/requestfriends.php`);
        const data = await response.json();
        if (!data.success) {
            console.error("API Error: ", data.error.message, data.error.code);
            return;
        }
        if (data.users.length < 1) {
            const div = document.createElement("div");
            div.innerHTML = `<p style="text-align: center">No Friends</p>`;
            list.appendChild(div);
            return;
        }
        data.users.forEach(user => {
            const div = document.createElement("div");
            div.classList.add("friend-item");
            div.innerHTML = `
                <img src="../content/profiles/${user.PFP}" alt="Profile Photo">
                <div class="friend-details">
                    <strong>${user.Username}</strong>
                </div>
                <div class="follow-actions">
                    <button id="friend-remove-${user.UserID}" class="decline">Remove</button>
                </div>
            `;
            list.appendChild(div);
            const remove = document.getElementById(`friend-remove-${user.UserID}`);
            remove.addEventListener("click", async () => {
                try {
                    const response = await fetch(`/api/users/removefriend.php?rid=${user.UserID}`);
                    const data = response.json();
                    if (!data.success) {
                        console.log(data.error.message, data.error.code);
                        return;
                    } else {
                        div.remove();
                    }
                } catch (e) {
                    console.log(e);
                }
            });
        });
    } catch (e) {
        console.error(e);
    }
}