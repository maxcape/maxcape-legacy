<?php
    session_start();

    require_once("../../dbfunctions.php");
    require_once("../../userfunctions.php");
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $isloggedin = $uf->isLoggedIn();

    if($isloggedin) {
        $db = $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

        $userid = $_SESSION['userid'];
        $capeid = mysql_real_escape_string($_POST['capeid']);

        $dbf->query("INSERT INTO capeflags (UserID, CapeID, Date, Flag) VALUES ('$userid', '$capeid', NOW(), '1')");

        $dbf->disconnectFromDatabase($db['handle']);
    }