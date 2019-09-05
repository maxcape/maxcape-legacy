<?php
    session_start();
    require_once( "../../dbfunctions.php" );
    require_once( "../../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf  = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    if ( $loggedin ) {
        $userid = $_SESSION[ 'userid' ];
        if ( $userid != 0 ) {

            $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

            $capeid    = mysql_real_escape_string( $_POST[ 'id' ] );
            $direction = mysql_real_escape_string( $_POST[ 'direction' ] );

            if ( $direction == "upvote" ) {
                $val = 1;
            } else {
                $val = -1;
            }

            $userhistory = $dbf->queryToText( "SELECT SUM(Direction) FROM capevotes WHERE UserID='$userid'" );

            if ( $userhistory > -10 || $val == 1 ) {
                $exists = $dbf->getAllAssocResults( "SELECT * FROM capevotes WHERE UserID='$userid' AND CapeID='$capeid' LIMIT 1" );

                if ( count( $exists ) == 0 ) {
                    $dbf->query( "INSERT INTO capevotes (UserID, CapeID, Direction, Date) VALUES ('$userid', '$capeid', '$val', NOW())" );

                    echo 0;
                } else {
                    if ( $exists[ 0 ][ 'Direction' ] != $val ) {
                        $voteid = $exists[ 0 ][ 'CapeVoteID' ];
                        $dbf->query( "UPDATE capevotes SET Direction='$val', Date=NOW() WHERE CapeVoteID='$voteid'" );

                        echo 0;
                    } else {
                        echo "You cannot make a repeat vote.";
                    }
                }
            } else {
                echo "You have downvoted too many capes. Please upvote some before downvoting any more.";
            }
        } else {
            echo "Unknown Error";
        }
    } else {
        echo "User is not logged in.";
    }
