<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;
    require_once "../htmlpurify/HTMLPurifier.auto.php";

    $hpconfig = HTMLPurifier_Config::createDefault();
    $hpconfig->set('Core.Encoding', 'UTF-8');
    $hpconfig->set('HTML.Doctype', 'HTML 4.01 Transitional');

    $purifier = new HTMLPurifier($hpconfig);

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database.");

    $forum = mysql_real_escape_string($_POST['forum']);
    $mdcontent = $_POST['md'];
    $htmlcontent = $_POST['html'];
    $title = mysql_real_escape_string($_POST['title']);
    $title = htmlentities($title);
    $subtitle = mysql_real_escape_string($_POST['subtitle']);
    $subtitle = htmlentities($subtitle);

    $userid = $_SESSION['userid'];
    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM USERS WHERE UserID='$userid'");

    if(intval($userlevel) < 5) {
        $htmlcontent = mysql_real_escape_string($purifier->purify($htmlcontent));
        $mdcontent = mysql_real_escape_string($purifier->purify($mdcontent));
    } else {
        $htmlcontent = mysql_real_escape_string($htmlcontent);
        $mdcontent = mysql_real_escape_string($mdcontent);
    }

    $dbf->query("INSERT INTO forumthreads (ForumID, UserID, Title, Subtitle, CreationDate) VALUES ('$forum', '$userid', '$title', '$subtitle', NOW())");
    $threadid = mysql_insert_id();

    $dbf->query("INSERT INTO forumposts (ThreadID, UserID, PostDate, HTMLContent, MDContent, IsMainPost) VALUES ('$threadid', '$userid', NOW(), '$htmlcontent', '$mdcontent', 1)");

    echo $threadid;