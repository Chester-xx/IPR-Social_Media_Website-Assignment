<?php

    // --- CONSTANTS ---
    define("PFP", __DIR__ . "/../content/profiles/");
    define("POST", __DIR__ . "/../content/posts/");
    define("ACCEPTED_FILES", ["jpg", "jpeg", "png", "webp", "gif", "webm", "mp4", "mov"]);
    define("ACCEPTED_PFP", ["jpg", "jpeg", "png", "webp"]);
    // --- CONSTANTS END ---

    function Error(string $error, string $message, bool $pure_text = false): void {
        // tests if an error of '$error' exists from GET and outputs the specified message
        // if pure_text is specified to true, the error message will not be formated but printed as a pure string
        // this makes it easier for me to print errors to inputs like textarea
        if (isset($_GET["error"]) && $_GET["error"] == $error) {
            if ($pure_text) { echo(htmlspecialchars($message, ENT_QUOTES, "UTF-8")); }
            else { echo("<span class=\"error\">" . htmlspecialchars($message, ENT_QUOTES, "UTF-8") . "</span>"); }
        } else if ($error == "None") {
            if ($pure_text) { echo(htmlspecialchars($message, ENT_QUOTES, "UTF-8")); }
            else { echo("<span class=\"error\">" . htmlspecialchars($message, ENT_QUOTES, "UTF-8") . "</span>"); }
        }
    }

    function DBSesh(): mysqli | null {
        // creates a db session id for accessing dbconnect.php
        $_SESSION["dbconnect"] = true;

        // start session
        StartSesh();
        // if the user tried to start a connection with the database illegally
        if (!isset($_SESSION["dbconnect"])) {
            header("Location: /login/index.php");
            exit();
        }
        // unset for next db request
        unset($_SESSION["dbconnect"]);
        // try create a connection to mysqli
        try {
            $conn = mysqli_connect("localhost", "root", "", "dbconnect");
        } catch (mysqli_sql_exception) {
            // out errors to page
            exit("Error: Could not establish a connection with the database");
        }
        return $conn;
    }

    function StartSesh(): void {
        // checks if a session already exists and creates one if it doesnt
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // NBNBNBNBN!!!
    function RunQuery(mysqli | null $conn = null, string $Query, string $ExpectedResult, string $Redirector, string $VariableTypes = "", mixed ...$Variables): mysqli_result | int | array {
        // $ExpectedResult - Refers to how the query is expected to behave
        // Meaning, does it change the database (insert, delete, update) ? OR do we expect no result to be returned or atleast one result (select) 
        try {
            if ($conn !== null) {
                $connection = $conn;
                $closeconn = false;
            } else {
                $connection = DBSesh();
                $closeconn = true;
            }
            // connection failed
            if (!$connection) throw new Exception("Failed to connect to the database", 503);
            // prepare statement between db and API
            $stmt = $connection->prepare($Query);
            // failed query preperation
            if (!$stmt) throw new Exception("Failed to prepare query:" . $connection->error, $connection->errno);
            // check if the query has parameters -> bind them
            if ($VariableTypes !== "") $stmt->bind_param($VariableTypes, ...$Variables);
            // check execution success
            if (!$stmt->execute()) throw new Exception("Query execution failed: " . $stmt->error, $stmt->errno);
            // check if the query is a select statement
            $selectstmt = stripos(strtolower(trim($Query)), "select") === 0;
            // if its a select query, return mysqli_result, otherwise return int affected rows
            $result = $selectstmt ? $stmt->get_result() : $stmt->affected_rows;
            // check if the result failed
            if ($selectstmt && $result === false) throw new Exception("Failed to fetch result: " . $stmt->error, $stmt->errno);
            // Run checks on query results or statements
            switch (strtolower($ExpectedResult)) {
                case "query":
                    CheckQueryResult($result, $stmt, $connection, $Redirector);
                    break;
                case "noquery":
                    CheckNoQueryResult($result, $stmt, $connection, $Redirector);
                    break;
                case "change":
                    CheckChangeFail($stmt, $connection, $Redirector);
                    break;
                case "none":
                    // custom checks like in process.php
                    break;
                default:
                    throw new Exception("Non query check present or null", 1);
            }
            // close statement and connection - leave result, we dont run $result->free()
            $stmt->close();
            if ($closeconn) $connection->close();
            return $result;
        } catch (Exception $e) {
            return ["success" => false, "error" => ["message" => $e->getMessage(), "code" => $e->getCode()]];
        }
    }

    function CatchDBError(mixed $Result, bool $APICALL = false): bool {
        // I choose whether to assign the return value, if its under my use case
        if ($APICALL) {
            if (is_array($Result) && array_key_exists("error", $Result)) {
                return true;
            }
        } else if (is_array($Result) && array_key_exists("error", $Result)) {
            $error = $Result["error"];
            Error("None", ($error["message"] ?? "Unknown error") . ", Code: " . ($error["code"] ?? "N/A"));
            return true;
        }
        return false;
    }

    function CheckQueryResult(mysqli_result | bool $result, mysqli_stmt | bool $statement, mysqli | bool $connection, string $message): void {
        // checks if a queried result exists, if NOT frees resources and redirects to the desired page
        if ($result->num_rows < 1) {
            $statement->close();
            $result->free();
            $connection->close();
            header("Location: $message");
            exit();
        }
    }

    function CheckNoQueryResult(mysqli_result | bool $result, mysqli_stmt | bool $statement, mysqli | bool $connection, string $message): void {
        // checks if a queried result DOES NOT EXIST, if it does exist frees resources and redirects to the desired page
        if ($result->num_rows > 0) {
            $statement->close();
            $result->free();
            $connection->close();
            header("Location: $message");
            exit();
        }
    }

    function CheckChangeFail(mysqli_stmt | bool $statement, mysqli | bool $connection, string $message): void {
        // checks if insertion OR update to the database has failed
        if ($statement->affected_rows < 1) {
            $statement->close();
            $connection->close();
            if ($message != "None") {
                header("Location: " . $message);
                exit();
            }
        }
    }

    function PrintHeader(): void {
        // outputs the content of header.php to the page
        include_once("../includes/header.php");
    }

    function IsLoggedIn(): bool {
        // returns true or false based on if the users id has been set in session
        return isset($_SESSION["UserID"]);
    }

    function CheckLogIn(): void {
        // checks if the user has previously logged in, if so redirects them to the dashboard
        if (IsLoggedIn()) {
            header("Location: /dashboard/");
            exit();
        }
    }

    function CheckNotLoggedIn(): void {
        // checks if the user is not logged in, if so redirects them to the login page
        if (!IsLoggedIn()) {
            header("Location: /login/");
            exit();
        }
    }

?>