<?php
    session_start();
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $rsn     = mysql_real_escape_string( $_POST[ 'rsn' ] );
        $email   = mysql_real_escape_string( $_POST[ 'email' ] );
        $visible = mysql_real_escape_string( $_POST[ 'visibility' ] );
        $userid  = $_SESSION[ 'userid' ];

        $dbf->query("UPDATE users SET RSN='$rsn', Email='$email', ProfileVisible='$visible' WHERE UserID='$userid'");

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }