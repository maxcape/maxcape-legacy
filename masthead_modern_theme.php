<?php
    function str_replace_first( $search, $replace, $subject ) {
        $pos = strpos( $subject, $search );
        if ( $pos !== false ) {
            $subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
        }
        return $subject;
    }

    $file = str_replace_first( $dbf->basefilepath, "", $_SERVER[ 'PHP_SELF' ] );
    $filepath = dirname( $file );

    $userid = isset( $_SESSION[ 'userid' ] ) ? $_SESSION[ 'userid' ] : NULL;

    if ( $userid != NULL ) {
        $accesslevel = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid'" );
    } else {
        $accesslevel = 0;
    }
?>

<?php
    if ( !strstr( $filepath, "admin" ) ) {
        ?>
        <script>
            $(document).ready(function () {
                $('.twitter, .facebook, .googleplus, .tumblr').click(function (event) {
                    var width = 575,
                        height = 400,
                        left = ($(window).width() - width) / 2,
                        top = ($(window).height() - height) / 2,
                        url = this.href,
                        opts = 'status=1' +
                            ',width=' + width +
                            ',height=' + height +
                            ',top=' + top +
                            ',left=' + left;

                    window.open(url, 'twitter', opts);

                    return false;
                });
            });
        </script>
    <?php
    }
?>

<?php require_once( "analytics.php" ); ?>

    <div id="masthead">
        <div class="header">

            <?php
                if ( $filepath == "profile" ) {
                    ?>
                    <img class="icon" src="http://services.runescape.com/m=avatar-rs/<?php echo $userdata[ 'RSN' ]; ?>/chat.png">

                    <a style="float:left;" href="<?php echo $dbf->basefilepath; ?>nr"><h1>MAX/COMP CAPE PROFILES</h1></a>
                <?php
                } else {
                    if ( $_GET[ 'beta' ] != 1 ) {
                        ?>
                        <img src="<?php echo $dbf->basefilepath; ?>images/TL_icon.png" class="icon">
                        <a style="float:left;" href="<?php echo $dbf->basefilepath; ?>nr"><h1>MAX/COMP CAPE CALCULATOR</h1></a>
                    <?php
                    } else {
                        ?>
                        <div class="logo-1">
                            <div class="bar"></div>
                        </div>
                        <div class="logo-2">
                            <div class="bar"></div>
                        </div>
                        <div class="logo-3">
                            <div class="bar"></div>
                        </div>
                        <a style="float:left;" href="<?php echo $dbf->basefilepath; ?>nr"><h1>Maxcape</h1></a>
                    <?php
                    }
                }
            ?>
        </div>
        <div class="nav">
            <ul>
                <li <?php echo $filepath == "." || $filepath == "\\" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>nr"><span class="icon-home"></span></a></li>
                <li <?php echo $filepath == "designer" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>designer/">Designer</a></li>
                <li <?php echo $filepath == "sig" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>sig/">Signatures</a></li>
                <li <?php echo $filepath == "milestones" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>milestones/">Milestones</a></li>
                <?php
                    if ( $loggedin ) {
                        ?>
                        <li <?php echo $filepath == "profile" && $username == $myusername ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $myusername; ?>">My Profile</a></li>
                    <?php
                    } else {
                        ?>
                        <li <?php echo $filepath == "login" && $action == "login" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>user/login">Login</a></li>
                        <li <?php echo $filepath == "login" && $action == "register" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>user/register">Register</a></li>
                    <?php
                    }
                ?>
                <li <?php echo $filepath == "search" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>search/">Search Profiles</a></li>
                <?php
                    if ( $loggedin ) {
                        ?>
                        <li <?php echo $filepath == "ucp" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/0">User Control Panel</a></li>
                    <?php
                    }

                    if ( $accesslevel > 0 ) {
                        ?>
                        <li <?php echo strstr( $filepath, "admin" ) ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>admin/">Admin</a></li>
                    <?php
                    }
                ?>
            </ul>
        </div>

        <?php
            if ( $filepath == "calc" ) {
                ?>
                <form action="/calc/" method="get">
                    <button type="submit" onclick="(function(e) { e.preventDefault(); document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#name').val().replace(' ', '+') + '?beta=1'; })(event)"><i
                            class="icon-search"></i></button>
                    <input type="text" name="name" id="name" placeholder="Search..." required="required">
                    <?php
                        if ( isset( $_COOKIE[ 'maxcompcapename' ] ) && $_COOKIE[ 'maxcompcapename' ] == $username ) {
                            ?>
                            <button type="button" id="defnameBtn" class="defnameset" onclick="defname('<?php echo $username; ?>');"><i class="icon-ok"></i></button>
                        <?php
                        } else {
                            ?>
                            <button type="button" id="defnameBtn" class="defnamenotset" onclick="defname('<?php echo $username; ?>');"><i class="icon-remove"></i></button>
                        <?php
                        }
                    ?>
                </form>
            <?php
            } else {
                ?>
                <form style="float:right;" action="<?php echo $dbf->basefilepath; ?>calc/" method="get">
                    <button type="submit" onclick="(function(e) { e.preventDefault(); document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#name').val().replace(' ', '+') + '?beta=1'; })(event)"><i
                            class="icon-search"></i></button>
                    <input type="text" name="name" id="name" placeholder="Search..." required="required">
                </form>
            <?php
            }
        ?>

    </div>


    <div class="latestNews">
        <?php
            $latest = $dbf->queryToAssoc( "SELECT * FROM posts WHERE Visible=1 ORDER BY Date DESC LIMIT 1" );
        ?>
        <h3>Latest News: <a href="<?php echo $dbf->basefilepath; ?>post/<?php echo $latest[ 'PostID' ]; ?>"><?php echo $latest[ 'Headline' ]; ?></a></h3>

        <?php
            if ( $loggedin ) {
                ?>
                <div id="user-options">
                    <p>Welcome, <?php echo $myusername; ?> | <a href="<?php echo $dbf->basefilepath; ?>user/logout">Logout</a></p>
                </div>
            <?php
            }

            if($filepath == "calc") {
                ?>
                <div class="social">
                    <a href="http://twitter.com/share?url=<?php echo urlencode( $dbf->getUrl() ); ?>&via=The__Orange&hashtags=RuneScape&text=<?php echo urlencode( "I'm " . number_format( ( ( $maxcapexp - $xpToMaxCape ) / $maxcapexp ) * 100, 2 ) . "% of the way to Max Cape, and " . number_format( ( ( $compcapexp - $xpToCompCape ) / $compcapexp ) * 100, 2 ) . "% of the way to Comp Cape!" ) ?>"
                       class="s3d twitter"> <span class="icon-twitter"></span> </a>

                    <a href="http://www.tumblr.com/share/link?url=<?php echo urlencode( $dbf->getUrl() ); ?>&name=<?php echo urlencode( "$username - Max/Comp Cape Calculator" ); ?>&description=<?php echo urlencode( "I'm " . number_format( ( ( $maxcapexp - $xpToMaxCape ) / $maxcapexp ) * 100, 2 ) . "% of the way to Max Cape, and " . number_format( ( ( $compcapexp - $xpToCompCape ) / $compcapexp ) * 100, 2 ) . "% of the way to Comp Cape!" ) ?>"
                       class="s3d tumblr"> <span class="icon-tumblr"></span> </a>

                    <a href="http://www.facebook.com/sharer.php?s=100&p[title]=<?php echo urlencode( "Check out my RuneScape Max/Comp Cape progress!" ); ?>&p[summary]=<?php echo urlencode( "I'm " . number_format( ( ( $maxcapexp - $xpToMaxCape ) / $maxcapexp ) * 100, 2 ) . "% of the way to Max Cape, and " . number_format( ( ( $compcapexp - $xpToCompCape ) / $compcapexp ) * 100, 2 ) . "% of the way to Comp Cape!" ) ?>&p[url]=<?php echo urlencode( $dbf->getUrl() ); ?>&p[images][0]="
                       class="s3d facebook"> <span class="icon-facebook"></span> </a>

                    <a href="https://plus.google.com/share?url=<?php echo urlencode( $dbf->getUrl() ); ?>" class="s3d googleplus"> <span class="icon-google-plus"></span> </a>
                </div>
                <?php
            }
        ?>


    </div>

    <script src="<?php echo $dbf->basefilepath; ?>js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="<?php echo $dbf->basefilepath; ?>js/timer.js"></script>
    <div id="timer" class="minimized blackbg">
        <div id="timer-header">
            <span id="running-icon" class="icon-time"></span>

            <span>Global Timer</span>

            <i class="icon-double-angle-up"></i>
        </div>

        <div id="timer-container">
            <span id="minutes">00</span><span id="seperator">:</span><span id="seconds">00</span>
        </div>

        <div id="timer-tools">
            <label for="minutes-set">Min:</label>
            <input type="text" id="minutes-set" value="0">
            <button type="button" onclick="startTimer(); toggleTimer(true);" id="startbtn">Start</button>
            <label for="seconds-set">Sec:</label>
            <input type="text" id="seconds-set" value="0">
            <button type="button" onclick="stopTimer();">Stop</button>
        </div>
    </div>

    <div id="sounddummy"></div>

    <script>
        var running = localStorage.getItem("running");

        if (localStorage.getItem("timerState") == "up") {
            toggleTimer(false);
        }

        if (running == "1") {
            startTimer();
        }

        $("#timer-header").click(function () {
            toggleTimer(true);
        });
    </script>

<?php
    $donator = $dbf->queryToText( "SELECT Donator FROM users WHERE UserID='$userid'" );

    if ( $donator == 1 ) {
        ?>
        <style>
            .ad {
                display: none !important;
            }
        </style>
    <?php
    }
?>