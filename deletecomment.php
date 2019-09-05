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
        $type      = $_POST[ 'type' ];
        $hard      = $_POST[ 'hard' ];

        $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

        $userid    = mysql_real_escape_string( $userid );
        $commentid = mysql_real_escape_string( $commentid );

        $override = false;

        if ( $hard == 1 ) {
            $accesslevel = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid'" );

            if ( $accesslevel >= 4 ) {
                $override = true;
                $type     = 3;
            } else {
                $override = false;
            }
        }

        $valid = $dbf->getAllAssocResults( "SELECT * FROM comments WHERE CommentID='$commentid' AND UserID='$userid'" );

        $accesslevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

        if ( count( $valid ) > 0 || $accesslevel > 0 ) {
            if ( !$override ) {
                $children = $dbf->getAllAssocResults( "SELECT * FROM comments WHERE ReplyID='$commentid'" );
                if ( count( $children ) == 0 ) {
                    $override = true;
                    $type     = 3;
                }
            }

            if ( $override ) {
                $dbf->query("DELETE FROM commentflags WHERE CommentID='$commentid'");
                $dbf->query( "DELETE FROM comments WHERE ReplyID='$commentid'" );
                $dbf->query( "DELETE FROM comments WHERE CommentID='$commentid'" );

            } else {
                $dbf->query( "UPDATE comments SET Deleted=1 WHERE CommentID='$commentid'" );
            }

            $date = strtotime( $valid[ 0 ][ 'PostDate' ] );

            if ( $type == 0 ) {
                ?>
                <div class="comment deleted">
                    <img class="user-portrait" src="http://services.runescape.com/m=avatar-rs/default_chat.png">
                    <div class="arrow-left"></div>
                    <div class="comment-inner">
                        <div class="comment-header">
                            <a class="username">[deleted]</a>

                            <p class="post-date"><?php echo date( "M jS, Y", $date ) ?></p>
                        </div>


                        <div class="comment-content">
                            <p class="flagged">This comment has been deleted.</p>
                        </div>
                    </div>
                </div>
            <?php
            } else {
                if ( $type == 1 ) {
                    ?>
                    <div class="comment-inner">
                        <div class="comment-header">
                            <img class="user-portrait-mini" src="http://services.runescape.com/m=avatar-rs/default_chat.png">
                            <a class="username">[deleted]</a>

                            <p class="post-date"><?php echo date( "M jS, Y", $date ) ?></p>
                        </div>


                        <div class="comment-content">
                            <p class="flagged">This comment has been deleted.</p>
                        </div>
                    </div>
                <?php
                }
            }
        } else {
            echo "1";
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        echo "2";
    }