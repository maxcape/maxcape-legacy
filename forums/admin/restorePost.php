<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;
    require_once "../../userfunctions.php";
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    if(!$loggedin) {
        die("Error: You are not logged in.");
    }

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $userid = $_SESSION['userid'];
    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

    if($userlevel < 3) {
        die("Error: Insufficient user privileges.");
    }

    $postid = mysql_real_escape_string($_POST['postid']);

    if(intval($userlevel) >=  3) {
        $dbf->query("UPDATE forumposts SET IsDeleted=0 WHERE PostID='$postid'");
    } else {
        echo "This user is not a moderator UserID";
    }