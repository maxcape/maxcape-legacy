<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;
    require_once "../../userfunctions.php";
    $uf = new userfunctions;

    $userid = $_SESSION['userid'];

    if($uf->isLoggedIn()) {
        $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database.");

        $logid = mysql_real_escape_string($_POST['logid']);
        $action = mysql_real_escape_string($_POST['action']);

        if($action == "favorite") {
            $dbf->query("INSERT INTO logsfavorites ( LogID, UserID ) VALUES ( '$logid', '$userid' )");
            echo mysql_error();
        } else if ($action == "unfavorite") {
            $dbf->query("DELETE FROM logsfavorites WHERE LogID='$logid' AND UserID='$userid'");
        } else {
            echo "Unknown command";
        }
    }