<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $userid = $_SESSION['userid'];

    $userlevel = $dbf->queryToText("SELECT Privelegelevel FROM users WHERE UserID='$userid'");
    if($userlevel >= "4") {
        $cid = mysql_real_escape_string($_POST['id']);
        $cn = mysql_real_escape_string($_POST['title']);

        $dbf->query("UPDATE forumcategories SET Title='$title' WHERE CategoryID='$cid'");
    } else {
        echo "User does not have permission to do this!";
    }