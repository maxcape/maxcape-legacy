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

    $lockStatus = $dbf->queryToText("SELECT Locked FROM forumthreads WHERE ThreadID='$threadid'");

    if($lockStatus == "0") {
        $dbf->query("UPDATE forumthreads SET Locked=1 WHERE ThreadID='$threadid'");
        echo "lock";
    } else {
        $dbf->query("UPDATE forumthreads SET Locked=0 WHERE ThreadID='$threadid'");
        echo "unlock";
    }