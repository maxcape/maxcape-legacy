<?php
    session_start();

    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];
    $userid = $_SESSION[ 'userid' ];

    $favorites = array();

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database." );

    if ( isset( $_GET[ 'id' ] ) ) {
        $quickview = mysql_real_escape_string( $_GET[ 'id' ] );
    } else {
        $quickview = "";
    }
?>

    <!DOCTYPE html>
    <html>
        <head>
            <title>Designer</title>

            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/designer.css">

            <link rel="stylesheet" href="css/jquery.mCustomScrollbar.css">
            <link rel="stylesheet" href="css/jquery.smallipop.css" type="text/css" media="all" title="Screen"/>

            <script type="text/javascript" src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
            <script type="text/javascript" src="js/jquery.mCustomScrollbar.concat.min.js"></script>
            <script type="text/javascript" src="js/jquery.smallipop.js"></script>

            <script type="text/javascript" src="js/init.js"></script>

            <?php
            if ( $loggedin ) {
                ?>
                <script type="text/javascript" src="js/userinit.js"></script>
            <?php
            } else {
                ?>
                <script type="text/javascript" src="js/nonuserinit.js"></script>
            <?php
            }
            ?>
        </head>

        <body>
            <?php require_once( "../masthead.php" ); ?>

            <div id="content">
                <div id="maincontent" style="width:72%;">
                    <div class="innercontent" style="position:relative;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/max/base.png" id="mbase" style="display:none;"/>
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/max/color1.png" id="mc1" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/max/color2.png" id="mc2" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/max/color3.png" id="mc3" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/max/color4.png" id="mc4" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/max/trim.png" id="mtrim" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/comp/base.png" id="cbase" style="display:none;"/>
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/comp/color1.png" id="cc1" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/comp/color2.png" id="cc2" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/comp/color3.png" id="cc3" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/comp/color4.png" id="cc4" style="display:none;">
                        <img src="<?php echo $dbf->basefilepath; ?>images/capes/comp/trim.png" id="ctrim" style="display:none;">

                        <div id="canvascontainer-max">
                            <canvas id="mbasecanvas" width="238" height="425"></canvas>
                            <canvas id="mccanvas1" width="238" height="425"></canvas>
                            <canvas id="mccanvas2" width="238" height="425"></canvas>
                            <canvas id="mccanvas3" width="238" height="425"></canvas>
                            <canvas id="mccanvas4" width="238" height="425"></canvas>
                            <canvas id="mtrimcanvas" width="238" height="425"></canvas>

                            <button type="button" onclick="outputImage('m');">Generate Image</button>
                        </div>

                        <div id="output">
                            <div id="minicolors">
                                <?php
                                if ( $quickview == "" ) {
                                    ?>
                                    <div class="minicolor" id="minicolor1" style="background-color:hsl(356, 65%, 56%);" data-h="356" data-s="65" data-l="56"></div>
                                    <div class="minicolor" id="minicolor2" style="background-color:hsl(356, 52%, 47%);" data-h="356" data-s="52" data-l="47"></div>
                                    <div class="minicolor" id="minicolor3" style="background-color:hsl(356, 52%, 37%);" data-h="356" data-s="52" data-l="37"></div>
                                    <div class="minicolor" id="minicolor4" style="background-color:hsl(344, 38%, 25%);" data-h="344" data-s="38" data-l="25"></div>
                                <?php
                                } else {
                                    $colors = $dbf->getAllAssocResults("SELECT * FROM colors WHERE ColorID IN ( SELECT ColorID FROM capecolors WHERE CapeID = '$quickview')");

                                    foreach($colors as $i => $color) {
                                        ?>
                                        <div class="minicolor" id="minicolor<?php echo $i+1; ?>" style="background-color:hsl(<?php echo $color['H']; ?>, <?php echo $color['S']; ?>%, <?php echo $color['L']; ?>%);" data-h="<?php echo $color['H']; ?>" data-s="<?php echo $color['S']; ?>" data-l="<?php echo $color['L']; ?>"></div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>

                            <div id="colors">
                                <?php
                                if ( $quickview == "" ) {
                                    ?>
                                    <div class="color" id="color1" style="background-color:hsl(356, 65%, 56%);" data-h="356" data-s="65" data-l="56"></div>
                                    <div class="color" id="color2" style="background-color:hsl(356, 52%, 47%);" data-h="356" data-s="52" data-l="47"></div>
                                    <div class="color" id="color3" style="background-color:hsl(356, 52%, 37%);" data-h="356" data-s="52" data-l="37"></div>
                                    <div class="color" id="color4" style="background-color:hsl(344, 38%, 25%);" data-h="344" data-s="38" data-l="25"></div>
                                <?php
                                } else {
                                    $colors = $dbf->getAllAssocResults("SELECT * FROM colors WHERE ColorID IN ( SELECT ColorID FROM capecolors WHERE CapeID = '$quickview')");

                                    foreach($colors as $i => $color) {
                                        ?>
                                        <div class="color" id="color<?php echo $i+1; ?>" style="background-color:hsl(<?php echo $color['H']; ?>, <?php echo $color['S']; ?>%, <?php echo $color['L']; ?>%);" data-h="<?php echo $color['H']; ?>" data-s="<?php echo $color['S']; ?>" data-l="<?php echo $color['L']; ?>"></div>
                                    <?php
                                    }
                                }
                                ?>

                            </div>


                            <div id="coloroutput">
                                <label for="rshue">H:</label>
                                <input type="text" disabled="disabled" id="rshue">

                                <label for="rssat">S:</label>
                                <input type="text" disabled="disabled" id="rssat">

                                <label for="rslit">L:</label>
                                <input type="text" disabled="disabled" id="rslit">
                            </div>

                            <div id="cp">
                                <canvas id="colorpicker" width="150" height="150"></canvas>
                                <canvas id="huepicker" width="25" height="150"></canvas>
                            </div>

                            <?php
                            if ( $loggedin ) {
                                ?>
                                <div id="savebutton">
                                    <button id="save">Save Cape</button>
                                </div>

                            <?php
                            }
                            ?>
                        </div>

                        <div id="canvascontainer-comp">
                            <canvas id="cbasecanvas" width="221" height="425"></canvas>
                            <canvas id="cccanvas1" width="221" height="425"></canvas>
                            <canvas id="cccanvas2" width="221" height="425"></canvas>
                            <canvas id="cccanvas3" width="221" height="425"></canvas>
                            <canvas id="cccanvas4" width="221" height="425"></canvas>
                            <canvas id="ctrimcanvas" width="221" height="425"></canvas>

                            <button type="button" onclick="outputImage('c');">Generate Image</button>
                        </div>
                    </div>
                </div>

                <div class="sidebarheader">
                    <h2>My Favorites</h2>
                </div>
                <div class="sidebar" id="favorites" style="width:27%; text-align:left; height:417px;">
                    <?php
                    if ( $loggedin ) {
                        ?>
                        <div class="usercapes">
                            <?php
                            $capelist = $dbf->getAllAssocResults( "SELECT c.UserID, c.CapeID, c.Title, c.SubmitDate, cf.Date AS FavoriteDate FROM capefavorites cf JOIN capes c ON c.CapeID = cf.CapeID WHERE cf.UserID='$userid' ORDER BY Date DESC" );

                            foreach ( $capelist as $cape ) {
                                $submitterid = $cape[ 'UserID' ];
                                $submitter   = $dbf->queryToText( "SELECT Username FROM users WHERE UserID='$submitterid' LIMIT 1" );

                                $id           = $cape[ 'CapeID' ];
                                $favorites[ ] = $id;

                                $colors = $dbf->getAllAssocResults( "SELECT H, S, L FROM capecolors cc JOIN colors c ON c.ColorID = cc.ColorID WHERE CapeID = '$id'" );

                                $title         = $cape[ 'Title' ];
                                $maxstrlen     = 12;
                                $scalingfactor = 6;

                                if ( strlen( $title ) > $maxstrlen ) {
                                    $remaining = ( strlen( $title ) - $maxstrlen );
                                    $fsize     = 100 - ( $scalingfactor * $remaining );
                                } else {
                                    $fsize = 100;
                                }

                                ?>
                                <div class="preview" id="cape-preview-<?php echo $id; ?>-favorite">
                                    <div class="applycapestyle">
                                        <span class="capetitle" style="font-size:<?php echo $fsize; ?>%"><?php echo $cape[ 'Title' ]; ?></span>

                                        <div class="microcolors">
                                            <?php
                                            foreach ( $colors as $i => $color ) {
                                                ?>
                                                <div class="microcolor" style="background-color:hsl(<?php echo $color[ 'H' ]; ?>, <?php echo $color[ 'S' ]; ?>%, <?php echo $color[ 'L' ]; ?>%)"
                                                     data-h="<?php echo $color[ 'H' ]; ?>" data-s="<?php echo $color[ 'S' ]; ?>" data-l="<?php echo $color[ 'L' ]; ?>"></div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <?php
                                    $votetotal = $dbf->queryToText( "SELECT SUM(Direction) FROM capevotes WHERE CapeID='$id'" );
                                    $votetotal = intval( $votetotal, 10 );

                                    if ( $votetotal == "" || $votetotal == 0 ) {
                                        $votetotal = 0;
                                        $color     = "reszero";
                                        $sign      = "&nbsp;";
                                    } elseif ( $votetotal > 0 ) {
                                        $color = "respos";
                                        $sign  = "+";
                                    } else {
                                        $color = "resneg";
                                        $sign  = "-";
                                    }
                                    ?>
                                    <span class="result <?php echo $color; ?>"><span class="result-sign"><?php echo $sign; ?></span><span class="result-number"><?php echo abs( $votetotal ); ?></span></span>

                                    <div class="smallipop-hint">
                                        <h2 style="font-size:<?php echo round( 25 * ( $fsize / 100 ) ); ?>px"><?php echo $cape[ 'Title' ]; ?></h2>

                                        <p>Submitted by: <a target="_blank" href="<?php echo $dbf->basefilepath . "profile/$submitter"; ?>"><?php echo $submitter; ?></a></p>

                                        <div class="capevoteresults">
                                            <?php
                                            $upvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=1 AND CapeID='$id'" );
                                            $downvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=-1 AND CapeID='$id'" );
                                            ?>
                                            <p class="votecount respos floatleft">Upvotes: <span class="upvotecount"><?php echo $upvotes; ?></span></p>

                                            <p class="votecount resneg floatright">Downvotes: <span class="downvotecount"><?php echo $downvotes; ?></span></p>
                                        </div>

                                        <div class="controls">
                                            <p class="floatleft"><a href="javascript:void(0);" onclick="flag(<?php echo $id; ?>);"><span class="icon-flag"></span> Flag</a></p>

                                            <p class="floatright"><a class="favoritebutton favorited" href="javascript:void(0);" onclick="unfavorite(<?php echo $id; ?>);"><span class="icon-star"></span> Unfavorite</a>
                                            </p>
                                        </div>

                                        <p><a href="http://www.maxcape.com/designer/<? echo $id; ?>">Link to Cape</a></p>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    <?php
                    } else {
                        ?>
                        <div id="loginmessage">
                            <p>You must be logged in to view and set favorites.</p>

                            <p><a href="../user/login">Login</a> or <a href="../user/register/">Register</a></p>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>

            <div id="view-containers">
                <div id="preview-headers">
                    <h2>Most Recent</h2>

                    <h2>Top 100 (Overall)</h2>

                    <h2>Top 100 (This Month)</h2>
                </div>

                <div class="preview-container" id="most-recent">
                    <div class="usercapes">
                        <?php
                        $capelist = $dbf->getAllAssocResults( "SELECT * FROM capes ORDER BY SubmitDate DESC LIMIT 100" );

                        foreach ( $capelist as $cape ) {
                            $submitterid = $cape[ 'UserID' ];
                            $submitter   = $dbf->queryToText( "SELECT Username FROM users WHERE UserID='$submitterid' LIMIT 1" );

                            $id = $cape[ 'CapeID' ];

                            if ( in_array( $id, $favorites ) ) {
                                $favorited = true;
                            } else {
                                $favorited = false;
                            }

                            $colors = $dbf->getAllAssocResults( "SELECT H, S, L FROM capecolors cc JOIN colors c ON c.ColorID = cc.ColorID WHERE CapeID = '$id'" );

                            $title         = $cape[ 'Title' ];
                            $maxstrlen     = 12;
                            $scalingfactor = 6;

                            if ( strlen( $title ) > $maxstrlen ) {
                                $remaining = ( strlen( $title ) - $maxstrlen );
                                $fsize     = 100 - ( $scalingfactor * $remaining );
                            } else {
                                $fsize = 100;
                            }

                            ?>
                            <div class="preview" id="cape-preview-<?php echo $id; ?>">
                                <div class="applycapestyle">
                                    <span class="capetitle" style="font-size:<?php echo $fsize; ?>%"><?php echo $cape[ 'Title' ]; ?></span>

                                    <div class="microcolors">
                                        <?php
                                        foreach ( $colors as $i => $color ) {
                                            ?>
                                            <div class="microcolor" style="background-color:hsl(<?php echo $color[ 'H' ]; ?>, <?php echo $color[ 'S' ]; ?>%, <?php echo $color[ 'L' ]; ?>%)"
                                                 data-h="<?php echo $color[ 'H' ]; ?>" data-s="<?php echo $color[ 'S' ]; ?>" data-l="<?php echo $color[ 'L' ]; ?>"></div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>

                                <?php
                                if ( $loggedin ) {
                                    $choice = $dbf->queryToText( "SELECT Direction FROM capevotes WHERE UserID='$userid' AND CapeID='$id'" );
                                } else {
                                    $choice = 0;
                                }

                                if ( $choice == "" ) {
                                    $choice = 0;
                                }
                                ?>

                                <div class="vote" data-voted="<?php echo $choice == 0 ? "false" : "true"; ?>">
                                    <span class="icon-thumbs-up upvote <?php echo $choice == 1 ? "votedchoice" : ""; ?>" onclick="upvote(<?php echo $id; ?>, $(this));" id="upvote-<?php echo $id; ?>"></span> <span
                                        class="icon-thumbs-down downvote  <?php echo $choice == -1 ? "votedchoice" : ""; ?>" onclick="downvote(<?php echo $id; ?>, $(this));" id="downvote-<?php echo $id; ?>"></span>
                                </div>

                                <?php
                                $votetotal = $dbf->queryToText( "SELECT SUM(Direction) FROM capevotes WHERE CapeID='$id'" );
                                $votetotal = intval( $votetotal, 10 );

                                if ( $votetotal == "" || $votetotal == 0 ) {
                                    $votetotal = 0;
                                    $color     = "reszero";
                                    $sign      = "&nbsp;";
                                } elseif ( $votetotal > 0 ) {
                                    $color = "respos";
                                    $sign  = "+";
                                } else {
                                    $color = "resneg";
                                    $sign  = "-";
                                }
                                ?>
                                <span class="result <?php echo $color; ?>"><span class="result-sign"><?php echo $sign; ?></span><span class="result-number"><?php echo abs( $votetotal ); ?></span></span>

                                <div class="smallipop-hint">
                                    <h2 style="font-size:<?php echo round( 25 * ( $fsize / 100 ) ); ?>px"><?php echo $cape[ 'Title' ]; ?></h2>

                                    <p>Submitted by: <a target="_blank" href="<?php echo $dbf->basefilepath . "profile/$submitter"; ?>"> <?php echo $submitter; ?></a></p>

                                    <div class="capevoteresults">
                                        <?php
                                        $upvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=1 AND CapeID='$id'" );
                                        $downvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=-1 AND CapeID='$id'" );
                                        ?>
                                        <p class="votecount respos floatleft">Upvotes: <span class="upvotecount"><?php echo $upvotes; ?></span></p>

                                        <p class="votecount resneg floatright">Downvotes: <span class="downvotecount"><?php echo $downvotes; ?></span></p>
                                    </div>

                                    <div class="controls">
                                        <p class="floatleft"><a href="javascript:void(0);" onclick="flag(<?php echo $id; ?>);"><span class="icon-flag"></span> Flag</a></p>

                                        <?php
                                        if ( $favorited ) {
                                            ?>
                                            <p class="floatright"><a class="favoritebutton favorited" href="javascript:void(0);" onclick="unfavorite(<?php echo $id; ?>);"><span class="icon-star"></span> Unfavorite</a>
                                            </p>
                                        <?php
                                        } else {
                                            ?>
                                            <p class="floatright"><a class="favoritebutton" href="javascript:void(0);" onclick="favorite(<?php echo $id; ?>);"><span class="icon-star"></span> Favorite</a></p>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <p><a href="http://www.maxcape.com/designer/<? echo $id; ?>">Link to Cape</a></p>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="preview-container" id="most-popular-alltime">
                    <div class="usercapes">
                        <?php
                        $capelist = $dbf->getAllAssocResults( "SELECT c.CapeID, c.UserID, c.Title, c.SubmitDate, SUM(cv.Direction) AS VoteTotal FROM capes c JOIN capevotes cv ON c.CapeID = cv.CapeID GROUP BY c.CapeID ORDER BY VoteTotal DESC, Title ASC LIMIT 100" );

                        foreach ( $capelist as $cape ) {
                            $submitterid = $cape[ 'UserID' ];
                            $submitter   = $dbf->queryToText( "SELECT Username FROM users WHERE UserID='$submitterid' LIMIT 1" );

                            $id = $cape[ 'CapeID' ];

                            if ( in_array( $id, $favorites ) ) {
                                $favorited = true;
                            } else {
                                $favorited = false;
                            }

                            $colors = $dbf->getAllAssocResults( "SELECT H, S, L FROM capecolors cc JOIN colors c ON c.ColorID = cc.ColorID WHERE CapeID = '$id'" );

                            $title         = $cape[ 'Title' ];
                            $maxstrlen     = 12;
                            $scalingfactor = 6;

                            if ( strlen( $title ) > $maxstrlen ) {
                                $remaining = ( strlen( $title ) - $maxstrlen );
                                $fsize     = 100 - ( $scalingfactor * $remaining );
                            } else {
                                $fsize = 100;
                            }

                            ?>
                            <div class="preview" id="cape-preview-alltime-<?php echo $id; ?>">
                                <div class="applycapestyle">
                                    <span class="capetitle" style="font-size:<?php echo $fsize; ?>%"><?php echo $cape[ 'Title' ]; ?></span>

                                    <div class="microcolors">
                                        <?php
                                        foreach ( $colors as $i => $color ) {
                                            ?>
                                            <div class="microcolor" style="background-color:hsl(<?php echo $color[ 'H' ]; ?>, <?php echo $color[ 'S' ]; ?>%, <?php echo $color[ 'L' ]; ?>%)"
                                                 data-h="<?php echo $color[ 'H' ]; ?>" data-s="<?php echo $color[ 'S' ]; ?>" data-l="<?php echo $color[ 'L' ]; ?>"></div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>

                                <?php
                                if ( $loggedin ) {
                                    $choice = $dbf->queryToText( "SELECT Direction FROM capevotes WHERE UserID='$userid' AND CapeID='$id'" );
                                } else {
                                    $choice = 0;
                                }

                                if ( $choice == "" ) {
                                    $choice = 0;
                                }
                                ?>

                                <div class="vote" data-voted="<?php echo $choice == 0 ? "false" : "true"; ?>">
                                    <span class="icon-thumbs-up upvote <?php echo $choice == 1 ? "votedchoice" : ""; ?>" onclick="upvote(<?php echo $id; ?>, $(this));" id="upvote-alltime-<?php echo $id; ?>"></span> <span
                                        class="icon-thumbs-down downvote  <?php echo $choice == -1 ? "votedchoice" : ""; ?>" onclick="downvote(<?php echo $id; ?>, $(this));"
                                        id="downvote-alltime-<?php echo $id; ?>"></span>
                                </div>

                                <?php
                                $votetotal = $dbf->queryToText( "SELECT SUM(Direction) FROM capevotes WHERE CapeID='$id'" );
                                $votetotal = intval( $votetotal, 10 );

                                if ( $votetotal == "" || $votetotal == 0 ) {
                                    $votetotal = 0;
                                    $color     = "reszero";
                                    $sign      = "&nbsp;";
                                } elseif ( $votetotal > 0 ) {
                                    $color = "respos";
                                    $sign  = "+";
                                } else {
                                    $color = "resneg";
                                    $sign  = "-";
                                }
                                ?>
                                <span class="result <?php echo $color; ?>"><span class="result-sign"><?php echo $sign; ?></span><span class="result-number"><?php echo abs( $votetotal ); ?></span></span>

                                <div class="smallipop-hint">
                                    <h2 style="font-size:<?php echo round( 25 * ( $fsize / 100 ) ); ?>px"><?php echo $cape[ 'Title' ]; ?></h2>

                                    <p>Submitted by: <a target="_blank" href="<?php echo $dbf->basefilepath . "profile/$submitter"; ?>"> <?php echo $submitter; ?></a></p>

                                    <div class="capevoteresults">
                                        <?php
                                        $upvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=1 AND CapeID='$id'" );
                                        $downvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=-1 AND CapeID='$id'" );
                                        ?>
                                        <p class="votecount respos floatleft">Upvotes: <span class="upvotecount"><?php echo $upvotes; ?></span></p>

                                        <p class="votecount resneg floatright">Downvotes: <span class="downvotecount"><?php echo $downvotes; ?></span></p>
                                    </div>

                                    <div class="controls">
                                        <p class="floatleft"><a href="javascript:void(0);" onclick="flag(<?php echo $id; ?>);"><span class="icon-flag"></span> Flag</a></p>

                                        <?php
                                        if ( $favorited ) {
                                            ?>
                                            <p class="floatright"><a class="favoritebutton favorited" href="javascript:void(0);" onclick="unfavorite(<?php echo $id; ?>);"><span class="icon-star"></span> Unfavorite</a>
                                            </p>
                                        <?php
                                        } else {
                                            ?>
                                            <p class="floatright"><a class="favoritebutton" href="javascript:void(0);" onclick="favorite(<?php echo $id; ?>);"><span class="icon-star"></span> Favorite</a></p>
                                        <?php
                                        }
                                        ?>

                                    </div>

                                    <p><a href="http://www.maxcape.com/designer/<? echo $id; ?>">Link to Cape</a></p>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="preview-container" id="most-popular-month">
                    <div class="usercapes">
                        <?php
                        $capelist = $dbf->getAllAssocResults( "SELECT c.CapeID, c.UserID, c.Title, c.SubmitDate, SUM(cv.Direction) AS VoteTotal FROM capes c JOIN capevotes cv ON c.CapeID = cv.CapeID WHERE c.SubmitDate > DATE_SUB(NOW(), INTERVAL 1 MONTH) GROUP BY c.CapeID ORDER BY VoteTotal DESC, Title ASC LIMIT 100" );

                        foreach ( $capelist as $cape ) {
                            $submitterid = $cape[ 'UserID' ];
                            $submitter   = $dbf->queryToText( "SELECT Username FROM users WHERE UserID='$submitterid' LIMIT 1" );

                            $id = $cape[ 'CapeID' ];

                            if ( in_array( $id, $favorites ) ) {
                                $favorited = true;
                            } else {
                                $favorited = false;
                            }

                            $colors = $dbf->getAllAssocResults( "SELECT H, S, L FROM capecolors cc JOIN colors c ON c.ColorID = cc.ColorID WHERE CapeID = '$id'" );

                            $title         = $cape[ 'Title' ];
                            $maxstrlen     = 12;
                            $scalingfactor = 6;

                            if ( strlen( $title ) > $maxstrlen ) {
                                $remaining = ( strlen( $title ) - $maxstrlen );
                                $fsize     = 100 - ( $scalingfactor * $remaining );
                            } else {
                                $fsize = 100;
                            }

                            ?>
                            <div class="preview" id="cape-preview-month-<?php echo $id; ?>">
                                <div class="applycapestyle">
                                    <span class="capetitle" style="font-size:<?php echo $fsize; ?>%"><?php echo $cape[ 'Title' ]; ?></span>

                                    <div class="microcolors">
                                        <?php
                                        foreach ( $colors as $i => $color ) {
                                            ?>
                                            <div class="microcolor" style="background-color:hsl(<?php echo $color[ 'H' ]; ?>, <?php echo $color[ 'S' ]; ?>%, <?php echo $color[ 'L' ]; ?>%)"
                                                 data-h="<?php echo $color[ 'H' ]; ?>" data-s="<?php echo $color[ 'S' ]; ?>" data-l="<?php echo $color[ 'L' ]; ?>"></div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>

                                <?php
                                if ( $loggedin ) {
                                    $choice = $dbf->queryToText( "SELECT Direction FROM capevotes WHERE UserID='$userid' AND CapeID='$id'" );
                                } else {
                                    $choice = 0;
                                }

                                if ( $choice == "" ) {
                                    $choice = 0;
                                }
                                ?>

                                <div class="vote" data-voted="<?php echo $choice == 0 ? "false" : "true"; ?>">
                                    <span class="icon-thumbs-up upvote <?php echo $choice == 1 ? "votedchoice" : ""; ?>" onclick="upvote(<?php echo $id; ?>, $(this));" id="upvote-month-<?php echo $id; ?>"></span> <span
                                        class="icon-thumbs-down downvote  <?php echo $choice == -1 ? "votedchoice" : ""; ?>" onclick="downvote(<?php echo $id; ?>, $(this));" id="downvote-month-<?php echo $id; ?>"></span>
                                </div>

                                <?php
                                $votetotal = $dbf->queryToText( "SELECT SUM(Direction) FROM capevotes WHERE CapeID='$id'" );
                                $votetotal = intval( $votetotal, 10 );

                                if ( $votetotal == "" || $votetotal == 0 ) {
                                    $votetotal = 0;
                                    $color     = "reszero";
                                    $sign      = "&nbsp;";
                                } elseif ( $votetotal > 0 ) {
                                    $color = "respos";
                                    $sign  = "+";
                                } else {
                                    $color = "resneg";
                                    $sign  = "-";
                                }
                                ?>
                                <span class="result <?php echo $color; ?>"><span class="result-sign"><?php echo $sign; ?></span><span class="result-number"><?php echo abs( $votetotal ); ?></span></span>

                                <div class="smallipop-hint">
                                    <h2 style="font-size:<?php echo round( 25 * ( $fsize / 100 ) ); ?>px"><?php echo $cape[ 'Title' ]; ?></h2>

                                    <p>Submitted by: <a target="_blank" href="<?php echo $dbf->basefilepath . "profile/$submitter"; ?>"> <?php echo $submitter; ?></a></p>

                                    <div class="capevoteresults">
                                        <?php
                                        $upvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=1 AND CapeID='$id'" );
                                        $downvotes = $dbf->queryToText( "SELECT COUNT(*) FROM capevotes WHERE Direction=-1 AND CapeID='$id'" );
                                        ?>
                                        <p class="votecount respos floatleft">Upvotes: <span class="upvotecount"><?php echo $upvotes; ?></span></p>

                                        <p class="votecount resneg floatright">Downvotes: <span class="downvotecount"><?php echo $downvotes; ?></span></p>
                                    </div>

                                    <div class="controls">
                                        <p class="floatleft"><a href="javascript:void(0);" onclick="flag(<?php echo $id; ?>);"><span class="icon-flag"></span> Flag</a></p>

                                        <?php
                                        if ( $favorited ) {
                                            ?>
                                            <p class="floatright"><a class="favoritebutton favorited" href="javascript:void(0);" onclick="unfavorite(<?php echo $id; ?>);"><span class="icon-star"></span> Unfavorite</a>
                                            </p>
                                        <?php
                                        } else {
                                            ?>
                                            <p class="floatright"><a class="favoritebutton" href="javascript:void(0);" onclick="favorite(<?php echo $id; ?>);"><span class="icon-star"></span> Favorite</a></p>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <p><a href="http://www.maxcape.com/designer/<? echo $id; ?>">Link to Cape</a></p>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div id="footer">
                <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
            </div>

            <div id="imageblinder" onclick="hideimg();">
                <div id="imagecontainer" class="innercontent">
                    <div id="imageslot"></div>
                    <button type="button" onclick="hideimg();">Close</button>
                </div>
            </div>

            <?php
            if ( $loggedin ) {
                ?>
                <div class="blinder">
                    <div id="container" class="centered innercontent">
                        <form id="saveform" method="post">
                            <label for="capename">Name:</label>
                            <input type="text" id="capename" name="capename" title="20 Characters Remaining">

                            <div class="char-remaining-container">
                                <p id="char-remaining" class="resneg"><span id="char-remaining-val"></span> Characters Remaining</p>

                                <script>
                                    var maxlength = 20;
                                    $("#char-remaining-val").text(maxlength);

                                    $("#capename").on("input", function () {
                                        var val = $(this).val(),
                                            length = val.length,
                                            remaining = maxlength - length;

                                        if (remaining < 0) {
                                            val = val.slice(0, -1);
                                            remaining = 0;
                                        }

                                        $("#char-remaining-val").text(remaining);
                                        $(this).val(val);
                                    });
                                </script>
                            </div>

                            <input type="hidden" id="color1Hval" name="color1Hval" value="356">
                            <input type="hidden" id="color1Sval" name="color1Sval" value="65">
                            <input type="hidden" id="color1Lval" name="color1Lval" value="56">
                            <input type="hidden" id="color2Hval" name="color2Hval" value="356">
                            <input type="hidden" id="color2Sval" name="color2Sval" value="52">
                            <input type="hidden" id="color2Lval" name="color2Lval" value="47">
                            <input type="hidden" id="color3Hval" name="color3Hval" value="356">
                            <input type="hidden" id="color3Sval" name="color3Sval" value="52">
                            <input type="hidden" id="color3Lval" name="color3Lval" value="37">
                            <input type="hidden" id="color4Hval" name="color4Hval" value="344">
                            <input type="hidden" id="color4Sval" name="color4Sval" value="38">
                            <input type="hidden" id="color4Lval" name="color4Lval" value="25">

                            <div id="btncon">
                                <button type="button" onclick="(function(e) { e.preventDefault(); hideblinder(); })(event)">Cancel</button>
                                <button type="submit">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php
            }
            ?>

            <script src="js/cape.js"></script>
            <script src="js/colorpicker.js"></script>
        </body>
    </html>

<?php
    $dbf->disconnectFromDatabase( $db[ 'handle' ] );
?>