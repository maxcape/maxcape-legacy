<?php
    session_start();
    require_once( "dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "userfunctions.php" );
    $uf = new userfunctions();

    $loggedin = $uf->isLoggedIn();

    if ( $loggedin ) {
        function parseComment( $comment ) {
            $conversions = array(
                "\r\n\r\n" => "</p><p>", "\n\n" => "</p><p>", "\r\n" => "<br>", "\n" => "<br>", "&amp;" => "&"
            );

            $comment = htmlentities( $comment );
            $comment = "<p>" . $comment;
            foreach ( $conversions as $original => $new ) {
                $comment = str_replace( $original, $new, $comment );
            }

            $maxdisplaylength = 25;
            $regex            = '/(https?:\/\/|www\.)[\w\._\-]+\.[a-zA-Z]{1,5}\/?([\w\.\?\+\-_%=\/\&\#\,]+)?/';
            if ( preg_match_all( $regex, $comment, $url ) ) {
                foreach ( $url[ 0 ] as $reallink ) {
                    if ( !substr( $reallink, 0, 7 ) === "http://" && !substr( $reallink, 0, 8 ) === "https://" ) {
                        $link = "http://" . $reallink;
                    } else {
                        $link = $reallink;
                    }

                    $displaylink = str_replace( "https://", "", $link );
                    $displaylink = str_replace( "http://", "", $displaylink );
                    $displaylink = str_replace( "www.", "", $displaylink );

                    if ( strlen( $displaylink ) > $maxdisplaylength ) {
                        $displaylink = substr( $displaylink, 0, ( strlen( $displaylink ) - ( strlen( $displaylink ) - $maxdisplaylength ) ) );
                        $displaylink .= "...";
                    }

                    $comment = str_replace( $reallink, "<a target='_blank' href='$link'>$displaylink</a>", $comment );
                }
            }

            $comment = $comment . "</p>";

            return $comment;
        }

        $postedcomment = parseComment( $_POST[ 'comment' ] );
        $commentid     = $_POST[ 'commentid' ];
        $userid        = $_SESSION[ 'userid' ];

        if ( $userid != "" && $userid != 0 ) {
            $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

            $userid = mysql_real_escape_string( $userid );

            $valid       = $dbf->getAllAssocResults( "SELECT * FROM comments WHERE CommentID='$commentid' AND UserID='$userid'" );
            $accesslevel = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid'" );
            if ( count( $valid ) > 0 || $accesslevel >= 4 ) {
                $comment   = mysql_real_escape_string( $postedcomment );
                $commentid = mysql_real_escape_string( $commentid );
                $dbf->query( "UPDATE comments SET Content='$comment' WHERE CommentID='$commentid'" );

                echo $postedcomment;
            } else {
                echo "1";
            }

            $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        } else {
            echo "3";
        }
    } else {
        echo "2";
    }