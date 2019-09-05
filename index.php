<?php
    require_once( "dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "userfunctions.php" );
    $uf = new userfunctions;

    function time_elapsed_string( $ptime ) {
        $etime = time() - $ptime;

        if ( $etime < 1 ) {
            return '0 seconds';
        }

        $a = array(
            12 * 30 * 24 * 60 * 60 => 'year', 30 * 24 * 60 * 60 => 'month', 24 * 60 * 60 => 'day', 60 * 60 => 'hour', 60 => 'minute', 1 => 'second'
        );

        foreach ( $a as $secs => $str ) {
            $d = $etime / $secs;
            if ( $d >= 1 ) {
                $r = round( $d );
                return $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }
    }

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];
    $myuserid = $_SESSION[ 'userid' ];

    if ( isset( $_COOKIE[ 'maxcompcapename' ] ) && !isset( $_GET[ 'noredirect' ] ) && !isset( $_GET[ 'page' ] ) && $_GET[ 'action' ] != "viewpost" ) {
        header( "Location: calc/" . urlencode( $_COOKIE[ 'maxcompcapename' ] ) );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        if ( $loggedin ) {
            $email = $dbf->queryToText( "SELECT Email FROM users WHERE Username='$myusername'" );
        } else {
            $email = "";
        }

        if ( $myuserid != NULL ) {
            $accesslevel = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$myuserid'" );
        } else {
            $accesslevel = 0;
        }
        ?>
        <!DOCTYPE html>

        <html>
            <head>
                <?php
                    if ( $_GET[ 'action' ] != "viewpost" ) {
                        ?>
                        <title>Max/Comp Cape Calc</title>
                    <?php
                    } else {
                        $post_id   = mysql_real_escape_string( $_GET[ 'postid' ] );
                        $postTitle = $dbf->queryToText( "SELECT Headline FROM posts WHERE PostID='$post_id'" );
                        ?>
                        <title><?php echo $postTitle; ?> - Max/Comp Cape Calc</title>
                    <?php
                    }

                    if ( $_GET[ 'beta' ] == 1 ) {
                        ?>
                        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper2.css">
                        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/theme/news.css">
                    <?php
                    } else if($_GET['beta'] == 2) {
                        ?>
                        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper3.css">
                        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/theme2/news.css">
                        <?php
                    } else {
                        ?>
                        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/news.css">
                    <?php
                    }
                ?>


                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    var isReply = false;
                    var replyID = 0;

                    function htmlToText(str) {
                        //The function will attempt to replace text in the order they are listed, so list
                        //more complex replacements first.
                        var replace = {
                            "</p><p>": "\n\n",
                            "<br>": "\n",
                            "<p>": "",
                            "</p>": "",
                            "&amp;": "&"
                        };

                        str = str.trim();

                        for (var key in replace) {
                            var regex = new RegExp(key, 'g');
                            str = str.replace(regex, replace[key]);
                        }

                        //Replace any <a> tags with their href links.
                        var linkregex = new RegExp(/\<a.*>.+<\/a>/);
                        var hrefregex = new RegExp(/(https?:\/\/|www\.)[\w\._\-]+\.[a-zA-Z]{1,5}\/?([\w\.\?\+\_%=\/\&\#\-\,]+)?/);

                        if (linkregex.test(str)) {
                            var looping = true;

                            while (looping) {
                                var atag = linkregex.exec(str);

                                for (var i = 0; i < atag.length; i++) {
                                    var link = hrefregex.exec(atag[i])[0];
                                    str = str.replace(linkregex, link);
                                }

                                looping = linkregex.test(str);
                            }
                        }

                        return str;
                    }

                    function edit(id) {
                        var comment = $("#comment-" + id);
                        var html = comment.find(".comment-content").html();
                        var text = htmlToText(html);

                        var textarea = $("<textarea>").val(text).attr("id", "comment-edit-" + id).attr("data-original", html.trim());
                        comment.find(".comment-content").empty().prepend(textarea);

                        comment.find(".editbtn").text("Save").attr("onclick", "saveEdit(" + id + ")");
                        comment.find(".cancelbtn").css("display", "block");
                    }

                    function saveEdit(id) {
                        var comment = $("#comment-" + id);
                        var edited = comment.find("textarea").val();

                        comment.find(".editbtn").attr("onclick", "").empty().append($("<img>").attr("src", "<?php echo $dbf->basefilepath; ?>images/loader.gif"));

                        $.post("<?php echo $dbf->basefilepath; ?>editcomment.php", {
                            "comment": edited,
                            "commentid": id
                        }, function (data) {
                            if (data == "1") {
                                alert("Either you are not logged in or this is not your comment!");
                            } else if (data == "2") {
                                //Not logged in
                            } else if (data == "3") {
                                alert("There is something wrong with your login session. Please log out and back in.");
                            } else {
                                comment.find(".comment-content").empty().prepend(data);
                                comment.find(".editbtn").text("Edit").attr("onclick", "edit(" + id + ")");
                                comment.find(".cancelbtn").css("display", "none");
                            }
                        });
                    }

                    function cancelEdit(id) {
                        var cfm = confirm("Are you sure you want to cancel editing? All changes will be lost.");

                        if (cfm) {
                            var comment = $("#comment-" + id);

                            comment.find(".editbtn").text("Edit").attr("onclick", "edit(" + id + ")");
                            comment.find(".cancelbtn").css("display", "none");

                            var oldtxt = comment.find("textarea").attr("data-original");

                            comment.find(".comment-content").empty().append(oldtxt);
                        }
                    }

                    function reply(id) {
                        var comment = $("#comment-" + id);
                        var responding = comment.find(".username").text();

                        $("#reply-to").css("visibility", "visible");
                        $("#reply-to-name").text(responding);

                        isReply = true;
                        replyID = id;

                        location.href = "#leave-comment";
                        $("#comment-text").focus();
                    }

                    function removeReply() {
                        $("#reply-to").css("visibility", "hidden");

                        isReply = false;
                        replyID = false;
                    }

                    function showComment(el) {
                        var comment = el.parents(".comment");
                        var content = comment.find(".comment-content").attr("data-comment");

                        comment.find(".comment-content").empty().append(content);
                        comment.find(".comment-options").removeClass("withflag");

                        comment.find(".comment-content").attr("data-comment", "");
                    }

                    function flag(id) {
                        $.post("<?php echo $dbf->basefilepath; ?>flagcomment.php", { "commentid": id}, function (data) {
                            if (data == "0") {
                                $("#comment-" + id).find(".flagbtn").css("color", "green").attr("onclick", "").text("Flagged");
                                $("#comment-" + id).find(".flagbtn").prepend($("<i>").addClass("icon-ok"));
                            } else if (data == "1") {
                                alert("You have already flagged this comment!");
                            } else if (data == "2") {
                                //Not logged in.
                            }
                        });
                    }

                    function deleteComment(id, type, harddelete) {
                        if (typeof(harddelete) == "undefined") {
                            harddelete = 0;
                        }

                        var cnfm = confirm("Are you sure you want to permanently delete your comment?");

                        if (cnfm) {
                            $.post("<?php echo $dbf->basefilepath; ?>deletecomment.php", { "commentid": id, "type": type, "hard": harddelete}, function (data) {
                                if (data == "1") {
                                    alert("Either this comment does not exist or it is not your comment.");
                                } else if (data == "2") {
                                    //Not logged in.
                                } else {
                                    var cmnt = $("#comment-" + id);
                                    cmnt.addClass("deleted").empty().append(data);

                                    if (harddelete == 1) {
                                        var next = cmnt.next();

                                        if (next.hasClass("arrow-up")) {
                                            next.next().remove();
                                            next.remove();
                                            cmnt.remove();
                                        }
                                    }
                                }
                            });
                        }
                    }

                    $(document).ready(function () {
                        $(".close").click(function () {
                            $(this).parent().fadeOut();
                        });

                        $("#postcomment").submit(function (e) {
                            e.preventDefault();

                            var comment = $("#comment-text").val();

                            if (comment != "") {
                                if (!isReply) {
                                    $.post("<?php echo $dbf->basefilepath; ?>postcomment.php", {
                                        "comment": comment,
                                        "postid": <?php echo $_GET['postid'] != "" ? $_GET['postid'] : 0; ?>
                                    }, function (data) {
                                        $(".no-comments").remove();
                                        $("#comment-container").prepend(data);
                                    });
                                } else {
                                    $.post("<?php echo $dbf->basefilepath; ?>postcomment.php", {
                                        "comment": comment,
                                        "postid": <?php echo $_GET['postid'] != "" ? $_GET['postid'] : 0; ?>,
                                        "replyid": replyID
                                    }, function (data) {
                                        if (data != "1") {
                                            var cmnt = $("#comment-" + replyID);
                                            var next = cmnt.next();

                                            if (next.hasClass("arrow-up")) {
                                                var replies = next.next();
                                                replies.prepend(data);
                                            } else {
                                                var uparr = $("<div>").addClass("arrow-up");
                                                var replycnt = $("<div>").addClass("replies").append(data);

                                                uparr.insertAfter(cmnt);
                                                replycnt.insertAfter(uparr);
                                            }

                                            removeReply();
                                        } else {
                                            alert("There is something wrong with your login session. Please log out and back in.")
                                        }
                                    });
                                }
                            }

                            $("#comment-text").val("");
                        });

                        $("[class*='user-portrait']").each(function () {
                            $(this).load(function () {
                                //alert("Image loaded");
                            });

                            setTimeout(function () {
                                $(this).attr("src", "images/default_chat.png");
                            }, 500);
                        });
                    });
                </script>
            </head>

            <body>
                <?php $_GET['beta'] != 1 ? require_once( "masthead.php" ) : require_once("masthead_modern_theme.php"); ?>
                <div id="content">
                    <div id="maincontent">

                        <?php
                            if ( $email == "" && $loggedin ) {
                                ?>
                                <div class="alert-message alert">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p>Your account doesn't have an email associated with it, and you will be unable to recover your password. <br> <a
                                            href="<?php echo $dbf->basefilepath; ?>ucp/tab/0">Click here</a> to add one.</p>
                                </div>
                            <?php
                            }

		            if ( isset( $_GET[ 'temp' ] ) ) {
                                ?>
                                <div class="alert-message alert">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Temporarily down: </strong>The Calc is temporarily down while I look into an issue.</p>
                                </div>
                            <?php
                            }
                            if ( isset( $_GET[ 'badprofile' ] ) ) {
                                ?>
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Error: </strong>That profile doesn't appear to exist.</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'privateprofile' ] ) ) {
                                ?>
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Sorry</strong>, the requested profile has been set to private.</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'failedname' ] ) ) {
                                ?>
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Error: </strong>"<?php echo $_GET[ 'failedname' ]; ?>" doesn't appear to be in the highscores. Please check your spelling.</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'loggedout' ] ) ) {
                                ?>
                                <div class="alert-message info">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Success! </strong>You have been logged out. <a href="<?php echo $dbf->basefilepath; ?>user/login">Click here</a> to log back in.</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'notloggedin' ] ) ) {
                                ?>
                                <div class="alert-message alert">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Uh oh! </strong>You do not appear to be logged in. <a href="<?php echo $dbf->basefilepath; ?>user/login">Click here</a> to login, or <a
                                            href="login/?action=register">here</a> to register!</p>
                                </div>
                            <?php
                            }
                            if ( isset( $_GET[ 'permissionerror' ] ) ) {
                                ?>
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Error: </strong>You do not have sufficient permissions to view that page!</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'updated' ] ) ) {
                                ?>
                                <div class="alert-message info">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Success! </strong>You have updated your login. If you requested a reset, please check your email for the link (check your spam folder!)</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'resetrequested' ] ) ) {
                                ?>
                                <div class="alert-message info">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Success! </strong>Please check your email for your password reset link (check your spam folder!)</p>
                                </div>
                            <?php
                            }

                            if ( isset( $_COOKIE[ 'maxcompcapename' ] ) ) {
                                ?>
                                <div class="alert-message info">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Default Name: </strong><a href="calc/<?php echo $_COOKIE[ 'maxcompcapename' ]; ?>">Click here to go to your calculator page.</a></p>
                                </div>
                            <?php
                            }

                            if ( isset( $_GET[ 'passwordchanged' ] ) ) {
                                ?>
                                <div class="alert-message info">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Password Changed. </strong>Your password has been succesfully reset.</p>
                                </div>
                            <?php
                            }

                            if ( !isset( $_GET[ 'action' ] ) ) {
                                ?>
                                <div class="innercontent">
                                    <form id="usersearch" class="form-wrapper cf" action="/calc/">
                                        <input type="text" id="searchname" name="name" placeholder="Search..." required="required" pattern="^[a-zA-Z0-9_ ]*$">
                                        <button type="submit" id="submitbtn"
                                                onclick="(function(e) { e.preventDefault(); if($('#searchname').val() != '') { document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#searchname').val().replace(/ /g, '+'); } })(event)">Search
                                        </button>
                                    </form>

                                    <?php
                                    if($_GET['beta'] == 1) {
                                    ?>
                                    <div class="social">
                                        <a href="http://twitter.com/share?url=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>&via=The__Orange&hashtags=RuneScape&text=<?php echo urlencode( "RuneScape Max/Completionist Cape Calculator - " ) ?>"
                                           class="s3d twitter"> <span class="icon-twitter"></span> </a>

                                        <a href="http://www.tumblr.com/share/link?url=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>&name=<?php echo urlencode( "RuneScape Max/Completionist Cape Calculator" ); ?>&description=<?php echo urlencode( "The Max/Completionist Cape Calculator is a RuneScape tool that will help you figure out just how far (or close) you are from maxing your skills." ) ?>"
                                           class="s3d tumblr"> <span class="icon-tumblr"></span> </a>

                                        <a href="http://www.facebook.com/sharer.php?s=100&p[title]=<?php echo urlencode( "RuneScape Max/Completionist Cape Calculator" ); ?>&p[summary]=<?php echo urlencode( "The Max/Completionist Cape Calculator is a RuneScape tool that will help you figure out just how far (or close) you are from maxing your skills." ); ?> &p[url]=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>"
                                           class="s3d facebook"> <span class="icon-facebook"></span> </a>

                                        <a href="https://plus.google.com/share?url=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>" class="s3d googleplus"> <span class="icon-google-plus"></span> </a>
                                    </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            }

                            $MAX_POSTS_PER_PAGE = 5;
                            $pagenumber = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : 1;

                            if ( $_GET[ 'action' ] != "viewpost" ) { //Standard Front Page
                                $posts     = $dbf->getAllAssocResults( "SELECT * FROM posts WHERE Visible=1 ORDER BY Sticky DESC, Date DESC" );
                                $pagecount = ceil( count( $posts ) / $MAX_POSTS_PER_PAGE );

                                $minpost  = ( $pagenumber - 1 ) * $MAX_POSTS_PER_PAGE;
                                $maxpost  = ( $minpost + $MAX_POSTS_PER_PAGE ) - 1;
                                $numposts = 0;

                                foreach ( $posts as $i => $post ) {
                                    if ( $i >= $minpost && $i <= $maxpost ) {
                                        $numposts++;
                                        ?>
                                        <div class="innercontent">
                                            <h2>
                                                <a href="<?php echo $dbf->basefilepath; ?>post/<?php echo $post[ 'PostID' ]; ?>"><?php echo $post[ 'Headline' ]; ?></a>
                                            </h2>

                                            <?php
                                                echo str_replace( "\\", "", $post[ 'Content' ] );
                                            ?>

                                        </div>

                                        <div class="postinfo">

                    <span class="date">
                        <?php
                            if ( $post[ 'Sticky' ] == 1 ) {
                                ?>
                                <span class="icon-pushpin"></span>
                            <?php
                            }
                            $postid = $post[ 'PostID' ];
                            $numcomments = $dbf->queryToText( "SELECT COUNT(*) FROM comments WHERE PostID='$postid'" );
                        ?>
                        Posted <?php echo date( "F d, Y", strtotime( $post[ 'Date' ] ) ); ?> by <?php echo $post[ 'Author' ]; ?></span> <span class="comments"> <a
                                                    href="<?php echo $dbf->basefilepath; ?>post/<?php echo $post[ 'PostID' ]; ?>"><?php echo $numcomments; ?> Comments</a></span>
                                        </div>
                                    <?php
                                    }
                                }
                                ?>
                                <?php
                                if ( $numposts == 0 ) {
                                    ?>
                                    <div class="alert-message error">
                                        <a class="close icon-remove-sign" href="#"></a>

                                        <p><strong>Error: </strong>There doesn't seem to be anything here. <a href="<?php echo $dbf->basefilepath; ?>">Click here</a> to return to the front page.</p>
                                    </div>
                                <?php
                                }
                            } else { //INDIVIDUAL POST VIEW
                                $postID = mysql_real_escape_string( $_GET[ 'postid' ] );
                                $posts  = $dbf->getAllAssocResults( "SELECT * FROM posts WHERE PostID='$postID' AND Visible=1 ORDER BY Sticky DESC, Date ASC" );

                                if ( count( $posts ) == 0 ) {
                                    ?>
                                    <script>
                                        location.href = "<?php echo $dbf->basefilepath; ?>nr";
                                    </script>
                                    <?php
                                    die();
                                }
                                foreach ( $posts as $post ) {
                                    ?>
                                    <div class="innercontent">
                                        <h2>
                                            <a href="<?php echo $dbf->basefilepath; ?>post/<?php echo $post[ 'PostID' ]; ?>"><?php echo $post[ 'Headline' ]; ?></a>
                                        </h2>
                                        <span class="date"><?php echo date( "M d, Y", strtotime( $post[ 'Date' ] ) ); ?></span>

                                        <?php
                                            echo str_replace( "\\", "", $post[ 'Content' ] );
                                        ?>
                                        <hr>
                                        <div id="comments">
                                            <?php
                                                if ( $loggedin ) {
                                                    ?>
                                                    <div id="leave-comment">
                                                        <form id="postcomment" method="post" action="<?php echo $dbf->basefilepath; ?>postcomment.php">
                                                            <img class="user-portrait-large" src="http://services.runescape.com/m=avatar-rs/<?php echo urlencode($_SESSION[ 'rsn' ]); ?>/chat.png">
                                                            <textarea name="comment" id="comment-text" placeholder="Leave a comment..."></textarea>

                                                            <p id="reply-to">In Reply To: <span id="reply-to-name"></span> <a class="icon-remove-sign" title="Remove Reply" href="javascript:removeReply();"></a></p>
                                                            <input type="hidden" id="reply-to-id">
                                                            <button type="submit">Post Comment</button>
                                                        </form>
                                                    </div>
                                                <?php
                                                } else {
                                                    ?>
                                                    <div id="leave-comment" class="notloggedin">
                                                        <p style="text-align:center;">You must be logged in to post a comment.</p>

                                                        <p style="text-align:center;"><a href="<?php echo $dbf->basefilepath; ?>user/login">Login</a> or <a
                                                                href="<?php echo $dbf->basefilepath; ?>user/register/">Register</a></p>
                                                    </div>
                                                <?php
                                                }
                                            ?>


                                            <hr>
                                            <div id="comment-container">
                                                <?php
                                                    $comments = $dbf->getAllAssocResults( "SELECT c.CommentID, u.Username, u.RSN, u.UserID, c.PostDate, c.Content, c.Deleted, u.PrivelegeLevel, u.CommentBGColor
                                                                              FROM comments c
                                                                              JOIN users u
                                                                                ON u.UserID = c.UserID
                                                                              WHERE PostID = '" . $post[ 'PostID' ] . "'
                                                                              AND ReplyID IS NULL
                                                                              ORDER BY c.PostDate DESC" );

                                                    if ( count( $comments ) > 0 ) {
                                                        foreach ( $comments as $comment ) {
                                                            if ( $comment[ 'Deleted' ] == 0 ) {
                                                                $date            = strtotime( $comment[ 'PostDate' ] );
                                                                $commentid       = $comment[ 'CommentID' ];
                                                                $flags           = $dbf->getAllAssocResults( "SELECT * FROM commentflags WHERE CommentID='$commentid'" );
                                                                $useraccesslevel = $comment[ 'PrivelegeLevel' ];
                                                                ?>
                                                                <div class="comment" id="comment-<?php echo $comment[ 'CommentID' ]; ?>">
                                                                    <img class="user-portrait" src="http://services.runescape.com/m=avatar-rs/<?php echo urlencode($comment[ 'RSN' ]); ?>/chat.png">
                                                                    <div class="arrow-left" style="border-right-color:<?php echo "#" . $comment['CommentBGColor']; ?>"></div>
                                                                    <div class="comment-inner">
                                                                        <div class="comment-header" style="background-color:<?php echo "#" . $comment['CommentBGColor']; ?>">
                                                                            <a class="username"
                                                                               href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $comment[ 'Username' ]; ?>"><?php echo $comment[ 'Username' ]; ?></a> <a
                                                                                class="rsn"
                                                                                href="<?php echo $dbf->basefilepath; ?>calc/<?php echo $comment[ 'RSN' ]; ?>"><?php echo $comment[ 'RSN' ]; ?></a>

                                                                            <p class="post-date"><?php echo date( "M jS, Y", $date ) ?> (<?php echo time_elapsed_string( $date ); ?>)</p>

                                                                            <?php
                                                                                if ( $useraccesslevel == 4 ) {
                                                                                    ?>
                                                                                    <p class="flair">Admin</p>
                                                                                <?php
                                                                                } else {
                                                                                    if ( $useraccesslevel == 5 ) {
                                                                                        ?>
                                                                                        <p class="flair">
                                                                                            <img src="<?php echo $dbf->basefilepath; ?>images/TL_icon.png"/>
                                                                                            Dev
                                                                                        </p>
                                                                                    <?php
                                                                                    }
                                                                                }
                                                                            ?>
                                                                        </div>

                                                                        <?php
                                                                            if ( count( $flags ) < 3 ) {
                                                                                ?>
                                                                                <div class="comment-content">
                                                                                    <?php echo str_replace( "\\", "", $comment[ 'Content' ] ); ?>
                                                                                </div>
                                                                            <?php
                                                                            } else {
                                                                                ?>
                                                                                <div class="comment-content" data-comment="<?php echo str_replace( "\\", "", $comment[ 'Content' ] ); ?>">
                                                                                    <p class="flagged">This comment has been flagged as spam... <a href="javascript:void(0)"
                                                                                                                                                   onclick="showComment($(this));">(show the comment)</a></p>
                                                                                </div>
                                                                            <?php
                                                                            }

                                                                            if ( $loggedin ) {
                                                                                ?>
                                                                                <div class="comment-options <?php echo count( $flags ) >= 3 ? "withflag" : ""; ?>">
                                                                                    <a class="replybtn" href="javascript:void(0)" onclick="reply('<?php echo $comment[ "CommentID" ]; ?>');">Reply</a>
                                                                                    <?php
                                                                                        $flaggedByMe = false;
                                                                                        foreach ( $flags as $f ) {
                                                                                            if ( $f[ 'UserID' ] == $_SESSION[ 'userid' ] ) {
                                                                                                $flaggedByMe = true;
                                                                                            }
                                                                                        }

                                                                                        if ( $flaggedByMe ) {
                                                                                            ?>
                                                                                            <a class="flagbtn" style="color: green;" href="javascript:void(0)"><i class="icon-ok"></i>Flagged</a>
                                                                                        <?php
                                                                                        } else {
                                                                                            ?>
                                                                                            <a class="flagbtn" href="javascript:void(0)" onclick="flag('<?php echo $comment[ "CommentID" ]; ?>');">Flag</a>
                                                                                        <?php
                                                                                        }

                                                                                        if ( $comment[ 'UserID' ] ===
                                                                                            $_SESSION[ 'userid' ] ||
                                                                                            $accesslevel > 3 ) {
                                                                                            ?>
                                                                                            <a class="deletebtn" href="javascript:void(0)" onclick="deleteComment('<?php echo $comment[ 'CommentID' ]; ?>', 0);">Delete</a>
                                                                                            <a class="editbtn" href="javascript:void(0)" onclick="edit('<?php echo $comment[ 'CommentID' ]; ?>');">Edit</a>
                                                                                            <a class="cancelbtn" href="javascript:void(0)" onclick="cancelEdit('<?php echo $comment[ 'CommentID' ]; ?>');">Cancel</a>
                                                                                        <?php
                                                                                        }

                                                                                        if ( $accesslevel >= 4 ) {
                                                                                            ?>
                                                                                            <a class="harddeletebtn" href="javascript:void(0)"
                                                                                               onclick="deleteComment(<?php echo $comment[ 'CommentID' ]; ?>, 0, 1)">Hard Delete</a>
                                                                                        <?php
                                                                                        }
                                                                                    ?>
                                                                                </div>
                                                                            <?php
                                                                            }
                                                                        ?>
                                                                    </div>
                                                                </div>
                                                            <?php
                                                            } else {
                                                                ?>
                                                                <div class="comment deleted" id="comment-<?php echo $comment[ 'CommentID' ] ?>">
                                                                    <img class="user-portrait" src="http://services.runescape.com/m=avatar-rs/default_chat.png">
                                                                    <div class="arrow-left"></div>
                                                                    <div class="comment-inner">
                                                                        <div class="comment-header">
                                                                            <a class="username">[deleted]</a>
                                                                        </div>


                                                                        <div class="comment-content">
                                                                            <p class="flagged">This comment has been deleted.</p>
                                                                        </div>

                                                                        <?php
                                                                            if ( $accesslevel >= 4 ) {
                                                                                ?>
                                                                                <div class="comment-options">
                                                                                    <a class="harddeletebtn" href="javascript:void(0)" onclick="deleteComment(<?php echo $comment[ 'CommentID' ]; ?>, 0, 1)">Hard Delete</a>
                                                                                </div>
                                                                            <?php
                                                                            }
                                                                        ?>
                                                                    </div>
                                                                </div>
                                                            <?php
                                                            }

                                                            $replies = $dbf->getAllAssocResults( "SELECT c.CommentID, u.Username, u.UserID, u.RSN, c.PostDate, c.Content, c.Deleted, u.PrivelegeLevel, u.CommentBGColor
                                                                                FROM comments c
                                                                                JOIN users u
                                                                                  ON u.UserID = c.UserID
                                                                                WHERE PostID = '" . $post[ 'PostID' ] . "'
                                                                                AND ReplyID = '" . $comment[ 'CommentID' ] . "'" );

                                                            if ( count( $replies ) > 0 ) {
                                                                ?>
                                                                <div class="arrow-up"></div>
                                                                <div class="replies">
                                                                    <?php
                                                                        foreach ( $replies as $reply ) {
                                                                            if ( $reply[ 'Deleted' ] == 0 ) {
                                                                                $date            = strtotime( $reply[ 'PostDate' ] );
                                                                                $commentid       = $reply[ 'CommentID' ];
                                                                                $flags           = $dbf->getAllAssocResults( "SELECT * FROM commentflags WHERE CommentID='$commentid'" );
                                                                                $useraccesslevel = $reply[ 'PrivelegeLevel' ];
                                                                                ?>
                                                                                <div class="comment" id="comment-<?php echo $reply[ 'CommentID' ]; ?>">
                                                                                    <div class="comment-inner">
                                                                                        <div class="comment-header" style="background-color:<?php echo "#" . $reply['CommentBGColor']; ?>">
                                                                                            <img class="user-portrait-mini" src="http://services.runescape.com/m=avatar-rs/<?php echo urlencode($reply[ 'RSN' ]); ?>/chat.png">
                                                                                            <a class="username"
                                                                                               href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $reply[ 'Username' ]; ?>"><?php echo $reply[ 'Username' ]; ?></a>
                                                                                            <a
                                                                                                class="rsn" href="<?php echo $dbf->basefilepath; ?>calc/<?php echo $reply[ 'RSN' ]; ?>"><?php echo $reply[ 'RSN' ]; ?></a>

                                                                                            <p class="post-date"><?php echo date( "M jS, Y", $date ) ?> (<?php echo time_elapsed_string( $date ); ?>)</p>

                                                                                            <?php
                                                                                                if ( $useraccesslevel
                                                                                                    == 4 ) {
                                                                                                    ?>
                                                                                                    <p class="flair">Admin</p>
                                                                                                <?php
                                                                                                } else {
                                                                                                    if (
                                                                                                        $useraccesslevel == 5 ) {
                                                                                                        ?>
                                                                                                        <p class="flair">
                                                                                                            <img src="<?php echo $dbf->basefilepath; ?>images/TL_icon.png"/>
                                                                                                            Dev
                                                                                                        </p>
                                                                                                    <?php
                                                                                                    }
                                                                                                }
                                                                                            ?>
                                                                                        </div>

                                                                                        <?php
                                                                                            if ( count( $flags ) < 3 ) {
                                                                                                ?>
                                                                                                <div class="comment-content">
                                                                                                    <?php echo str_replace( "\\", "", $reply[ 'Content' ] ); ?>
                                                                                                </div>
                                                                                            <?php
                                                                                            } else {
                                                                                                ?>
                                                                                                <div class="comment-content" data-comment="<?php echo str_replace( "\\", "", $reply[ 'Content' ] ); ?>">
                                                                                                    <p class="flagged">This comment has been flagged as spam... <a href="javascript:void(0)"
                                                                                                                                                                   onclick="showComment($(this));">(show the comment)</a>
                                                                                                    </p>
                                                                                                </div>
                                                                                            <?php
                                                                                            }

                                                                                            if ( $loggedin ) {
                                                                                                ?>
                                                                                                <div class="comment-options <?php echo count( $flags ) >= 3 ? "withflag" : ""; ?>">
                                                                                                    <?php
                                                                                                        $flaggedByMe = false;
                                                                                                        foreach ( $flags as $f ) {
                                                                                                            if ( $f[ 'UserID' ] == $_SESSION[ 'userid' ] ) {
                                                                                                                $flaggedByMe = true;
                                                                                                            }
                                                                                                        }

                                                                                                        if ( $flaggedByMe ) {
                                                                                                            ?>
                                                                                                            <a class="flagbtn" style="color: green;" href="javascript:void(0)"><i class="icon-ok"></i>Flagged</a>
                                                                                                        <?php
                                                                                                        } else {
                                                                                                            ?>
                                                                                                            <a class="flagbtn" href="javascript:void(0)" onclick="flag('<?php echo $reply[ "CommentID" ]; ?>');">Flag</a>
                                                                                                        <?php
                                                                                                        }

                                                                                                        if ( $reply[
                                                                                                            'UserID' ] === $_SESSION[ 'userid' ] || $accesslevel >=4 ) {
                                                                                                            ?>
                                                                                                            <a class="deletebtn" href="javascript:void(0)"
                                                                                                               onclick="deleteComment('<?php echo $reply[ 'CommentID' ]; ?>', 1);">Delete</a>
                                                                                                            <a class="editbtn" href="javascript:void(0)" onclick="edit('<?php echo $reply[ 'CommentID' ]; ?>');">Edit</a>
                                                                                                            <a class="cancelbtn" href="javascript:void(0)"
                                                                                                               onclick="cancelEdit('<?php echo $reply[ 'CommentID' ]; ?>');">Cancel</a>
                                                                                                        <?php
                                                                                                        }

                                                                                                        if (
                                                                                                            $accesslevel >= 4) {
                                                                                                            ?>
                                                                                                            <a class="harddeletebtn" href="javascript:void(0)"
                                                                                                               onclick="deleteComment(<?php echo $reply[ 'CommentID' ]; ?>, 1, 1)">Hard Delete</a>
                                                                                                        <?php
                                                                                                        }
                                                                                                    ?>
                                                                                                </div>
                                                                                            <?php
                                                                                            }
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php
                                                                            } else {
                                                                                ?>
                                                                                <div class="comment deleted" id="comment-<?php echo $reply[ 'CommentID' ] ?>">
                                                                                    <div class="comment-inner">
                                                                                        <div class="comment-header">
                                                                                            <img class="user-portrait-mini" src="http://services.runescape.com/m=avatar-rs/default_chat.png">
                                                                                            <a class="username">[deleted]</a>
                                                                                        </div>


                                                                                        <div class="comment-content">
                                                                                            <p class="flagged">This comment has been deleted.</p>
                                                                                        </div>

                                                                                        <?php
                                                                                            if ( $accesslevel >=4 ) {
                                                                                                ?>
                                                                                                <div class="comment-options">
                                                                                                    <a class="harddeletebtn" href="javascript:void(0)"
                                                                                                       onclick="deleteComment(<?php echo $comment[ 'CommentID' ]; ?>, 0, 1)">Hard Delete</a>
                                                                                                </div>
                                                                                            <?php
                                                                                            }
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php
                                                                            }
                                                                        }
                                                                    ?>
                                                                </div>
                                                            <?php
                                                            }
                                                        }
                                                    } else {
                                                        ?>
                                                        <div class="no-comments">
                                                            <p>There are no comments yet.</p>
                                                        </div>
                                                    <?php
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                            }
                        ?>
                    </div>
                    <div class="sidebar">
                        <?php
                            $dbf->query("DELETE FROM searches WHERE Time < DATE_SUB(NOW(), INTERVAL 1 DAY)");
                            $totalNames = $dbf->queryToText( "SELECT count(*) FROM searches_new" ) + 3785; // 3785 from OLD DATABASE that got lost
                            $totalSearches = $dbf->queryToText( "SELECT SUM(TimesSearched) FROM searches_new" ) + 29477; //29477 from OLD DATABASE that got lost.
                            $totalUsers = $dbf->queryToText( "SELECT COUNT(*) FROM users" );
                            $totalSearches24Hrs = $dbf->queryToText( "SELECT COUNT(*) FROM searches" );
                            $totalCapes = $dbf->queryToText( "SELECT COUNT(*) FROM capes" );
                        ?>
                        <div class="stats">
                            <h2>Statistics</h2>

                            <p>Total Names</p>

                            <h3><?php echo number_format( $totalNames ); ?></h3>

                            <p>Total Searches</p>

                            <h3><?php echo number_format( $totalSearches ); ?></h3>

                            <p>Total Searches (last 24 hrs)</p>

                            <h3><?php echo number_format( $totalSearches24Hrs ); ?></h3>

                            <p>Total Users</p>

                            <h3 id="totalusers"><?php echo number_format( $totalUsers ); ?></h3>

                            <p>Total Capes Submitted</p>

                            <h3><?php echo number_format( $totalCapes ); ?></h3>
                        </div>

                        <h2>Recent Searches</h2>

                        <ul>
                            <?php

                                $recentsearches = $dbf->getAllAssocResults( "SELECT * FROM searches_new WHERE RSN NOT IN (SELECT RSN FROM users WHERE HideRSN = 1) ORDER BY LastSearchDate DESC LIMIT 10" );

                                for ( $i = 0; $i < 10; $i++ ) {
                                    ?>
                                    <li>
                                        <a href="<?php echo $dbf->basefilepath; ?>calc/<?php echo str_replace( " ", "+", $recentsearches[ $i ][ 'RSN' ] ); ?>"><?php echo $recentsearches[ $i ][ 'RSN' ]; ?></a>
                    <span
                        class="datetext"><?php echo time_elapsed_string( strtotime( $recentsearches[ $i ][ 'LastSearchDate' ] ) ); ?></span>
                                    </li>
                                <?php
                                }

                            ?>
                        </ul>
                    </div>

                    <div class="sidebar">
                        <h2>Recent Comments</h2>

                        <ul>
                            <?php
                                $recentcomments = $dbf->getAllAssocResults( "SELECT u.UserName, p.Headline, c.PostDate, c.PostID
                                                                    FROM comments c
                                                                    JOIN users u
                                                                        ON u.UserID = c.UserID
                                                                    JOIN posts p
                                                                        ON p.PostID = c.PostID
                                                                    WHERE c.Deleted = 0
                                                                    AND p.Visible = 1
                                                                    ORDER BY c.PostDate DESC
                                                                    LIMIT 10" );

                                foreach ( $recentcomments as $cmnt ) {
                                    ?>
                                    <li class="recent-comment">
                                    <span class="comment-top">
                                    <a href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $cmnt[ 'UserName' ]; ?>"><?php echo $cmnt[ 'UserName' ]; ?></a>
                                    <p>commenteds on</p>
                                        </span> <a class="comment-title" href="<?php echo $dbf->basefilepath; ?>post/<?php echo $cmnt[ 'PostID' ]; ?>"><?php echo $cmnt[ 'Headline' ]; ?></a> <span
                                            class="datetext"><?php echo time_elapsed_string( strtotime( $cmnt[ 'PostDate' ] ) ); ?></span>
                                    </li>
                                <?php
                                }
                            ?>
                        </ul>
                    </div>

                    <div class="sidebar">
                        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                            <input type="hidden" name="cmd" value="_s-xclick">
                            <input type="hidden" name="hosted_button_id"
                                   value="MLGB9DZPENU6Y">
                            <input type="image"
                                   src="<?php echo $dbf->basefilepath; ?>images/paypal.png"
                                   name="submit"
                                   alt="PayPal - The safer, easier way to pay online!">
                            <img alt="" border="0"
                                 src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif"
                                 width="1" height="1">
                        </form>
                    </div>
                </div>

                <?php
                    if ( !isset( $_GET[ 'viewpost' ] ) ) {
                        ?>

                        <div id="pagination">
                            <?php
                                for ( $i = 1; $i <= $pagecount; $i++ ) {
                                    if ( $i > 1 ) {
                                        ?>
                                        <div class="page <?php echo $i == $pagenumber ? "active-page blackbg" : "whitebg"; ?>">
                                            <a href="<?php echo $dbf->basefilepath; ?>page/<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </div>
                                    <?php
                                    } else {
                                        ?>
                                        <div class="page <?php echo $i == $pagenumber ? "active-page blackbg" : "whitebg"; ?>">
                                            <a href="<?php echo $dbf->basefilepath; ?>nr"><?php echo $i; ?></a>
                                        </div>
                                    <?php
                                    }
                                }
                            ?>
                        </div>

                        <script>
                            $(".page").mouseover(function () {
                                $(this).removeClass("whitebg").addClass("blackbg");
                            }).mouseleave(function () {
                                    if (!$(this).hasClass("active-page")) {
                                        $(this).removeClass("blackbg").addClass("whitebg");
                                    }
                                });
                        </script>
                    <?php
                    }
                ?>

                <div id="footer">
                    <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
                </div>

                <?php
                    $time = microtime();
                    $time = explode(' ', $time);
                    $time = $time[1] + $time[0];
                    $finish = $time;
                    $total_time = round(($finish - $start), 4);

                    $dbf->query("INSERT INTO loadtimes (Time, PageID) VALUES ('$total_time', '0')");
                ?>
            </body>
        </html>

        <?php
        if ( $_GET[ 'beta' ] == 1 ) {
            ?>
            <!--div id="screenwidth">

            </div-->
            <script>
                $(document).ready(function () {
                    //$("#screenwidth").text("Screen Width: " + ($(window).width() + 17) + "px");
                    window.resizeTo(1024,768);
                    $("a").each(function () {
                        var href = $(this).prop("href");
                        if (href.indexOf("<?php echo $dbf->basefilepath; ?>") != -1) {
                            if (href.indexOf("?") != -1) {
                                //Link contains URL query
                                href += "&beta=1";
                            } else {
                                href += "?beta=1";
                            }

                            //console.log(href);
                            $(this).prop("href", href);
                        }
                    });

                    //$(window).resize(function() {
                     //   $("#screenwidth").text("Screen Width: " + ($(window).width() + 17) + "px");
                   //});
                });
            </script>
        <?php
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        echo "Cannot establish database connection";
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }