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

    $postid =    mysql_real_escape_string($_POST['id']);
    $mdcontent =   $_POST['md'];
    $htmlcontent = $_POST['html'];
    $userid = $_SESSION['userid'];
    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

    $mypost = $dbf->queryToAssoc("SELECT * FROM forumposts WHERE PostID='$postid' AND UserID='$userid'");

    $html = $purifier->purify($htmlcontent);

    if(intval($userlevel) < 4) {
        $htmlcontent = mysql_real_escape_string($purifier->purify($html));
        $mdcontent = mysql_real_escape_string($purifier->purify($mdcontent));
    } else {
        $htmlcontent = mysql_real_escape_string($htmlcontent);
        $mdcontent = mysql_real_escape_string($mdcontent);
    }

    if(count($mypost) != 0 || intval($userlevel) >= 3) {
        $dbf->query("UPDATE forumposts SET MDContent='$mdcontent', HTMLContent='$htmlcontent', EditDate=NOW(), EditCount = EditCount + 1, EditUserID = '$userid' WHERE PostID='$postid'");
    }

    print_r($html);