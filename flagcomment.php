<?php
    session_start();
    require_once( "dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "userfunctions.php" );
    $uf = new userfunctions();

    $loggedin = $uf->isLoggedIn();

    if ( $loggedin ) {
        $commentid = $_POST[ 'commentid' ];
        $userid    = $_SESSION[ 'userid' ];

        $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

        $userid = mysql_real_escape_string($userid);
        $commentid = mysql_real_escape_string( $commentid );

        $alreadyflagged = $dbf->queryToAssoc("SELECT * FROM commentflags WHERE UserID='$userid' AND CommentID='$commentid'");

        if(count($alreadyflagged) == 0) {
            $dbf->query("INSERT INTO commentflags (UserID, CommentID, Date) VALUES ('$userid', '$commentid', NOW())");
            echo "0";
        } else {
            echo "1";
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        echo "2";
    }