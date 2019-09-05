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
        $action = mysql_real_escape_string($_POST['action']);

        if($action == "add") {
            $dbf->query("INSERT INTO capefavorites (UserID, CapeID, Date) VALUES ('$userid', '$capeid', NOW())");
        } else {
            $dbf->query("DELETE  FROM capefavorites WHERE UserID='$userid' AND CapeID='$capeid'");
        }


        $dbf->disconnectFromDatabase($db['handle']);
    }