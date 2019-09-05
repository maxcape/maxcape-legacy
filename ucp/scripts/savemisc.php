<?php
    session_start();
    require_once("../../dbfunctions.php");
    require_once("../../userfunctions.php");
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $isloggedin = $uf->isLoggedIn();

    if(!$isloggedin) {
        header("Location: /nr");
    }

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $userid = $_SESSION['userid'];

    $hidersn = mysql_real_escape_string($_POST['rsnvis']);
    $sigbg = mysql_real_escape_string($_POST['sigbg']);
    $sigtxt = mysql_real_escape_string($_POST['sigtxt']);
    $commentbg = mysql_real_escape_string($_POST['cmntbg']);

    $dbf->query("UPDATE users SET HideRSN='$hidersn', SigBGColor='$sigbg', SigTxtColor='$sigtxt', commentbgcolor='$commentbg' WHERE UserID='$userid'");