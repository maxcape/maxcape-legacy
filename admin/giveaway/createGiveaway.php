<?php
    session_start();
    require_once("../../dbfunctions.php");
    require_once("../../userfunctions.php");
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    if($loggedin) {
        $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

        $userid      = $_SESSION[ 'userid' ];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if ( $permissions >= 5 ) {
            $giveawaytitle = mysql_real_escape_string($_POST['title']);
            $startDate = mysql_real_escape_string($_POST['startDate']);
            $endDate = mysql_real_escape_string($_POST['endDate']);
            $desc = mysql_real_escape_string($_POST['desc']);

            $dbf->query("INSERT INTO giveaways (Title, Description, StartDate, EndDate) VALUES ('$giveawaytitle', '$desc', '$startDate', '$endDate')");
            echo mysql_error();
            $giveawayID = mysql_insert_id();

            $prizes = $_POST['prizeList'];

            foreach($prizes as $number => $prize) {
                $dbf->query("INSERT INTO giveawayprizes (GiveawayID, PrizeNumber, Prize) VALUES ('$giveawayID', '$number', '$prize')");
            }
        }
    }
