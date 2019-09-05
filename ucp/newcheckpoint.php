<?php
    session_start();
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once("../rsfunctions.php");
    $rsf = new rsfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $userid = mysql_real_escape_string($_SESSION[ 'userid' ]);

        if ( count( $dbf->queryToAssoc( "SELECT * FROM checkpoints WHERE Time >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND UserID='$userid'" ) ) == 0 ) {
            $username = $dbf->queryToText( "SELECT RSN FROM users WHERE UserID='$userid'" );

            $data = $rsf->updatePlayer($username);

            $response = $data[0];
            $data = $data[1];

            if ( !strstr( $data, "<html>" ) ) {
                if ( $data != "" ) {
                    $dbf->query( "INSERT INTO checkpoints (UserID, Time) VALUES ('$userid', NOW())" );
                    $checkpointid = mysql_insert_id();

                    $data = preg_split( "/\s+/", $data );

                    for ( $i = 1; $i < 28; $i++ ) {
                        $skillid = $i;
                        $skill   = explode( ",", $data[ $i - 1 ] );
                        $rank    = $skill[ 0 ];
                        $level   = $skill[ 1 ];
                        $exp     = $skill[ 2 ];

                        $dbf->query( "INSERT INTO checkpointdata (CheckpointID, SkillID, Rank, Level, Experience) VALUES ('$checkpointid', '$skillid', '$rank', '$level', '$exp')" );
                    }
                    echo "Checkpoint successfully set!";
                } else {
                    echo "Unknown Error";
                }

            } else {
                echo "Your set RSN isn't valid";
            }
        }
        else {
            echo "Checkpoint last set less then 24 hours ago.";
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }