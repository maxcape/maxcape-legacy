<?php
session_start();
require_once( "../dbfunctions.php" );
require_once( "../userfunctions.php" );
require_once("../rsfunctions.php");

$dbf = new dbfunctions;
$uf = new userfunctions;
$rsf = new rsfunctions;

$db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );
$loggedin = $uf->isLoggedIn();

if ( $loggedin ) {
    $userid = $_SESSION[ 'userid' ];
    $rsn    = $dbf->queryToText( "SELECT RSN FROM users WHERE UserID='$userid'" );

    if ( $userid != "" ) {

        $lastCacheUpdate = $dbf->queryToText( "SELECT TimeFetched FROM apicache WHERE RSN='$rsn'" );

        if ( strtotime( "now" ) - strtotime( $lastCacheUpdate ) < ( 3600 * 2 ) ) {

            $lastoverride = $dbf->queryToText( "SELECT LastCacheOverride FROM users WHERE UserID='$userid'" );

            if ( strtotime( 'now' ) - strtotime( $lastoverride ) >= ( 3600 * 4 ) ) {
                $rsf->updatePlayer( $rsn, false, true );

                $dbf->query( "UPDATE users SET LastCacheOverride=NOW() WHERE UserID='$userid'" );

                echo "0";
            } else {
                echo "1";
            }
        } else {
            echo "2";
        }
    } else {
        echo "3";
    }
} else {
    echo "4";
}

$dbf->disconnectFromDatabase($db['handle']);
