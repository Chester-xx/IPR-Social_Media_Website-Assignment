# IPR Assignment 2nd Semester
- Social Media Website | @Connect
- XAMPP Server management
- Login state managed with Sessions
- Unauthorized page access managed by users with session states and header tracking for displayed pages

## File Structure
- All files fall under the "htdocs" directory in a XAMPP installation for ease of use
- This obviously makes private files and directories more unsafe and easier to access through domain/dir, such as the 'content' directory
- I did it this way to make it easier to manage the files and for testing purposes on the invigilators end

## Security Concerns
- If i could i would make the following changes:
    - Full api control rather than inline querying with some api control
    - Move both 'content', 'includes' and 'api' folders outside of the public directory, reducing how easily they can be accessed
    - i learned that filtering has been depricated as of 8.1, so moving forward, i would use htmlspecialchars with respect to ENT_QUOTES and UTP-8 as the encoding method
    - PHP MyAdmin has not been set up at all, but due to the nature of this project it is not needed
    - I also would introduce ini prompts to deny access to any content within the previously mentioned folders

## Database Structure
- dbConnect - Main Database
    ### tblUsers | Stores user data
        - | UserID | PK - int - auto_inc - not null
        - | Email | Unique - varchar(255) - not null
        - | Password | Hashed - varchar(255) - not null
        - | Username | Unique - varchar(50)  - not null
        - | pfpPath | - varchar(255) - default - "default.jpg"
    
    ### tblPasswordResets | Stores temporary password reset requests
        - | ResetID | PK - int - auto_inc - not null
        - | Email | FK - varchar(255) - not null
        - | Token | hashed - varchar(255) - not null
        - | Expires | datetime - current_timestamp - not null
    
    ###  tblPosts - Stores post information
        - | PostID | PK - int - auto_inc - not null
        - | UserID | FK - int - not null
        - | Content | text - not null
        - | Image | varchar(255) - default - null
        - | CreateTime | datetime - current_timestamp - not null
        - | Likes | int - not null

    ### tblLikes - Stores each like for a specific post
        - | LikeID | PK -int - auto_inc - not null
        - | UserID | FK - int - not null
        - | PostID | FK - int - not null

    ### tblMessages - Stores private message information
        - | MessageID | PK - int - auto_inc - not null
        - | SenderID | FK - int - not null
        - | RecieverID | FK - int - not null
        - | Message | text - not null
        - | SentAt | datetime - current_timestamp - not null

    ## Other Database Information
    - Images that can be uploaded, are stored as relative file names
    - The images are not stored within the database in order to focus on efficiency & database security
    - Passwords are hashed before storage, using the standard hashing algorithm SHA256
    - MySQL database storage, connection using mysqli in PHP, management in phpmyadmin

## Landing Page
- Redirects users to the Log In page or Dashboard relative to their session ID
- Creates a session, checking if a connection has a user ID
- If the user has one, it means that they are logged in and must be redirected to the Dashboard
- If not, the user must be redirected to the Log In page

## Logging In
- The user must enter the following VALID information:
    - Email
    - Password
- Comparisons are made on whether the email exists, and then if the password matches the password token stored in the database with respect to that email.
- Once the correct information is entered, the user will be redirected to the dashboard.

## Creating an Account
- The user must enter the following VALID information:
    - Email
    - Full Name (not validated on any naming metric)
    - Username
    - Password
    - Repeated password
- Once the user has successfully filled in valid entries, comparisons are made on the email and usernames, to check that accounts dont exist already with the entered information.
- Passwords are checked to ensure they are the same.
- the users information is processed and stored in the database, allowing them to log in.

## After Successfully Creating An Account
- The user is redirected to the success.php page.
- This is where the user is notified of their account creation
- The user is then granted to optionally upload their profile now at the same time
- Once they confirm, they will be redirected to the log in page