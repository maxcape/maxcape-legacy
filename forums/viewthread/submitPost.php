<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;
    require_once "../htmlpurify/HTMLPurifier.auto.php";

    $hpconfig = HTMLPurifier_Config::createDefault();
    $hpconfig->set('Core.Encoding', 'UTF-8');
    $hpconfig->set('HTML.Doctype', 'HTML 4.01 Transitional');

    $purifier = new HTMLPurifier($hpconfig);

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $threadid =    mysql_real_escape_string($_POST['threadid']);
    $mdcontent =   $_POST['md'];
    $htmlcontent = $_POST['html'];
    $userid = $_SESSION['userid'];
    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

    $locked = $dbf->queryToText("SELECT locked FROM forumthreads WHERE ThreadID='$threadid'");

    if($locked === "0") {
        if(intval($userlevel) < 4) {
            $htmlcontent = mysql_real_escape_string($purifier->purify($htmlcontent));
            $mdcontent = mysql_real_escape_string($purifier->purify($mdcontent));
        } else {
            $htmlcontent = mysql_real_escape_string($htmlcontent);
            $mdcontent = mysql_real_escape_string($mdcontent);
        }

        $dbf->query("INSERT INTO forumposts (ThreadID, UserID, PostDate, HTMLContent, MDContent) VALUES ('$threadid', '$userid', NOW(), '$htmlcontent', '$mdcontent')");
    }