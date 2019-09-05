<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $userid = $_SESSION['userid'];

    $userlevel = $dbf->queryToText("SELECT Privelegelevel FROM users WHERE UserID='$userid'");
    if($userlevel >= "4") {
        $sfn = mysql_real_escape_string($_POST['title']);
        $desc = mysql_real_escape_string($_POST['desc']);
        $cid = mysql_real_escape_string($_POST['catid']);

        $dbf->query("INSERT INTO forums (Title, Description, CategoryID) VALUES ('$sfn', '$desc', '$cid')");
        echo mysql_error();
    } else {
        echo "User does not have permission to do this!";
    }