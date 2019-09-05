<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $userid = $_SESSION['userid'];

    $userlevel = $dbf->queryToText("SELECT Privelegelevel FROM users WHERE UserID='$userid'");
    if($userlevel >= "4") {
        $cn = mysql_real_escape_string($_POST['title']);

        $dbf->query("INSERT INTO forumcategories (Title) VALUES ('$cn')");
    } else {
        echo "User does not have permission to do this!";
    }