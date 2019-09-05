<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $userid = $_SESSION['userid'];

    $userlevel = $dbf->queryToText("SELECT Privelegelevel FROM users WHERE UserID='$userid'");
    if($userlevel >= "4") {
        $sfid = mysql_real_escape_string($_POST['id']);
        $sfn = mysql_real_escape_string($_POST['title']);
        $desc = mysql_real_escape_string($_POST['desc']);
        $cid = mysql_real_escape_string($_POST['category']);

        $dbf->query("UPDATE forums SET Title='$sfn', Description='$desc', CategoryID='$cid' WHERE ForumID='$sfid'");
        echo mysql_error();
    } else {
        echo "User does not have permission to do this!";
    }