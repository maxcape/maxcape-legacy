<?php
    session_start();
    error_reporting( 0 );
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $userid = mysql_real_escape_string( $_SESSION[ 'userid' ] );
        $type   = mysql_real_escape_string( $_POST[ 'type' ] );

        $subreqs = $dbf->getAllAssocResults( "SELECT *
                                             FROM subrequirements
                                             WHERE RequirementID IN (
                                                 SELECT RequirementID
                                                 FROM requirements
                                                 WHERE CapeType = '$type'
                                             )" );

        foreach ( $subreqs as $req ) {
            $id    = $req[ 'SubrequirementID' ];
            $value = $_POST[ 'sub-' . $id ];

            if ( $value == "" ) {
                $value = 0;
            }
            else if ( $value == "on" ) {
                $value = 1;
            }

            if ( !is_numeric( $value ) ) {
                $value = 0;
            }

            if ( $value > $req[ 'Number' ] ) {
                $value = $req[ 'Number' ];
            }

            $dbf->query( "INSERT INTO userrequirements
                            (UserID, SubrequirementID, Value)
                         VALUES
                            ('$userid', '$id', '$value')
                         ON DUPLICATE KEY UPDATE
                            Value='$value'" );

            echo mysql_error();
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }