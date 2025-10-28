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
let currentUserID = null;

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
    // Dont call API within 200ms after scroll
    if (timer) clearTimeout(timer);
    // If they reach 150px before the end of page
    if ((document.documentElement.scrollTop + window.innerHeight) >= ( document.documentElement.scrollHeight - 150)) {
        // Load older posts
        GetNewPosts(APICALL, uid, following);
        // Call function with respects to where the user is loading posts
        timer = setTimeout(() => {
            switch (feed) {
                // recents tab in main
                case "recents":
                    GetNewPosts(0);
                    break;
                // following tab in main
                case "following":
                    GetNewPosts(2);
                    break;
                // user account page
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
        // create preview of media upload and append to the create post box
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
    // seconds closest to each specification
    const u = [["year", 31536000], ["month", 2592000], ["day", 86400], ["hour", 3600], ["minute", 60]];
    // compare time periods
    for (const [name, sec] of u) {
        const n = Math.floor(s/sec);
        // update string date for respective time period chosen
        if (n > 0) return n + " " + name + (n > 1 ? "s" : "") + " ago";
    }
    // otherwise return current
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
                // request posts either for friends tab or recents tab
                endp = following ? `/api/posts/requestfollowerposts.php?offset=${offset}` : `/api/posts/request.php?offset=${offset}`;
                feed = "recents";
                break;
            case 1:
                // request user account posts
                if (!uid) throw "UID required for user posts";
                endp = `/api/posts/requestuserposts.php?uid=${uid}&offset=${offset}`;
                feed = "user";
                currentUID = uid;
                break;
            case 2:
                // request friends recent posts
                endp = `/api/posts/requestfollowerposts.php?offset=${offset}`;
                feed = "following";
                break;
            default:
                // default to standard request
                endp = `/api/posts/request.php?offset=${offset}`;
                feed = "recents";
        }
        // fetch api response
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
            // upload post media
            if (post.Image) {
                const ext = post.Image.split(".").pop().toLowerCase();
                if (["mp4", "webm", "mov"].includes(ext)) {
                    media = `<video src="../content/posts/${post.Image}" controls></video>`;
                } else {
                    media = `<img src="../content/posts/${post.Image}" alt="Post Image">`;
                }
            }
            // add post time stamp
            let time = new Date(post.CreateTime).toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                hour12: false
            });
            // check if the post is liked or not
            const likestate = post.Liked ? "../content/assets/liked.png" : "../content/assets/unliked.png";
            // create dynamic content
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
            // append
            box.appendChild(div);
            const like = document.getElementById(`like-${post.PostID}`);
            // add an event listener for double clicking on a post
            div.addEventListener("dblclick", async () => {
                try {
                    // send to PHP via post
                    const form = new FormData();
                    form.append("PostID", post.PostID);
                    // fetch
                    const response = await fetch(`/api/posts/like.php`, {
                        method: "POST",
                        body: form
                    });
                    const data = await response.json();
                    // load dynamic content
                    if (data.success) {
                        like.src = data.liked ? "../content/assets/liked.png" : "../content/assets/unliked.png";
                    } else {
                        console.error(data.error.message, ", Code: ", data.error.code);
                    }
                } catch (e) {
                    console.error(e);
                }
            });
            // add event listener for clicking on the like button
            like.addEventListener("click", async () => {
                try {
                    // send to PHP via post
                    const form = new FormData();
                    form.append("PostID", post.PostID);
                    // fetch
                    const response = await fetch(`/api/posts/like.php`, {
                        method: "POST",
                        body: form
                    });
                    const data = await response.json();
                    // show liked or not
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
            // add comment button event listener
            document.getElementById(`comment-${post.PostID}`).addEventListener("click", async () => {
                try {
                    // remove comment section if its loaded
                    let commentsection = document.getElementById(`comments-${post.PostID}`);
                    if (commentsection) {
                        commentsection.remove();
                        return;
                    }
                    // create comment section + comment upload method
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
                    // fetch the comments on that post
                    const response = await fetch(`/api/posts/requestcomments.php?PostID=${post.PostID}`);
                    const data = await response.json();
                    const list = document.getElementById(`list-${post.PostID}`);
                    // either load comments or show that there are none
                    if (data.none) {
                        list.innerHTML = `<small style="display: block; text-align: center; margin-top: 1rem;">No comments yet</small>`;
                    } else {
                        // for each comment, create dynamic box and append
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
                    // add event listener to post comment button
                    const postbtn = document.getElementById(`btn-${post.PostID}`);
                    const input = document.getElementById(`input-${post.PostID}`);
                    postbtn.addEventListener("click", async () => {
                        // get comment
                        const content = input.value.trim();
                        if (!content) { return; }
                        // send via post
                        const form = new FormData();
                        form.append("PostID", post.PostID);
                        form.append("Content", content);
                        // fetch response
                        const response2 = await fetch(`/api/posts/comment.php`, {
                            method: "POST",
                            body: form
                        });
                        const result = await response2.json();
                        if (result.success) {
                            // show the new comment the user posted
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
    // get all follow requests for specific user
    const list = document.getElementById("follow-requests");
    try {
        // fetch
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
        // load each request dynamically
        data.users.forEach(user => {
            const div = document.createElement("div");
            div.classList.add("follow-item");
            div.innerHTML = `
                <a href="../dashboard/profile.php?Username=${user.Username}"><img src="../content/profiles/${user.PFP}" alt="Profile Photo"></a>
                <div class="follow-details">
                    <a class="noline" href="../dashboard/profile.php?Username=${user.Username}"><strong>${user.Username}</strong></a>
                </div>
                <div class="follow-actions">
                    <button id="follow-accept-${user.RequestID}" class="accept">Accept</button>
                    <button id="follow-decline-${user.RequestID}" class="decline">Decline</button>
                </div>
            `;
            list.appendChild(div);
            // add event listeners to accepting or declining requests
            const accept = document.getElementById(`follow-accept-${user.RequestID}`);
            const decline = document.getElementById(`follow-decline-${user.RequestID}`);
            accept.addEventListener("click", async () => {
                try {
                    // fetch
                    const response = await fetch(`/api/users/acceptfollow.php?rid=${user.RequestID}`);
                    const data = await response.json();
                    // refresh friend list for new friend
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
                    // fetch
                    const response = await fetch(`/api/users/declinefollow.php?rid=${user.RequestID}`);
                    const data = await response.json();
                    // remove friend from html
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
    // Loads a list of friends onto the page
    const list = document.getElementById("friends-list");
    list.innerHTML = "";
    try {
        // fetch
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
        // for each user thats a friend load a preview of the account
        data.users.forEach(user => {
            const div = document.createElement("div");
            div.classList.add("friend-item");
            div.innerHTML = `
                <a href="../dashboard/profile.php?Username=${user.Username}" class="noline">
                    <a href="../dashboard/profile.php?Username=${user.Username}"><img src="../content/profiles/${user.PFP}" alt="Profile Photo"></a>
                    <div class="friend-details">
                        <a class="noline" href="../dashboard/profile.php?Username=${user.Username}"><strong>${user.Username}</strong></a>
                    </div>
                    <div class="follow-actions">
                        <button id="friend-remove-${user.UserID}" class="decline">Remove</button>
                    </div>
                </a>
            `;
            list.appendChild(div);
            // add a remove friend button
            const remove = document.getElementById(`friend-remove-${user.UserID}`);
            // add event listener to remove button
            remove.addEventListener("click", async () => {
                try {
                    // fetch
                    const response = await fetch(`/api/users/removefriend.php?uid=${user.UserID}`);
                    const data = await response.json();
                    // remove friend from html
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

async function GetConversations() {
    // get the list of active conversations
    const conversations = document.getElementById("conversationsList");
    const chat = document.getElementById("chatMessages");
    const toggleList = document.getElementById("toggleList");
    try {
        // create a search bar for initiating a new conversation or finding a specific user to chat with
        conversations.innerHTML = "";
        const search = document.createElement("div");
        search.classList.add("conversation-header");
        search.innerHTML = `<input type="text" id="searchBar" placeholder="Search Users">`;
        conversations.appendChild(search);
        const result = document.createElement("div");
        result.classList.add("conversation-item");
        result.style.display = "none";
        conversations.appendChild(result);
        const searchinp = document.getElementById("searchBar");
        // add event listener to query each input of search bar in messages
        searchinp.addEventListener("input", async () => {
            // get search query
            const query = searchinp.value.trim();
            result.innerHTML = "";
            // remove result so it doesnt appear buggy before a successful load
            result.style.display = "none";
            if (!query) return;
            try {
                // fetch
                const response = await fetch(`/api/messages/searchusers.php?query=${query}`);
                const data = await response.json();
                if (!data.success || !data.user) return;
                currentUserID = data.user.UserID;
                // display the result
                result.style.display = "flex";
                // load user preview
                result.innerHTML = `
                    <img src="../content/profiles/${data.user.PFP}" alt="Profile Photo">
                    <strong>${data.user.Username}</strong>
                `;
                // add event listener for the result so the conversation can be initiated or opened
                result.onclick = () => {
                    if (currentUserID) OpenConversation();
                };
            } catch (e) {
                console.error(e);
                result.innerHTML = "<p style='text-align: center;'>No User Found</p>";
                result.style.display = "flex";
            }
        });
        // for mobile - toggle between the conversation list and message area
        if (toggleList) toggleList.addEventListener("click", () => {
            conversations.classList.toggle("active");
        });
        // fetch active conversations
        const response = await fetch(`/api/messages/loadopenconversations.php`);
        const data = await response.json();
        // check if there are any active conversations
        if (!data.success || !data.conversations || data.conversations.length === 0) {
            const msg = document.createElement("p");
            msg.id = "noconv";
            msg.style.textAlign = "center";
            msg.textContent = "No conversations yet";
            conversations.appendChild(msg);
            chat.innerHTML = "<p>Open a conversation</p>";
            return;
        }
        // for each convo - add a profile preview and update current id for when the user clicks on the conversation to open it
        data.conversations.forEach(conv => {
            const div = document.createElement("div");
            div.classList.add("conversation-item");
            div.innerHTML = `
                <img src="../content/profiles/${conv.PFP}" alt="Profile Photo">
                <div>
                    <strong>${conv.Username}</strong><br>
                    <small>${conv.LastMessage}</small>
                </div>
            `;
            // when they click on the respective user, open the conversation and load the messages in the message box
            div.addEventListener("click", () => {
                currentUserID = conv.UserID;
                OpenConversation()
            });
            conversations.appendChild(div);
        });
        chat.innerHTML = "<p>Open a conversation</p>";
    } catch (e) {
        console.error("Error loading conversations:", e);
        conversations.innerHTML = `<p style="text-align:center;">Failed to load conversations.</p>`;
    }
}

async function OpenConversation() {
    // Opens any conversation, active or not, and displays all messages most recently
    const chat = document.getElementById("chatMessages");
    chat.innerHTML = "";
    try {
        // fetch - send via post to php
        const form = new FormData();
        form.append("UserID", currentUserID);
        const response = await fetch(`/api/messages/requestmessages.php`, {
            method: "POST",
            body: form,
        });
        // error
        const data = await response.json();
        if (!data.success) {
            chat.innerHTML = `<p>Failed to load messages</p>`;
            return;
        }
        // no messages
        if (data.messages.length === 0) {
            chat.innerHTML = `<p>No messages</p>`;
            return;
        }
        // for each message that there is, create its own container and load
        data.messages.forEach(message => {
            const div = document.createElement("div");
            // decide if its sent or recieved to display respectively via style
            if (message.SenderID === uid) {
                div.classList.add("chat-message", "sent");
            } else {
                div.classList.add("chat-message", "received");
            }
            // add the message content and the time since "this was the only buggy thing i found, the time doesnt load properly in styling until a refresh"
            div.innerHTML = `
                ${message.Message}
                <small style='display: block; font-size: 0.7rem; color: #333333; text-align: right; padding: 0'>${timesince(message.SentAt)}</small>
            `;
            chat.appendChild(div);
        });
        chat.scrollTop = chat.scrollHeight;
    } catch (e) {
        console.error(e);
        chat.innerHTML = "<p>Failed to load messages</p>";
    }
}