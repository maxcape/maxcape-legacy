<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions();

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $id = mysql_real_escape_string($_POST['logid']);

    $loguserid = $dbf->queryToText("SELECT UserID FROM logs WHERE LogID = '$id'");

    if($loguserid === $_SESSION['userid']) {
        $dbf->query("DELETE FROM logitems WHERE LogID='$id'");
        $dbf->query("DELETE FROM logtripitems WHERE LogTripID IN (SELECT LogTripID FROM logtrips WHERE LogID='$id')");
        $dbf->query("DELETE FROM logtrips WHERE LogID='$id'");
        $dbf->query("DELETE FROM logsfavorites WHERE LogID='$id'");
        $dbf->query("DELETE FROM logs WHERE LogID='$id'");
    }