<?php
    // SEARCH FOR A USER PAGE
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
    <title>@Connect | Find User</title>
</head>
<body>
    <?php PrintHeader(); ?>
    <!-- Search bar -->
    <div class="search-cont">
        <div class="search">
            <div class="search-bar">
                <input type="text" id="search_qry" placeholder="Search for a User">
            </div>
            <div id="note" style="text-align: center;">Users will appear here</div>
            <div class="search-results" id="search-results"></div>
        </div>
    </div>
    <script>
        // Dynamically load user account previews based on the users query by calling API
        const searchbar = document.getElementById("search_qry");
        const results = document.getElementById("search-results");
        // Ensure on the change of the search input
        searchbar.addEventListener("input", async () => {
            const qry = searchbar.value.trim();
            if (!qry) return;
            document.getElementById("note").innerHTML = "";
            results.innerHTML = `<small>Loading</small>`;
            try {
                // API Call
                const response = await fetch(`/api/search/searchusername.php?query=${qry}`);
                const data = await response.json();
                // Error outputs
                if (!data.success) {
                    results.innerHTML = `<p class="error">${data.error.message}, code: ${data.error.code}</p>`;
                    return;
                }
                if (data.users.length === 0) {
                    results.innerHTML = `<p>No users found</p>`;
                    return;
                }
                // Load profile previews from search result
                results.innerHTML = "";
                data.users.forEach(user => {
                    const div = document.createElement("div");
                    div.classList.add("result");
                    div.innerHTML = `
                        <a href="../dashboard/profile.php?Username=${user.Username}">
                            <img src="../content/profiles/${user.PFP}" alt="User profile photo">
                            <strong>${user.Username}</strong>
                        </a>
                    `;
                    results.appendChild(div);
                });
            } catch (e) {
                results.innerHTML = `<p class="error">Error fetching user results</p>`;
                console.error(e);
            }
        });
    </script>
</body>
</html>