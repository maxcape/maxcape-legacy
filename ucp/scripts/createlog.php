<?php
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions();
    require_once "../../userfunctions.php";
    $uf = new userfunctions();

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database.");

    $loggedin = $uf->isLoggedIn();

    if($loggedin && $_SESSION['userid'] > 0) {
        $userid = $_SESSION['userid'];
        $title = mysql_real_escape_string($_POST['logname']);
        $title = htmlentities($title);
        $description = mysql_real_escape_string($_POST['logdesc']);
        $description = htmlentities($title);
        $type = mysql_real_escape_string($_POST['logtype'][0]);
        $cumulativelogs = $_POST['cumulativelogs'];

        if($type != "4") { //Log is not cumulative
            $dbf->query("INSERT INTO logs ( UserID, LogType, LogTitle, LogDescription, CreationDate ) VALUES ( '$userid', '$type', '$title', '$description', NOW() )");
            $logid = mysql_insert_id();

            echo "Log created with ID $logid";
        } else {
            $dbf->query("INSERT INTO logs ( UserID, LogType, LogTitle, LogDescription, CreationDate ) VALUES ( '$userid', '$type', '$title', '$description', NOW() )");
            $logid = mysql_insert_id();

            foreach($cumulativelogs as $log) {
                $log = mysql_real_escape_string($log);
                if($log != "0") {
                    $dbf->query("INSERT INTO logscumulative ( PrimaryLogID, SecondaryLogID) VALUES ( '$logid', '$log' )");
                }
            }
        }
    }