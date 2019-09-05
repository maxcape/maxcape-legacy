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
        $regex            = '/(https?:\/\/|www\.)[\w\._-]+\.[a-zA-Z]{1,5}\/?([\w\.\?\+\_%=\/\&\#\,-]+)?/';
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
    $postid        = $_POST[ 'postid' ];
    $userid        = $_SESSION[ 'userid' ];
    $replyid       = isset( $_POST[ 'replyid' ] ) ? $_POST[ 'replyid' ] : NULL;

    if ( $userid != "" && $userid != 0 ) {
        $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

        $comment = mysql_real_escape_string( $postedcomment );
        $postid  = mysql_real_escape_string( $postid );
        $userid  = mysql_real_escape_string( $userid );
        $replyid = mysql_real_escape_string( $replyid );

        $accesslevel = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid'" );

        if ( $replyid != NULL ) {
            $dbf->query( "INSERT INTO comments (UserID, PostID, PostDate, Content, ReplyID) VALUES ('$userid', '$postid', NOW(), '$comment', '$replyid')" );
        } else {
            $dbf->query( "INSERT INTO comments (UserID, PostID, PostDate, Content) VALUES ('$userid', '$postid', NOW(), '$comment')" );
        }

        $commentid = mysql_insert_id();

        $user = $dbf->queryToAssoc( "SELECT * FROM users WHERE UserID='$userid'" );

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );

        $date = strtotime( 'now' );

        if ( $replyid == NULL ) {
            ?>
            <div class="comment" id="comment-<?php echo $commentid; ?>">
                <img class="user-portrait" src="http://services.runescape.com/m=avatar-rs/<?php echo $user[ 'RSN' ]; ?>/chat.png">
                <div class="arrow-left"></div>
                <div class="comment-inner">
                    <div class="comment-header">
                        <a class="username" href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $user[ 'Username' ]; ?>"><?php echo $user[ 'Username' ]; ?></a> <a
                            class="rsn"
                            href="<?php echo $dbf->basefilepath; ?>calc/<?php echo $user[ 'RSN' ]; ?>"><?php echo $user[ 'RSN' ]; ?></a>

                        <p class="post-date"><?php echo date( "M jS, Y", $date ) ?></p>
                    </div>

                    <div class="comment-content">
                        <?php echo $postedcomment; ?>
                    </div>

                    <div class="comment-options">
                        <a class="replybtn" href="javascript:void(0)" onclick="reply('<?php echo $commentid; ?>');">Reply</a> <a class="flagbtn" href="javascript:void(0)"
                                                                                                                                 onclick="flag('<?php echo $commentid; ?>');">Flag</a>

                        <?php
                        if ( $userid === $_SESSION[ 'userid' ] ) {
                            ?>
                            <a class="deletebtn" href="javascript:void(0)" onclick="deleteComment('<?php echo $commentid; ?>', 0);">Delete</a>
                            <a class="editbtn" href="javascript:void(0)" onclick="edit('<?php echo $commentid; ?>');">Edit</a>
                            <a class="cancelbtn" href="javascript:void(0)" onclick="cancelEdit('<?php echo $commentid; ?>');">Cancel</a>
                        <?php
                        }

                        if ( $accesslevel > 0 ) {
                            ?>
                            <a class="harddeletebtn" href="javascript:void(0)" onclick="deleteComment(<?php echo $commentid ?>, 0, 1)">Hard Delete</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

        <?php
        } else {
            ?>
            <div class="comment" id="comment-<?php echo $commentid ?>">
                <div class="comment-inner">
                    <div class="comment-header">
                        <img class="user-portrait-mini" src="http://services.runescape.com/m=avatar-rs/<?php echo $user[ 'RSN' ]; ?>/chat.png">
                        <a class="username" href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $user[ 'Username' ]; ?>"><?php echo $user[ 'Username' ]; ?></a> <a
                            class="rsn" href="<?php echo $dbf->basefilepath; ?>calc/<?php echo $user[ 'RSN' ]; ?>"><?php echo $user[ 'RSN' ]; ?></a>

                        <p class="post-date"><?php echo date( "M jS, Y", $date ) ?></p>
                    </div>

                    <div class="comment-content">
                        <?php echo str_replace( "\\", "", $postedcomment ); ?>
                    </div>

                    <div class="comment-options">
                        <a class="flagbtn" href="javascript:void(0)" onclick="flag('<?php echo $commentid; ?>');">Flag</a>

                        <?php
                        if ( $userid === $_SESSION[ 'userid' ] ) {
                            ?>
                            <a class="deletebtn" href="javascript:void(0)" onclick="deleteComment('<?php echo $commentid; ?>', 1);">Delete</a>
                            <a class="editbtn" href="javascript:void(0)" onclick="edit('<?php echo $commentid ?>');">Edit</a>
                            <a class="cancelbtn" href="javascript:void(0)" onclick="cancelEdit('<?php echo $commentid; ?>');">Cancel</a>
                        <?php
                        }

                        if ( $accesslevel > 0 ) {
                            ?>
                            <a class="harddeletebtn" href="javascript:void(0)" onclick="deleteComment(<?php echo $commentid ?>, 1, 1)">Hard Delete</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php
        }
    } else {
        echo "1";
    }
}
?>
