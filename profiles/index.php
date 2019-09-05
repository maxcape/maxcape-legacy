<?php
    require_once("../rsfunctions.php");
    require_once("../dbfunctions.php");
    require_once( "../userfunctions.php" );
    $rsf = new rsfunctions;
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    function sum_arr( $array ) {
        $total = 0;

        foreach ( $array as $arr ) {
            $total += $arr;
        }

        return $total;
    }

    $username = $_GET[ 'user' ];

    $level99    = 13034431;
    $level120   = 104273166;
    $maxcapexp  = $level99 * 26;
    $compcapexp = ($level99 * 25) + $level120;

    $ranks               = array();
    $levels              = array();
    $experience          = array();
    $experienceRemaining = array();

    $loggedin   = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];

if ( isset( $username ) && $username != "" ) {
    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $username      = mysql_real_escape_string( $username );
        $profileexists = $dbf->queryToAssoc( "SELECT * FROM users WHERE username='$username' LIMIT 1" );
        if ( count( $profileexists ) == 0 ) {
            header( "Location: ../nr?badprofile" );
            die();
        } elseif ( $profileexists[ "ProfileVisible" ] == 0 && $myusername != $username ) {
            header( "Location: ../nr?privateprofile" );
            die();
        }

        $dbf->query( "UPDATE users SET ProfileViews=ProfileViews+1, LastProfileView=NOW() WHERE username='$username'" );
        $userdata = $dbf->queryToAssoc( "SELECT * FROM users WHERE username='$username' LIMIT 1" );

        $data = $rsf->updatePlayer( $userdata[ 'RSN' ] );

        $response = $data[ 0 ];
        $data     = $data[ 1 ];

        $data = preg_split( "/\s+/", $data );
        $skills = $dbf->getAllAssocResults( "SELECT * FROM skills ORDER BY Number ASC" );

        $skillnames = array();
        for ( $i = 0; $i < count( $skills ); $i++ ) {
            $skillnames[ ] = $skills[ $i ][ 'Name' ];
        }

        for ( $i = 0; $i < count( $skills ); $i++ ) {
            $skillNumber = $skills[ $i ][ 'Number' ];
            $thisSkill   = explode( ",", $data[ $skillNumber ] );

            if ( $thisSkill[ 0 ] == -1 ) {
                $thisSkill[ 0 ] = 0;
            }
            if ( $thisSkill[ 1 ] == -1 ) {
                $thisSkill[ 1 ] = 0;
            }
            if ( $thisSkill[ 2 ] == -1 ) {
                $thisSkill[ 2 ] = 0;
            }

            $ranks[ $skillNumber ]      = $thisSkill[ 0 ];
            $levels[ $skillNumber ]     = $thisSkill[ 1 ];
            $experience[ $skillNumber ] = $thisSkill[ 2 ];
        }

        if ( $levels[ 0 ] < 2673 ) {
            $milestone = ( floor( min( $levels ) / 10 ) * 10 );
            $msno      = $milestone / 10;
        } else {
            $maxed = true;
            $comped = true;
            foreach($levels as $i => $lvl) {
                if($i > 0 && ($i != 25 || $i != 27)) {
                    if($lvl < 99) {
                        $maxed = false;
                    }
                } else if($i == 25 || $i == 27) {
                    if($lvl < 99) {
                        $maxed = false;
                    }

                    if($lvl < 120) {
                        $comped = false;
                    }
                }
            }

            if($comped) {
                $milestone = "Completionist";
                $msno = 11;
            } elseif($maxed) {
                $milestone = "Max";
                $msno = 10;
            } else {
                $milestone = ( floor( min( $levels ) / 10 ) * 10 );
                $msno      = $milestone / 10;
            }
        }

        $dbf->query( "UPDATE apicache SET Milestone='$msno' WHERE RSN='$username'" );

        $dbf->query( "UPDATE userrequirements
					 SET Value='" . $levels[ 0 ] . "'
					 WHERE SubrequirementID = (
						 SELECT SubrequirementID
						 FROM subrequirements
						 WHERE Text = 'Total Level'
					 )
					 AND UserID = (
					 	 SELECT UserID
						 FROM users
						 WHERE Username = '$username'
					 )" );

        $xpToCompCape = 0;
        $xpToMaxCape  = 0;

        for($i = 1; $i < count($skills); $i++) {
            if($i != 25 && $i != 27) { //Not Dungeoneering or Invention
                if($experience[$i] < $level99) {
                    $experienceRemaining[$i] = $level99 - $experience[$i];
                } else {
                    $experienceRemaining[$i] = 0;
                }
            } elseif($i == 25) { //Dungeoneering
                if($experience[$i] < $level99) {
                    $experienceRemaining[$i] = $level99 - $experience[$i];
                } elseif($experience[$i] < $level120) {
                    $experienceRemaining[$i] = $level120 - $experience[$i];
                } else {
                    $experienceRemaining[$i] = 0;
                }
            } elseif($i == 27) {
                $invent99 = $rsf->getExperience(99, "invention");
                $invent120 = $rsf->getExperience(120, "invention");
                if($experience[$i] < $invent99) {
                    $experienceRemaining[$i] = $invent99 - $experience[$i];
                } elseif($experience[$i] < $invent120) {
                    $experienceRemaining[$i] = $invent120 - $experience[$i];
                } else {
                    $experienceRemaining[$i] = 0;
                }
            }
        }

        for($i = 1; $i < count($skills); $i++) {
            if($i != 25 && $i != 27) { //Not dungeoneerng or invention
                $xpToMaxCape += $experienceRemaining[$i];
                $xpToCompCape += $experienceRemaining[$i];
            } elseif($i == 25) {
                if($experience[$i] < $level99) {
                    $xpToMaxCape = $experienceRemaining[$i];
                    $xpToCompCape = $level120 - $experience[$i];
                } elseif($experience[$i] < $level120) {
                    $xpToMaxCape += 0;
                    $xpToCompCape += $experienceRemaining[$i];
                } else {
                    $xpToMaxCape += 0;
                    $xpToCompCape += 0;
                }
            } else {
                $invent99 = $rsf->getExperience(99, "invention");
                $invent120 = $rsf->getExperience(120, "invention");

                if($experience[$i] < $invent99) {
                    $xpToMaxCape = $experienceRemaining[$i];
                    $xpToCompCape = $invent120 - $experience[$i];
                } elseif($experience[$i] < $invent120) {
                    $xpToMaxCape += 0;
                    $xpToCompCape += $experienceRemaining[$i];
                } else {
                    $xpToMaxCape += 0;
                    $xpToCompCape += 0;
                }
            }
        }

//        for ( $i = 1; $i < count($skills); $i++ ) {
//            if ( $i != 25 ) {
//                if ( $experience[ $i ] < $level99 ) {
//                    $experienceRemaining[ $i ] = $level99 - $experience[ $i ];
//                } else {
//                    $experienceRemaining[ $i ] = 0;
//                }
//            } else {
//                if ( $experience[ $i ] < $level99 ) {
//                    $experienceRemaining[ $i ] = $level99 - $experience[ $i ];
//                    $dgTo120                   = $level120 - $experience[ $i ];
//                } else {
//                    if ( $experience[ $i ] < $level120 ) {
//                        if ( $milestone != "Max" && $milestone != "Completionist" ) {
//                            $experienceRemaining[ $i ] = 0;
//                            $xpToCompCape += $level120 - $experience[ $i ];
//                        } else {
//                            $experienceRemaining[ $i ] = $level120 - $experience[ $i ];
//                        }
//
//                    } else {
//                        $experienceRemaining[ $i ] = 0;
//                    }
//                }
//            }
//        }
//
//        for ( $i = 1; $i <= count($skills); $i++ ) {
//            if ( $i != 25 ) {
//                $xpToMaxCape += $experienceRemaining[ $i ];
//                $xpToCompCape += $experienceRemaining[ $i ];
//            } else {
//                if ( $experience[ $i ] < $level99 ) {
//                    $xpToMaxCape += $experienceRemaining[ $i ];
//                    $xpToCompCape += $dgTo120;
//                } else {
//                    if ( $experience[ $i ] >= $level99 && $experience[ $i ] < $level120 ) {
//                        $xpToCompCape += $experienceRemaining[ $i ];
//                    }
//                }
//            }
//        }
        ?>
        <!DOCTYPE html>

        <html>
            <head>
                <title><?php echo $username; ?>'s Profile - Max/Completionist Cape Calculator</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/rss.css">
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/profile.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/justgage/js/raphael.2.1.0.min.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/justgage/js/justgage.1.0.1.min.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.collapse.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.easing.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/highcharts.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/modules/exporting.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/gray.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>profiles/jquery.zrssfeed.min.js"></script>

                <script>
                    $(document).ready(function () {
                        $("#alog").rssfeed("http://services.runescape.com/m=adventurers-log/rssfeed?searchName=<?php echo $userdata['RSN']; ?>", {
                            limit: 10,
                            header: false,
                            date: false,
                            content: false,
                            snippet: false,
                            media: false
                        });

                        $("#alog").mouseenter(function() {
                            var left = ($(this).offset().left);
                            var top = ($(this).offset().top);

                            $(this).css("position", "absolute").css("top", top + "px").css("left", left + "px").css( "width", "248px").css("box-shadow", "-2px 2px 5px 1px black");
                            $(this).animate({height: "300px"}, 100, function() {
                                $(this).css("position", "absolute");
                            });

                        }).mouseleave(function() {
                            $(this).animate({height:"153px"}, 100, function() {
                                $(this).css("position", "static");
                                $(this).css("box-shadow", "none");
                            });
                        });

                        var g1 = new JustGage({
                            id: "max-gauge",
                            value: <?php echo number_format((($maxcapexp - $xpToMaxCape)/$maxcapexp)*100, 2); ?>,
                            min: 0,
                            max: 100,
                            title: "Max Cape",
                            label: "%",
                            valueFontColor: "#1C1C1C",
                            labelFontColor: "#1C1C1C",
                            titleFontColor: "#1C1C1C",
                            levelColors: [
                                "#FF0000",
                                "#F9C802",
                                "#A9D70B"
                            ]
                        });

                        var g2 = new JustGage({
                            id: "comp-gauge",
                            value: <?php echo number_format((($compcapexp - $xpToCompCape)/$compcapexp)*100, 2); ?>,
                            min: 0,
                            max: 100,
                            title: "Completionist Cape",
                            label: "%",
                            valueFontColor: "#1C1C1C",
                            labelFontColor: "#1C1C1C",
                            titleFontColor: "#1C1C1C",
                            levelColors: [
                                "#FF0000",
                                "#F9C802",
                                "#A9D70B"
                            ]
                        });

                        $(function () {
                            $(window).scroll(function () {
                                if ($(this).scrollTop() > 10) {
                                    $('#back-top').fadeIn();
                                } else {
                                    $('#back-top').fadeOut();
                                }
                            });

                            $('#back-top a').click(function () {
                                $('body,html').animate({
                                    scrollTop: 0
                                }, 300);
                                return false;
                            });
                        });
                    });

                    $(function () {
                        $('#regularreqs').jqcollapse({
                            slide: true,
                            speed: 150,
                            easing: 'easeOutCubic'
                        });
                        $('#trimmedreqs').jqcollapse({
                            slide: true,
                            speed: 150,
                            easing: 'easeOutCubic'
                        });
                    });

                    function toggle(dir) {
                        $(".jqcNode").each(function () {
                            var a = $(this);
                            var arr = a.children('img.arrow');
                            var check = a.children('img.marker');

                            if (dir == 'close') {
                                if (arr.hasClass('down')) {
                                    a.click();
                                }
                            } else if (dir == 'open') {
                                if (!arr.hasClass('down')) {
                                    a.click();
                                }
                            } else if (dir == 'complete') {
                                if (check.hasClass('done') && !arr.hasClass('down')) {
                                    a.click();
                                }

                                if (check.hasClass('notdone') && arr.hasClass('down')) {
                                    a.click();
                                }

                                if (check.hasClass('partial') && arr.hasClass('down')) {
                                    a.click();
                                }
                            } else if (dir == 'incomplete') {
                                if (check.hasClass('notdone') && !arr.hasClass('down')) {
                                    a.click();
                                }

                                if (check.hasClass('done') && arr.hasClass('down')) {
                                    a.click();
                                }

                                if (check.hasClass('partial') && arr.hasClass('down')) {
                                    a.click();
                                }
                            } else if (dir == 'partial') {
                                if (check.hasClass('partial') && !arr.hasClass('down')) {
                                    a.click();
                                }

                                if (check.hasClass('done') && arr.hasClass('down')) {
                                    a.click();
                                }

                                if (check.hasClass('notdone') && arr.hasClass('down')) {
                                    a.click();
                                }
                            }

                        });
                    }
                </script>
            </head>

            <body>
                <?php
                require_once( "../masthead.php" );
                ?>

                <div class="colcontainer">
                    <?php
                    if ( $response == "not in use" ) {
                        ?>
                        <div class="alert-message error">
                            <a class="close icon-remove-sign" href="#"></a>

                            <p><strong>Error: </strong>This user's RSN is out of date.</p>
                        </div>
                    <?php
                    }
                    ?>
                    <div id="userinfo" class="first">
                        <div class="inner" style="padding-top:10px;">
                            <p>RSN</p>

                            <h3><?php echo $userdata[ 'RSN' ]; ?></h3>

                            <p>Times Searched</p>

                            <h3><?php echo number_format( $dbf->queryToText( "SELECT TimesSearched FROM searches_new WHERE RSN='" . $userdata[ 'RSN' ] . "'" ) ); ?></h3>

                            <p>Profile Views</p>

                            <h3><?php echo number_format( $userdata[ 'ProfileViews' ] ); ?></h3>

                            <p>Milestone</p>

                            <h3><?php echo $milestone; ?></h3>
                            <a target="_blank" href="<?php echo $dbf->basefilepath; ?>calc/<?php echo $userdata[ 'RSN' ]; ?>">View Calculator &rarr;</a>
                        </div>
                    </div>

                    <div id="maxinfo">
                        <div id="max-gauge">

                        </div>
                    </div>

                    <div id="compinfo">
                        <div id="comp-gauge">

                        </div>
                    </div>

                    <div id="alog" class="last">

                    </div>
                </div>

                <div id="reqcontainer">
                    <div id="controls">
                        <a href="javascript:void(0);" onclick="toggle('close');"
                           onmousedown="$(this).toggleClass('mousedown');"
                           onmouseup="$(this).toggleClass('mousedown');"
                           onmouseout="if ($(this).hasClass('mousedown')) { $(this).toggleClass('mousedown') }">Close All</a> <a href="javascript:void(0);" onclick="toggle('open');"
                                                                                                                                 onmousedown="$(this).toggleClass('mousedown');"
                                                                                                                                 onmouseup="$(this).toggleClass('mousedown');"
                                                                                                                                 onmouseout="if ($(this).hasClass('mousedown')) { $(this).toggleClass('mousedown') }">Open All</a>
                        <a href="javascript:void(0);" onclick="toggle('complete');"
                           onmousedown="$(this).toggleClass('mousedown');"
                           onmouseup="$(this).toggleClass('mousedown');"
                           onmouseout="if ($(this).hasClass('mousedown')) { $(this).toggleClass('mousedown') }">Only Open Complete</a> <a href="javascript:void(0);" onclick="toggle('partial');"
                                                                                                                                          onmousedown="$(this).toggleClass('mousedown');"
                                                                                                                                          onmouseup="$(this).toggleClass('mousedown');"
                                                                                                                                          onmouseout="if ($(this).hasClass('mousedown')) { $(this).toggleClass('mousedown') }">Only Open Partial</a>
                        <a href="javascript:void(0);" onclick="toggle('incomplete');"
                           onmousedown="$(this).toggleClass('mousedown');"
                           onmouseup="$(this).toggleClass('mousedown');"
                           onmouseout="if ($(this).hasClass('mousedown')) { $(this).toggleClass('mousedown') }">Only Open Incomplete</a>
                    </div>
                    <div id="regular">
                        <h1>Regular</h1>
                        <ul id="regularreqs">
                            <?php
                            $req = $dbf->getAllAssocResults( "SELECT * FROM requirements WHERE CapeType=1" );

                            foreach ( $req AS $requirement ) {
                                $subreq                = $dbf->getAllAssocResults( "
										SELECT Text, Number, IFNULL(Value, 0) Value
										FROM subrequirements s
										LEFT OUTER JOIN userrequirements u
											ON s.SubrequirementID = u.SubrequirementID
											AND UserID='" . $userdata[ 'UserID' ] . "'
										WHERE RequirementID='" . $requirement[ 'RequirementID' ] . "'
									" );
                                $done                  = true;
                                $completionpercentages = array();

                                foreach ( $subreq as $subrequirement ) {
                                    $completionpercentages[ ] = $subrequirement[ 'Value' ] / $subrequirement[ 'Number' ];

                                    if ( $subrequirement[ 'Value' ] < $subrequirement[ 'Number' ] ) {
                                        $done = false;
                                    } elseif ( $subrequirement[ 'Value' ] >= $subrequirement[ 'Number' ] && $done != false ) {
                                        $done = true;
                                    }
                                }

                                $completionpercentage = number_format( ( sum_arr( $completionpercentages ) / count( $completionpercentages ) ) * 100 );

                                if ( $completionpercentage == 100 && !$done ) {
                                    $completionpercentage = 99;
                                }
                                ?>
                                <li>
                                    <img class="arrow" src="<?php echo $dbf->basefilepath; ?>images/arrowup.png">
                                    <span><?php echo str_replace( "\\", "", $requirement[ 'Text' ] );  ?></span>
                                    <?php
                                    if ( $done ) {
                                        ?>
                                        <img class="marker done" src="<?php echo $dbf->basefilepath; ?>images/checkmark.png">
                                    <?php
                                    } elseif ( $completionpercentage != 0 ) {
                                        ?>
                                        <img class="marker partial" src="<?php echo $dbf->basefilepath; ?>images/circle.png">
                                    <?php
                                    } else {
                                        ?>
                                        <img class="marker notdone" src="<?php echo $dbf->basefilepath; ?>images/bigx.png">
                                    <?php
                                    }
                                    ?>
                                    <span class="completion"><?php echo $completionpercentage; ?>%</span>
                                    <ul class="sub">
                                        <?php
                                        foreach ( $subreq as $subrequirement ) {
                                            if ( $subrequirement[ 'Number' ] > 1 ) {
                                                if ( $subrequirement[ 'Value' ] >= $subrequirement[ 'Number' ] ) {
                                                    ?>
                                                    <li class="complete"><?php echo number_format( $subrequirement[ 'Value' ] ) . "/" . number_format( $subrequirement[ 'Number' ] ) . " " . str_replace( "\\", "", $subrequirement[ 'Text' ] );  ?></li>
                                                <?php
                                                } else {
                                                    ?>
                                                    <li class="incomplete"><?php echo number_format( $subrequirement[ 'Value' ] ) . "/" . number_format( $subrequirement[ 'Number' ] ) . " " . str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                }
                                            } else {
                                                if ( $subrequirement[ 'Value' ] >= $subrequirement[ 'Number' ] ) {
                                                    ?>
                                                    <li class="complete"><?php echo str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                } else {
                                                    ?>
                                                    <li class="incomplete"><?php echo str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </ul>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>

                    </div>

                    <div id="trimmed">
                        <h1>Trimmed</h1>
                        <ul id="trimmedreqs">
                            <?php
                            $req = $dbf->getAllAssocResults( "SELECT * FROM requirements WHERE CapeType=2" );

                            foreach ( $req AS $requirement ) {
                                $subreq                = $dbf->getAllAssocResults( "
										SELECT Text, Number, IFNULL(Value, 0) Value
										FROM subrequirements s
										LEFT OUTER JOIN userrequirements u
											ON s.SubrequirementID = u.SubrequirementID
											AND UserID='" . $userdata[ 'UserID' ] . "'
										WHERE RequirementID='" . $requirement[ 'RequirementID' ] . "'
									" );
                                $done                  = true;
                                $completionpercentages = array();

                                foreach ( $subreq as $subrequirement ) {
                                    $completionpercentages[ ] = $subrequirement[ 'Value' ] / $subrequirement[ 'Number' ];

                                    if ( $subrequirement[ 'Value' ] < $subrequirement[ 'Number' ] ) {
                                        $done = false;
                                    } elseif ( $subrequirement[ 'Value' ] >= $subrequirement[ 'Number' ] && $done != false ) {
                                        $done = true;
                                    }
                                }

                                $completionpercentage = number_format( ( sum_arr( $completionpercentages ) / count( $completionpercentages ) ) * 100 );

                                if ( $completionpercentage == 100 && !$done ) {
                                    $completionpercentage = 99;
                                }
                                ?>
                                <li>
                                    <img class="arrow" src="<?php echo $dbf->basefilepath; ?>images/arrowup.png">
                                    <span><?php echo str_replace( "\\", "", $requirement[ 'Text' ] );  ?></span>
                                    <?php
                                    if ( $done ) {
                                        ?>
                                        <img class="marker done" src="<?php echo $dbf->basefilepath; ?>images/checkmark.png">
                                    <?php
                                    } elseif ( $completionpercentage != 0 ) {
                                        ?>
                                        <img class="marker partial" src="<?php echo $dbf->basefilepath; ?>images/circle.png">
                                    <?php
                                    } else {
                                        ?>
                                        <img class="marker notdone" src="<?php echo $dbf->basefilepath; ?>images/bigx.png">
                                    <?php
                                    }
                                    ?>
                                    <span class="completion"><?php echo $completionpercentage; ?>%</span>
                                    <ul class="sub">
                                        <?php
                                        foreach ( $subreq as $subrequirement ) {
                                            if ( $subrequirement[ 'Number' ] > 1 ) {
                                                if ( $subrequirement[ 'Value' ] >= $subrequirement[ 'Number' ] ) {
                                                    ?>
                                                    <li class="complete"><?php echo number_format( $subrequirement[ 'Value' ] ) . "/" . number_format( $subrequirement[ 'Number' ] ) . " " . str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                } else {
                                                    ?>
                                                    <li class="incomplete"><?php echo number_format( $subrequirement[ 'Value' ] ) . "/" . number_format( $subrequirement[ 'Number' ] ) . " " . str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                }
                                            } else {
                                                if ( $subrequirement[ 'Value' ] >= $subrequirement[ 'Number' ] ) {
                                                    ?>
                                                    <li class="complete"><?php echo str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                } else {
                                                    ?>
                                                    <li class="incomplete"><?php echo str_replace( "\\", "", $subrequirement[ 'Text' ] ); ?></li>
                                                <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </ul>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>

                <div id="xp-dist-chart">
                    <script>
                        $(function () {
                            var chart;
                            $(document).ready(function () {
                                chart = new Highcharts.Chart({
                                    chart: {
                                        renderTo: 'xp-dist-chart',
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Experience Distribution'
                                    },
                                    subtitle: {
                                        text: '<?php echo $username; ?>'
                                    },
                                    xAxis: {
                                        categories: [
                                            'Attack', 'Defence', 'Strength', 'Constitution', 'Ranged', 'Prayer', 'Magic', 'Cooking', 'Woodcutting', 'Fletching', 'Fishing', 'Firemaking', 'Crafting', 'Smithing', 'Mining', 'Herblore', 'Agility', 'Thieving', 'Slayer', 'Farming', 'Runecraft', 'Hunter', 'Construction', 'Summoning', 'Dungeoneering', 'Divination'
                                        ],
                                        labels: {
                                            rotation: -90,
                                            align: 'right'
                                        }
                                    },
                                    yAxis: {
                                        min: 0,
                                        title: {
                                            text: 'Experience'
                                        }
                                    },
                                    tooltip: {
                                        formatter: function () {
                                            return '' +
                                                this.x + ': ~' + this.y + "% of overall experience";
                                        }
                                    },
                                    plotOptions: {
                                        column: {
                                            pointPadding: 0.2,
                                            borderWidth: 0
                                        }
                                    },
                                    series: [
                                        {
                                            name: '<?php echo $profileexists['RSN']; ?>',
                                            data: [
                                                <?php
                                                    $oaxp = $experience[0];
                                                    for($i = 1; $i < count($skills); $i++) {
                                                        $xp = $experience[$i];
                                                        echo number_format( ( $xp / $oaxp ) * 100, 1 );

                                                        if($i < count($skills) - 1) {
                                                            echo ", ";
                                                        }
                                                    }
                                                ?>
                                            ]
                                        }
                                    ]
                                });
                            });
                        });
                    </script>
                </div>

                <div id="footer">
                    <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
                </div>

                <div id="back-top">
                    <a title="Return to Top">&#8593;</a>
                </div>
            </body>
        </html>
        <?php
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        ?>
        <h1>Cannot find or connect to database.</h1>
    <?php
    }
} else {
    ?>
    <script>
        document.location = "/nr";
    </script>
<?php
}
