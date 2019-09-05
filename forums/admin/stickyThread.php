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

    $threadid = $_POST['threadid'];
    $threadid = mysql_real_escape_string($threadid);

    $stickyStatus = $dbf->queryToText("SELECT Sticky FROM forumthreads WHERE ThreadID='$threadid'");

    if($stickyStatus == "0") {
        $dbf->query("UPDATE forumthreads SET Sticky=1 WHERE ThreadID='$threadid'");
        echo "sticky";
    } else {
        $dbf->query("UPDATE forumthreads SET Sticky=0 WHERE ThreadID='$threadid'");
        echo "unsticky";
    }