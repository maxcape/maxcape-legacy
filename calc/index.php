<?php
    //error_reporting( 0 );

    require_once("../rsfunctions.php");
    require_once("../dbfunctions.php");
    require_once( "../userfunctions.php" );
    require_once("badges.php");
    $rsf = new rsfunctions;
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];

    $level99 = 13034431;
    $level120 = 104273166;
    $maxcapexp = ($level99 * 26) + $rsf->getExperience(99, "invention");
    $compcapexp = ( $level99 * 25 ) + $level120 + $rsf->getExperience(120, "invention");

    $xpToMaxCape = 0;
    $xpToCompCape = 0;

    $ranks = array();
    $levels = array();
    $experience = array();
    $experienceRemaining = array();

    function time_elapsed_string( $ptime ) {
        $etime = time() - $ptime;

        if ( $etime < 1 ) {
            return '0 seconds';
        }

        $etime = 7200 - $etime;

        $a = array(
            60 * 60 => 'hour', 60 => 'minute', 1 => 'second'
        );

        $output = "";

        foreach ( $a as $secs => $str ) {
            $d = $etime / $secs;
            if ( $d >= 1 ) {
                $r = floor( $d );
                $output .= "<b>" . $r . "</b>" . ' ' . $str . ( $r > 1 ? 's ' : ' ' );

                $etime -= floor( $d ) * $secs;
            }
        }

        $output .= "until next update";

        return $output;
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    $username = $_GET[ 'name' ];

    if ( isset( $username ) && $username != "" ) {
        $data     = $rsf->updatePlayer( $username );
        $response = $data[ 0 ];
        $data     = $data[ 1 ];
        if ( strstr( $data, "<html>" ) || $data == "" ) {
            //$dbf->query( "INSERT INTO failedsearches (Source, Time) VALUES ('calc', NOW())" );
            ?>
            <script>
                document.location = "/badrsn/<?php echo $username; ?>";
            </script>
            <?php
            die();
        } else {
//            $dbf->query("UPDATE apicache SET Response='$result', TimeFetched=NOW() WHERE RSN='$rsn'");
        }

        if ( $response == "cached" ) {
            echo "<!--This is a cached version of this players stats. They will next be updated within the next two hours.-->";
        }

        $apiResp = $data;
        $data = preg_split( "/\s+/", $data );

        if ( $db[ 'found' ] ) {
            $safeUsername = mysql_real_escape_string( $username );
            $dbf->query( "INSERT INTO searches_new (RSN, TimesSearched, LastSearchDate) VALUES ('$safeUsername', 1, NOW()) ON DUPLICATE KEY UPDATE TimesSearched=TimesSearched + 1, LastSearchDate = NOW()" );
            $dbf->query("INSERT INTO searches (RSN, Time) VALUES ('$safeUsername', NOW())");
            $dbf->query("INSERT INTO apicache (RSN, Response, TimeFetched) VALUES ('$safeUsername', '$apiResp', NOW()) ON DUPLICATE KEY UPDATE Response='$apiResp', TimeFetched=NOW()");

            //$dbf->query("INSERT INTO searchIP (SearchString, IPAddress) VALUES ('$safeUsername', '" . $_SERVER['REMOTE_ADDR'] . "')");

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

            if ($levels[0] < 2673) {
                $milestone = (floor(min($levels) / 10) * 10);
                $msno = $milestone / 10;
            } else {
                $notmaxed = false;
                if ($levels[0] >= 2673 && $levels[0] < 2715) {
                    for ($i = 1; $i < count($levels); $i++) {
                        if ($levels[$i] < 99) {
                            $notmaxed = true;
                        }
                    }

                    if ($notmaxed) {
                        $milestone = (floor(min($levels) / 10) * 10);
                        $msno = $milestone / 10;
                    } else {
                        $milestone = "Max";
                        $msno = 10;
                    }

                } else {
                    $milestone = "Completionist";
                    $msno = 11;
                }
            }

//            if ( $levels[ 0 ] < 2673 ) {
//                $milestone = ( floor( min( $levels ) / 10 ) * 10 );
//                $msno      = $milestone / 10;
//            } else {
//                $maxed = true;
//                $comped = true;
//                foreach($levels as $i => $lvl) {
//                    if($i > 0 && ($i != 25 || $i != 27)) {
//                        if($lvl < 99) {
//                            $maxed = false;
//                        }
//                    } else if($i == 25 || $i == 27) {
//                        if($lvl < 99) {
//                            $maxed = false;
//                        }
//
//                        if($lvl < 120) {
//                            $comped = false;
//                        }
//                    }
//                }
//
//                if($comped) {
//                    $milestone = "Completionist";
//                    $msno = 11;
//                } elseif($maxed) {
//                    $milestone = "Max";
//                    $msno = 10;
//                } else {
//                    $milestone = ( floor( min( $levels ) / 10 ) * 10 );
//                    $msno      = $milestone / 10;
//                }


//                if ( $levels[ 0 ] >= 2574 && $levels[ 0 ] < 2595 ) {
//                    for ( $i = 1; $i < count( $levels ); $i++ ) {
//                        if ( $levels[ $i ] < 99 ) {
//                            $notmaxed = true;
//                        }
//                    }
//
//                    if ( $notmaxed ) {
//                        $milestone = ( floor( min( $levels ) / 10 ) * 10 );
//                        $msno      = $milestone / 10;
//                    } else {
//                        $milestone = "Max";
//                        $msno      = 10;
//                    }
//
//                } else {
//                    $milestone = "Completionist";
//                    $msno      = 11;
//                }
//            }

            $dbf->query( "UPDATE apicache SET Milestone='$msno' WHERE RSN='$safeUsername'" );

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

//            echo "<div style='width:10px; height:10px; background:white; outline:thin solid red; display:inline; overflow: hidden;'>";
//            print_r($experienceRemaining);
//            echo "</div>";

            for($i = 1; $i < count($skills); $i++) {
                if($i != 25 && $i != 27) { //Not dungeoneerng or invention
                    $xpToMaxCape += $experienceRemaining[$i];
                    $xpToCompCape += $experienceRemaining[$i];
                } elseif($i == 25) {
                    if($experience[$i] < $level99) {
                        $xpToMaxCape += $experienceRemaining[$i];
                        $xpToCompCape += $level120 - $experience[$i];
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
                        $xpToMaxCape += $experienceRemaining[$i];
                        $xpToCompCape += $invent120 - $experience[$i];
                    } elseif($experience[$i] < $invent120) {
                        $xpToMaxCape += 0;
                        $xpToCompCape += $experienceRemaining[$i];
                    } else {
                        $xpToMaxCape += 0;
                        $xpToCompCape += 0;
                    }
                }
            }

//            for ( $i = 1; $i < count( $skills ); $i++ ) {
//                if ( $i != 25 && $i != 27 ) {
//                    if ( $experience[ $i ] < $level99 ) {
//                        $experienceRemaining[ $i ] = $level99 - $experience[ $i ];
//                    } else {
//                        $experienceRemaining[ $i ] = 0;
//                    }
//                } else if($i == 25) {
//                    if ( $experience[ $i ] < $level99 ) {
//                        $experienceRemaining[ $i ] = $level99 - $experience[ $i ];
//                        $dgTo120                   = $level120 - $experience[ $i ];
//                    } else {
//                        if ( $experience[ $i ] < $level120 ) {
//                            if ( $milestone != "Max" && $milestone != "Completionist" ) {
//                                $experienceRemaining[ $i ] = 0;
//                                $xpToCompCape += $level120 - $experience[ $i ];
//                            } else {
//                                $experienceRemaining[ $i ] = $level120 - $experience[ $i ];
//                            }
//
//                        } else {
//                            $experienceRemaining[ $i ] = 0;
//                        }
//                    }
//                } else {
//                    if ( $experience[ $i ] < $level99 ) {
//                        $experienceRemaining[ $i ] = $level99 - $experience[ $i ];
//                        $dgTo120                   = $level120 - $experience[ $i ];
//                    } else {
//                        if ( $experience[ $i ] < $rsf->getExperience(120, "invention") ) {
//                            if ( $milestone != "Max" && $milestone != "Completionist" ) {
//                                $experienceRemaining[ $i ] = 0;
//                                $xpToCompCape += $level120 - $experience[ $i ];
//                            } else {
//                                $experienceRemaining[ $i ] = $level120 - $experience[ $i ];
//                            }
//
//                        } else {
//                            $experienceRemaining[ $i ] = 0;
//                        }
//                    }
//                }
//            }
//
//
//            for ( $i = 1; $i <= count( $skills ); $i++ ) {
//                if ( $i != 25 ) {
//                    $xpToMaxCape += $experienceRemaining[ $i ];
//                    $xpToCompCape += $experienceRemaining[ $i ];
//                } else {
//                    if ( $experience[ $i ] < $level99 ) {
//                        $xpToMaxCape += $experienceRemaining[ $i ];
//                        $xpToCompCape += $dgTo120;
//                    } else {
//                        if ( $experience[ $i ] >= $level99 && $experience[ $i ] < $level120 ) {
//                            $xpToCompCape += $experienceRemaining[ $i ];
//                        }
//                    }
//                }
//            }

            $cmb         = array_combine( $skillnames, $levels );
            $combatlevel =  floor(0.25 * (1.3 * max($cmb['Attack'] + $cmb['Strength'], 2 * $cmb['Magic'],
                                             2 * $cmb['Ranged']) + $cmb['Defence'] + $cmb['Constitution'] + (0.5 *
                                                $cmb['Prayer']) + (0.5 * $cmb['Summoning'])));
            ?>
            <!DOCTYPE html>
            <html>
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                    <meta name="og:title" content="RuneScape Max/Comp Cape Calculator">
                    <meta name="description" content="Calculate how much experience you need for the Max and Completionist Cape.">
                    <meta name="og:description" content="Calculate how much experience you need for the Max and Completionist Cape.">
                    <title><?php echo $username; ?> - Max/Completionist Cape Calculator</title>


                    <?php
                        if ( $_GET[ 'beta' ] == 1 ) {
                            ?>
                            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper2.css">
                            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/theme/calc.css">
                        <?php
                        } else {
                            ?>
                            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/calc.css">
                        <?php
                        }
                    ?>

                    <link rel="stylesheet" href="badge_sprites.css"/>


                    <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
                    <script src="<?php echo $dbf->basefilepath; ?>js/justgage/js/raphael.2.1.0.min.js"></script>
                    <script src="<?php echo $dbf->basefilepath; ?>js/justgage/js/justgage.1.0.1.min.js"></script>
                    <script src="<?php echo $dbf->basefilepath; ?>js/jquery.tinysort.min.js"></script>
                    <script src="<?php echo $dbf->basefilepath; ?>js/highcharts.js"></script>
                    <script src="<?php echo $dbf->basefilepath; ?>js/modules/exporting.js"></script>
                    <script src="<?php echo $dbf->basefilepath; ?>js/gray.js"></script>

                    <script type="text/javascript">
                        var skills = ['Attack', 'Defence', 'Strength', 'Constitution', 'Ranged', 'Prayer', 'Magic', 'Cooking', 'Woodcutting', 'Fletching', 'Fishing', 'Firemaking', 'Crafting', 'Smithing', 'Mining', 'Herblore', 'Agility', 'Thieving', 'Slayer', 'Farming', 'Runecrafting', 'Hunter', 'Construction', 'Summoning', 'Dungeoneering', 'Divination', "Invention"];
                        var g;
                        var currentsort = {"col": 0, "order": 0};
                        var viewAnims = true;

                        function toggleAnims() {
                            viewAnims = !viewAnims;

                            var toggle = $("#toggleAnims");
                            if (viewAnims) {
                                toggle.css("color", "green").attr("title", "Animations WILL be shown while sorting.");
                                toggle.find("i").removeClass("icon-remove").addClass("icon-ok");
                            } else {
                                toggle.css("color", "red").attr("title", "Animations will NOT be shown while sorting.");
                                toggle.find("i").removeClass("icon-ok").addClass("icon-remove");
                            }

                            if (typeof(Storage) !== "undefined") {
                                localStorage.setItem("anims", viewAnims);
                            }
                        }


                        function sort(col, order) {
                            var sort = $(".skill");

                            var container = $("#skilllist");
                            container.css({position: "relative", height: container.height(), display: "block"});
                            var iLnH;
                            sort.each(function (i, el) {
                                var iY = $(el).position().top;
                                $.data(el, 'h', iY);
                                if (i === 1) iLnH = iY;
                            });

                            if (order == 0) {
                                $.tinysort.defaults.order = 'asc';
                            } else {
                                $.tinysort.defaults.order = 'desc';
                            }

                            if (viewAnims) {
                                if (col != currentsort.col) {
                                    switch (col) {
                                        case 0:
                                            sort.tsort({attr: 'data-number'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: ""});
                                                });
                                            });
                                            break;
                                        case 1:
                                            sort.tsort({attr: 'data-skill'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 2:
                                            sort.tsort({attr: 'data-rank'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 3:
                                            sort.tsort({attr: 'data-level'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 4:
                                            sort.tsort({attr: 'data-vlvl'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 5:
                                            sort.tsort({attr: 'data-exp'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 6:
                                            sort.tsort({attr: 'data-tnl'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 7:
                                            sort.tsort({attr: 'data-remaining'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                        case 8:
                                            sort.tsort({attr: 'data-percentage'}).each(function (i, el) {
                                                var $El = $(el);
                                                var iFr = $.data(el, 'h');
                                                var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                    $El.css({position: "static"});
                                                    $("#skilllist").css({height: "auto"});
                                                });
                                            });
                                            break;
                                    }

                                    for (var i = 0; i <= 8; i++) {
                                        var icon = $("#sort-" + i);

                                        if (i == col) {
                                            currentsort.col = col;
                                            currentsort.order = order;

                                            if (order == 0) {
                                                icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-up.png");
                                            } else {
                                                icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-down.png");
                                            }
                                        } else {
                                            icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown.png");
                                        }
                                    }
                                } else {
                                    if (order != currentsort.order) {
                                        switch (col) {
                                            case 0:
                                                sort.tsort({attr: 'data-number'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 1:
                                                sort.tsort({attr: 'data-skill'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 2:
                                                sort.tsort({attr: 'data-rank'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 3:
                                                sort.tsort({attr: 'data-level'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 4:
                                                sort.tsort({attr: 'data-vlvl'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 5:
                                                sort.tsort({attr: 'data-exp'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 6:
                                                sort.tsort({attr: 'data-tnl'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 7:
                                                sort.tsort({attr: 'data-remaining'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                            case 8:
                                                sort.tsort({attr: 'data-percentage'}).each(function (i, el) {
                                                    var $El = $(el);
                                                    var iFr = $.data(el, 'h');
                                                    var iTo = (i * Math.round(iLnH / 2)) + 31;
                                                    $El.css({position: "absolute", top: iFr, width: "100%"}).animate({top: iTo}, 300, function () {
                                                        $El.css({position: "static"});
                                                        $("#skilllist").css({height: "auto"});
                                                    });
                                                });
                                                break;
                                        }

                                        for (var i = 0; i <= 8; i++) {
                                            var icon = $("#sort-" + i);

                                            if (i == col) {
                                                currentsort.col = col;
                                                currentsort.order = order;

                                                if (order == 0) {
                                                    icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-up.png");
                                                } else {
                                                    icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-down.png");
                                                }
                                            } else {
                                                icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown.png");
                                            }
                                        }
                                    }
                                }
                            } else {
                                switch (col) {
                                    case 0:
                                        sort.tsort({attr: 'data-number'});
                                        break;
                                    case 1:
                                        sort.tsort({attr: 'data-skill'});
                                        break;
                                    case 2:
                                        sort.tsort({attr: 'data-rank'});
                                        break;
                                    case 3:
                                        sort.tsort({attr: 'data-level'});
                                        break;
                                    case 4:
                                        sort.tsort({attr: 'data-vlvl'});
                                        break;
                                    case 5:
                                        sort.tsort({attr: 'data-exp'});
                                        break;
                                    case 6:
                                        sort.tsort({attr: 'data-tnl'});
                                        break;
                                    case 7:
                                        sort.tsort({attr: 'data-remaining'});
                                        break;
                                    case 8:
                                        sort.tsort({attr: 'data-percentage'});
                                        break;
                                }

                                for (var i = 0; i <= 8; i++) {
                                    var icon = $("#sort-" + i);

                                    if (i == col) {
                                        currentsort.col = col;
                                        currentsort.order = order;

                                        if (order == 0) {
                                            icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-up.png");
                                        } else {
                                            icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-down.png");
                                        }
                                    } else {
                                        icon.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown.png");
                                    }
                                }
                            }
                        }

                        function defname(username) {
                            var defnamebtn = $("#defnameBtn");

                            if (defnamebtn.attr("class") == "defnamenotset") {
                                $.post("setcookies.php", {
                                    name: username,
                                    type: "set"
                                }, function (data) {
                                    defnamebtn.removeClass("defnamenotset");
                                    defnamebtn.addClass("defnameset");
                                    defnamebtn.empty().append($("<i>").addClass("icon-ok"))
                                });
                            } else {
                                $.post("setcookies.php", {
                                    name: username,
                                    type: "clear"
                                }, function () {
                                    defnamebtn.removeClass("defnameset");
                                    defnamebtn.addClass("defnamenotset");
                                    defnamebtn.empty().append($("<i>").addClass("icon-remove"))
                                });
                            }
                        }

                        function pad(num, size) {
                            var s = num + "";
                            while (s.length < size) s = "0" + s;
                            return s;
                        }

                        function hideDone() {
                            for (var i = 0; i < skills.length; i++) {
                                var selector = $('#' + skills[i]);
                                var parent = selector.parents(".skill");

                                if (parent.attr("data-percentage") == "100") {
                                    parent.toggleClass('hide');
                                    $("#" + skills[i] + "-box").toggleClass("hide");
                                }
                            }
                        }

                        $(document).ready(function () {
                            var loadedAnims = localStorage.getItem("anims");

                            if (loadedAnims == "false") {
                                toggleAnims();
                            }

                            $(".close").click(function () {
                                $(this).parent().fadeOut();
                            });

                            g = new JustGage({
                                id: "gauge",
                                value: <?php echo $milestone === "Completionist" || $milestone === "Max" ? number_format((($compcapexp - $xpToCompCape)/$compcapexp)*100, 2) : number_format((($maxcapexp - $xpToMaxCape)/$maxcapexp)*100, 2); ?>,
                                min: 0,
                                max: 100,
                                title: "<?php echo $milestone === "Completionist" || $milestone === "Max" ? "Completionist Cape" : "Max Cape"; ?>",
                                label: "%",
                                valueFontColor: "#AAA",
                                refreshAnimationTime: 350,
                                levelColors: [
                                    "#FF0000",
                                    "#F9C802",
                                    "#A9D70B"
                                ]
                            });

                            $("#milestone").change(function () {
                                var value = $(this).val();
                                var title = $("#milestone").find("option[value='" + value + "']").text();
                                var info = value.split(",");
                                var level = info[0];
                                var exp = info[1];

                                updateSkills(exp, title, level);
                            });

                            $("map").each(function (col) {
                                var mapname = $(this).attr("name");
                                var img = $("img[usemap='#" + mapname + "']");

                                $(this).find("area").each(function (order) {
                                    switch (order) {
                                        case 0:
                                            $(this).mouseenter(function () {
                                                img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-up.png");
                                            }).mouseleave(function () {
                                                    if (currentsort.col == col) {
                                                        switch (currentsort.order) {
                                                            case 0:
                                                                img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-up.png");
                                                                break;
                                                            case 1:
                                                                img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-down.png");
                                                                break;
                                                        }
                                                    } else {
                                                        img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown.png");
                                                    }
                                                });
                                            break;
                                        case 1:
                                            $(this).mouseenter(function () {
                                                img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-down.png");
                                            }).mouseleave(function () {
                                                    if (currentsort.col == col) {
                                                        switch (currentsort.order) {
                                                            case 0:
                                                                img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-up.png");
                                                                break;
                                                            case 1:
                                                                img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown-down.png");
                                                                break;
                                                        }
                                                    } else {
                                                        img.attr("src", "<?php echo $dbf->basefilepath; ?>images/updown.png");
                                                    }
                                                });
                                            break;
                                    }
                                });
                            });
                        });

                        function updateSkills(experience, title, level) {
                            changeGageTitle(title);
                            sort(0, 0);

                            if (title == "Max Cape" || title == "Completionist Cape") {
                                level = 99;
                            } else {
                                level = parseInt(level, 10);
                            }

                            experience = parseInt(experience, 10);
                            var totalexp = 0;
                            var totallevel = 0;

                            $(".skill").each(function () {
                                $(this).find(".meter-wrap").removeClass("hide");
                                var userexp = $(this).attr("data-exp"), userlevel = $(this).attr("data-level"), skillnumber = $(this).attr("data-number"), skillname = $(this).attr("data-skill");
                                userexp = parseInt(userexp, 10);
                                userlevel = parseInt(userlevel, 10);
                                var percentage = 0, expremaining = 0;

                                if (userexp != 0) {
                                    if (skillnumber != 25 && skillnumber != 27) {
                                        if (userexp >= experience) {
                                            percentage = 100;
                                            totalexp += experience;
                                            totallevel += level;
                                        } else {
                                            percentage = (( userexp / experience ) * 100).toFixed(2);
                                            expremaining = experience - userexp;

                                            totalexp += userexp;
                                            totallevel += userlevel;
                                        }
                                    } else if (skillnumber == 25) {
                                        if (title == "Completionist Cape") {
                                            if (userexp >= 8061865) {
                                                percentage = 100;
                                                totalexp += 8061865;
                                                totallevel += 120;
                                            } else {
                                                percentage = (( userexp / 8061865 ) * 100).toFixed(2);
                                                expremaining = 8061865 - userexp;

                                                totalexp += userexp;
                                                totallevel += userlevel;
                                            }
                                        } else {
                                            if (userexp >= experience) {
                                                percentage = 100;
                                                totalexp += experience;
                                                totallevel += level;
                                            } else {
                                                percentage = (( userexp / experience ) * 100).toFixed(2);
                                                expremaining = experience - userexp;

                                                totalexp += userexp;
                                                totallevel += userlevel;
                                            }
                                        }
                                    }  else {
                                        if (title == "Completionist Cape") {
                                            if (userexp >= 104273166) {
                                                percentage = 100;
                                                totalexp += 104273166;
                                                totallevel += 120;
                                            } else {
                                                percentage = (( userexp / 104273166 ) * 100).toFixed(2);
                                                expremaining = 104273166 - userexp;

                                                totalexp += userexp;
                                                totallevel += userlevel;
                                            }
                                        } else {
                                            if (userexp >= experience) {
                                                percentage = 100;
                                                totalexp += experience;
                                                totallevel += level;
                                            } else {
                                                percentage = (( userexp / experience ) * 100).toFixed(2);
                                                expremaining = experience - userexp;

                                                totalexp += userexp;
                                                totallevel += userlevel;
                                            }
                                        }
                                    }

                                    $(this).attr("data-percentage", percentage);
                                    $(this).attr("data-remaining", expremaining);
                                    $(this).find(".meter-text-percentage").text(percentage + "%");
                                    $(this).find(".meter-text-remaining").text(delimitNumbers(expremaining));
                                    $(this).find(".meter-value").animate({
                                        width: percentage + "%"
                                    }, 350);
                                } else {
                                    totallevel += 1;
                                }
                            });

                            var totalposexp = 0, totalposlevel = 0;

                            if (title == "Completionist Cape") {
                                totalposexp = (experience * 25) + 104273166;
                                totalposlevel = (99 * 25) + 120;
                            } else {
                                totalposlevel = (level * 26);
                                totalposexp = experience * 26;
                            }

                            g.refresh(((totalexp / (totalposexp)) * 100).toFixed(2));

                            $("#goal-total-level").text(delimitNumbers(totallevel) + "/" + delimitNumbers(totalposlevel));
                            $("#goal-xp-remaining").text(delimitNumbers(totalposexp - totalexp));
                        }

                        function changeGageTitle(text) {
                            $("#gauge").find("tspan").each(function () {
                                if ($(this).text() == g.config.title) {
                                    $(this).text(text);
                                    g.config.title = text;
                                }
                            });
                        }


                        function toggleBox(skill, experience) {
                            var skillSelector = $('#' + skill);
                            var box = $('#' + skill + '-box');
                            var dir = "up";

                            if (!skillSelector.hasClass("skill-selected")) {
                                skillSelector.addClass("skill-selected");
                                dir = "down";
                            }

                            if ($.trim(box.text()).length == 0) {
                                $.post("createTable.php", {
                                    skill: skill,
                                    experience: experience
                                }, function (data) {
                                    box.empty().append(data);
                                    var height = box.show().height();
                                    box.hide();
                                    if (height > 200) {
                                        box.css("height", "200px");
                                        box.css("overflow-y", "scroll");
                                    }
                                    box.slideToggle(300, function () {
                                        if (dir == "up") {
                                            skillSelector.removeClass("skill-selected");
                                        }
                                    });
                                });
                            } else {
                                box.slideToggle(300, function () {
                                    if (box.height() > 200) {
                                        box.css("height", "200px");
                                        box.css("overflow-y", "scroll");
                                    }

                                    if (dir == "up") {
                                        skillSelector.removeClass("skill-selected");
                                    }
                                });
                            }

                        }

                        function delimitNumbers(str) {
                            return (str + "").replace(/(\d)(?=(\d{3})+(\.\d+|)\b)/g, "$1,");
                        }

                        function changeDesc(str) {
                            if (str == "") {
                                $("#description").empty().append("<h2>Badges</h2><p>Hover over a badge to see the description.</p>");
                            } else {
                                $("#description").empty().append(str);
                            }

                        }

                        function parseStr(str) {
                            str = str.replace(/ /g, '+');
                            return str;
                        }
                        <?php
                               $gains = $dbf->getAllAssocResults(" SELECT *
                                                                    FROM skills s
                                                                    LEFT OUTER JOIN (
                                                                        SELECT cpd.*, cp.UserID, cp.Time
                                                                        FROM checkpointdata cpd
                                                                        JOIN checkpoints cp
                                                                            ON cp.CheckpointID = cpd.CheckpointID
                                                                        JOIN users u
                                                                            ON u.UserID = cp.UserID
                                                                            AND u.RSN = '$safeUsername'
                                                                        WHERE TIME = (
                                                                            SELECT MAX(Time)
                                                                            FROM checkpoints
                                                                            WHERE UserID = u.UserID
                                                                        )
                                                                    ) cd
                                                                        ON s.SkillID = cd.SkillID
                                                                    WHERE cd.UserID IS NOT NULL
                                                                    ");

                                $date = $dbf->queryToText("SELECT Time
                                                            FROM checkpoints
                                                            WHERE Time >= (
                                                               SELECT MAX(DATE(Time))
                                                               FROM checkpoints
                                                               WHERE UserID = (
                                                                   SELECT UserID
                                                                   FROM users
                                                                   WHERE RSN = '$safeUsername'
                                                                   LIMIT 1
                                                               )
                                                            )");

                                if(count($gains) > 0) {
                                $haschart = true;
                        ?>
                        $(function () {
                            var chart;
                            $(document).ready(function () {
                                chart = new Highcharts.Chart({
                                    chart: {
                                        renderTo: 'chart-container',
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Experience Gains'
                                    },
                                    subtitle: {
                                        text: 'Since <?php echo date('M d Y', strtotime($date)); ?> (<a href="javascript:hideDg();">Hide DG</a>)'
                                    },
                                    xAxis: {
                                        categories: [
                                            'Attack',
                                            'Defence',
                                            'Strength',
                                            'Constitution',
                                            'Ranged',
                                            'Prayer',
                                            'Magic',
                                            'Cooking',
                                            'Woodcutting',
                                            'Fletching',
                                            'Fishing',
                                            'Firemaking',
                                            'Crafting',
                                            'Smithing',
                                            'Mining',
                                            'Herblore',
                                            'Agility',
                                            'Thieving',
                                            'Slayer',
                                            'Farming',
                                            'Runecraft',
                                            'Hunter',
                                            'Construction',
                                            'Summoning',
                                            'Dungeoneering',
                                            'Divination',
                                            'Invention'
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
                                                this.x + ': ' + delimitNumbers(this.y) + " exp";
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
                                            name: '<?php echo $username; ?>',
                                            data: [
                                                <?php
                                                    for ($i = 1; $i < count($gains); $i++) {
                                                        if($i != count($gains) - 1) {
                                                            echo ($experience[$i] - $gains[$i]['Experience']) . ", ";
                                                        } else {
                                                            echo ($experience[$i] - $gains[$i]['Experience']);
                                                        }
                                                    }
                                                ?>
                                            ]
                                        }
                                    ]
                                });
                            });
                        });

                        function hideDg() {
                            $('#chart-container').empty();
                            $(function () {
                                var chart;
                                $(document).ready(function () {
                                    chart = new Highcharts.Chart({
                                        chart: {
                                            renderTo: 'chart-container',
                                            type: 'column'
                                        },
                                        title: {
                                            text: 'Experience Gains'
                                        },
                                        subtitle: {
                                            text: 'Since <?php echo date('M d Y', strtotime($date)); ?> (<a href="javascript:showDg();">Show DG</a>)'
                                        },
                                        xAxis: {
                                            categories: [
                                                'Attack',
                                                'Defence',
                                                'Strength',
                                                'Constitution',
                                                'Ranged',
                                                'Prayer',
                                                'Magic',
                                                'Cooking',
                                                'Woodcutting',
                                                'Fletching',
                                                'Fishing',
                                                'Firemaking',
                                                'Crafting',
                                                'Smithing',
                                                'Mining',
                                                'Herblore',
                                                'Agility',
                                                'Thieving',
                                                'Slayer',
                                                'Farming',
                                                'Runecraft',
                                                'Hunter',
                                                'Construction',
                                                'Summoning',
                                                'Divination'
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
                                                    this.x + ': ' + delimitNumbers(this.y) + " exp";
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
                                                name: '<?php echo $username; ?>',
                                                data: [
                                                    <?php
                                                        for ($i = 1; $i < count($gains); $i++) {
                                                            if($i != count($gains) - 1) {
                                                                if($skillnames[$i] != "Dungeoneering") {
                                                                    echo ($experience[$i] - $gains[$i]['Experience']) . ", ";
                                                                }
                                                            } else {
                                                                echo ($experience[$i] - $gains[$i]['Experience']);
                                                            }
                                                        }
                                                    ?>
                                                ]
                                            }
                                        ]
                                    });
                                });
                            });
                        }

                        function showDg() {
                            $('#chart-container').empty();
                            $(function () {
                                var chart;
                                $(document).ready(function () {
                                    chart = new Highcharts.Chart({
                                        chart: {
                                            renderTo: 'chart-container',
                                            type: 'column'
                                        },
                                        title: {
                                            text: 'Experience Gains'
                                        },
                                        subtitle: {
                                            text: 'Since <?php echo date('M d Y', strtotime($date)); ?> (<a href="javascript:hideDg();">Hide Dg</a>)'
                                        },
                                        xAxis: {
                                            categories: [
                                                'Attack',
                                                'Defence',
                                                'Strength',
                                                'Constitution',
                                                'Ranged',
                                                'Prayer',
                                                'Magic',
                                                'Cooking',
                                                'Woodcutting',
                                                'Fletching',
                                                'Fishing',
                                                'Firemaking',
                                                'Crafting',
                                                'Smithing',
                                                'Mining',
                                                'Herblore',
                                                'Agility',
                                                'Thieving',
                                                'Slayer',
                                                'Farming',
                                                'Runecraft',
                                                'Hunter',
                                                'Construction',
                                                'Summoning',
                                                'Dungeoneering',
                                                'Divination',
                                                'Invention'
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
                                                    this.x + ': ' + delimitNumbers(this.y) + " exp";
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
                                                name: '<?php echo $username; ?>',
                                                data: [
                                                    <?php
                                                        for ($i = 1; $i < count($gains); $i++) {
                                                            if($i != count($gains) - 1) {
                                                                echo ($experience[$i] - $gains[$i]['Experience']) . ", ";
                                                            } else {
                                                                echo ($experience[$i] - $gains[$i]['Experience']);
                                                            }
                                                        }
                                                    ?>
                                                ]
                                            }
                                        ]
                                    });
                                });
                            });
                        }
                        <?php
                        } else {
                        $haschart = false;
                        }
                        ?>
                    </script>
                </head>

                <body>
                    <?php require_once("../masthead.php"); ?>

                    <div id="leftbar">
                        <div id="topbar" style="overflow:hidden;">
                            <a title="Animations WILL be shown while sorting." style="color:green; float:left;" id="toggleAnims" href="javascript:void(0)" onclick="toggleAnims();"><i class="icon-ok"></i>Animations</a> <a
                                id="toggleComplete"
                                href="javascript:hideDone();">Hide Complete</a>
                        </div>
                        <div class="user-info">
                            <p>RSN</p>

                            <h1><?php echo $username; ?></h1>

                            <p>Experience</p>

                            <h1><?php echo number_format( $experience[ 0 ] ); ?></h1>

                            <p>Combat Level</p>

                            <h1><?php echo $combatlevel; ?></h1>

                            <p>Milestone</p>

                            <h1><?php echo $milestone; ?></h1>

                            <p>Rank</p>

                            <h1><a target="_blank" href="http://services.runescape.com/m=hiscore/a=161/ranking?category_type=0&table=0&rank=<?php echo $ranks[ 0 ]; ?>"><?php echo number_format( $ranks[ 0 ] ); ?></a></h1>

                        </div>

                        <div class="advert">
                            <?php $dbf->ad("5228316002"); ?>
                        </div>

                        <form style="text-align:center;" onsubmit="(function(e) {e.preventDefault(); location.href='/calc/compare/<?php echo urlencode($username); ?>/' + parseStr($('#comparetb').val()); })(event)">
                            <input type="text" style="width:115px;" placeholder="Compare RSN" id="comparetb"/>
                            <button type="submit">Compare</button>
                        </form>

                        <div class="capes-container">
                            <div class="comp">
                                <div class="dropdown dropdown-dark">
                                    <select id="milestone" class="dropdown-select">
                                        <?php
                                            if ( gettype( $milestone ) != "string" && $milestone >= 0 && $milestone < 90 ) {
                                                for ( $i = $milestone + 10; $i <= 90; $i += 10 ) {
                                                    ?>
                                                    <option value="<?php echo $i; ?>,<?php echo $rsf->getExperience( $i ); ?>"><?php echo $i; ?>s Cape</option>
                                                <?php
                                                }
                                            }

                                            if ( $milestone !== "Completionist" && $milestone !== "Max" ) {
                                                ?>
                                                <option selected="selected" value="Max,13034431">Max Cape</option>
                                            <?php
                                            }
                                        ?>

                                        <option <?php echo $milestone === "Completionist" ? "selected='selected'" : ""; ?> value="Completionist,13034431">Completionist Cape</option>
                                        <option value="120,<?php echo $rsf->getExperience(120); ?>">120 Virtual Level</option>
                                        <option value="126,200000000">200m xp</option>
                                    </select>
                                </div>

                                <div id="gauge">

                                </div>

                                <div class="info">
                                    <p>Total Level</p>

                                    <h1 id="goal-total-level"><?php
                                            if ( $milestone === "Completionist" || $milestone === "Max" ) {
                                                echo number_format( $levels[ 0 ] ) . "/2,715";
                                            } else {
                                                if ( $levels[ 25 ] > 99 ) {
                                                    echo number_format( $levels[ 0 ] - ( $levels[ 25 ] - 99 ) ) . "/2,673";
                                                } else {
                                                    echo number_format( $levels[ 0 ] ) . "/2,673";
                                                }
                                            }
                                        ?></h1>

                                    <p>Exp Remaining</p>

                                    <h1 id="goal-xp-remaining"><?php echo $milestone === "Completionist" || $milestone === "Max" ? number_format( $xpToCompCape ) : number_format( $xpToMaxCape ); ?></h1>
                                </div>
                            </div>
                        </div>

                        <div id="rewards">

                            <div id="description">
                                <h2>Badges</h2>

                                <p>Hover over a badge to see the description.</p>
                            </div>
                            <div id="medals">
                                <?php printBadges( $skillnames, $experience, $levels ); ?>
                            </div>
                        </div>
                    </div>

                    <div id="mainContent">
                        <?php
                            if ( $response == "outdated" ) {
                                ?>
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Name Change: </strong>'<?php echo $username; ?>' is not in the highscores, but was within the last month. Shown below are their last known stats.</p>
                                </div>
                            <?php
                            }
                        ?>
                        <div id="skilllist">
                            <div class="meter-wrap header-row">
                                <div class="meter-value" style="width:0;">
                                    <div class="meter-text-icon">
                                        <img id="sort-0" src="<?php echo $dbf->basefilepath; ?>images/updown-up.png" alt="" usemap="#skillmap8">
                                        <map name="skillmap8">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(0, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(0, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-skill">
                                        <b>Skill</b>
                                        <img id="sort-1" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap0">
                                        <map name="skillmap0">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(1, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(1, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-rank">
                                        <b>Rank</b>
                                        <img id="sort-2" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap1">
                                        <map name="skillmap1">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(2, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(2, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-level">
                                        <b>Lvl</b>
                                        <img id="sort-3" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap2">
                                        <map name="skillmap2">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(3, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(3, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-vlevel">
                                        <span class="virtual" title="Virtual Level"><b>VLvl</b></span>
                                        <img id="sort-4" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap3">
                                        <map name="skillmap3">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(4, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(4, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-exp">
                                        <b>Experience</b>
                                        <img id="sort-5" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap4">
                                        <map name="skillmap4">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(5, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(5, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-tnl">
                                        <span class="virtual" title="Experience until next level"><b>TNL</b></span>
                                        <img id="sort-6" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap5">
                                        <map name="skillmap5">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(6, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(6, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-remaining">
                                        <span class="virtual" title="Experience until Complete"><b>To Go</b></span>
                                        <img id="sort-7" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap6">
                                        <map name="skillmap6">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(7, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(7, 1);">
                                        </map>
                                    </div>
                                    <div class="meter-text-percentage">
                                        <span class="virtual" title="Percentage of maximum level you are complete"><b>%</b></span>
                                        <img id="sort-8" src="<?php echo $dbf->basefilepath; ?>images/updown.png" alt="" usemap="#skillmap7">
                                        <map name="skillmap7">
                                            <area shape="rect" coords="0,0,11,7" title="Sort Ascending" href="javascript:void(0)" onclick="sort(8, 0);">
                                            <area shape="rect" coords="1,7,11,15" title="Sort Descending" href="javascript:void(0)" onclick="sort(8, 1);">
                                        </map>
                                    </div>

                                </div>
                            </div>
                            <?
                                foreach ( $skills as $skill ) {
                                    $i = $skill[ 'Number' ];

                                    if ( $i != 0 && $i != 27 ) {
                                        if ( $skill[ 'Name' ] != "Dungeoneering" ) {
                                            $width = number_format( ( $experience[ $i ] / $level99 ) * 100, 2 );
                                        } else {
                                            if($levels[$i] < 99) {
                                                $width = number_format(($experience[$i] / $level99) * 100, 2);
                                            } else {
                                                $width = number_format( ( $experience[ $i ] / $level120 ) * 100, 2 );
                                            }
//                                            if ( $milestone != "Max" && $milestone != "Completionist" ) {
//                                                $width = number_format( ( $experience[ $i ] / $level99 ) * 100, 2 );
//                                            } else {
//                                                $width = number_format( ( $experience[ $i ] / $level120 ) * 100, 2 );
//                                            }
                                        }

                                        $width = str_replace( ",", "", $width );

                                        if ( $width >= 100 ) {
                                            $width = 100;
                                        }

                                        if($width == 100 && $experience[$i] < $level99) {
                                            $width = 99.99;
                                        }

                                        if ( $width == 100 ) {
                                            $percentcolor = "#00BC00";
                                        } else {
                                            if ( $width >= 66 && $width < 100 ) {
                                                $percentcolor = "#A9D70B";
                                            } else {
                                                if ( $width < 66 && $width >= 33 ) {
                                                    $percentcolor = "#F9C802";
                                                } else {
                                                    $percentcolor = "#FF0000";
                                                }
                                            }
                                        }
                                        ?>
                                        <div class="skill"
                                             data-number="<?php echo $skill[ 'Number' ]; ?>"
                                             data-skill="<? echo $skillnames[ $i ]; ?>"
                                             data-rank="<?php echo $ranks[ $i ]; ?>"
                                             data-level="<?php echo $levels[ $i ]; ?>"
                                             data-vlvl="<?php echo $rsf->getLevel( $experience[ $i ] ); ?>"
                                             data-exp="<?php echo sprintf( '%09d', $experience[ $i ] ); ?>"
                                             data-tnl="<?php echo $rsf->getLevel( $experience[ $i ] ) != 126 ? sprintf( "%09d", ( $rsf->getExperience( ( $rsf->getLevel( $experience[ $i ] ) + 1 ) ) - $experience[ $i ] ) ) : 0; ?>"
                                             data-remaining="<?php echo $experienceRemaining[ $i ]; ?>"
                                             <?php if($i == 25) { ?> data-to120="<?php echo $level120 - $experience[$i]; ?>"  <?php } ?>
                                             data-percentage="<?php echo $width; ?>"
                                            >
                                            <div class="meter-wrap" id="<?php echo $skill[ 'Name' ] ?>" onclick="toggleBox('<?php echo $skill[ 'Name' ]; ?>', <?php echo $experience[ $i ]; ?>);">
                                                <div class="meter-value" style="width: <?php echo $width > 100 ? 100 : $width; ?>%;">
                                                    <img class="meter-text-icon" src="<?php echo $dbf->basefilepath; ?>images/<?php echo strtolower( $skill[ 'Name' ] ); ?>.png">
                                                    <div class="meter-text-skill"><?php echo $skill[ 'Name' ]; ?></div>
                                                    <div class="meter-text-rank"><?php if ( $ranks[ $i ] != 0 ) { ?><a target="_blank"
                                                                                                                       href="http://services.runescape.com/m=hiscore/a=161/ranking?category_type=0&table=<?php echo $i; ?>&rank=<?php echo $ranks[ $i ]; ?>"><?php echo number_format( $ranks[ $i ] ); ?></a><?php } else { ?>?<?php } ?>
                                                    </div>
                                                    <div class="meter-text-level"><?php echo $experience[ $i ] > 0 ? $levels[ $i ] : "?"; ?></div>
                                                    <div class="meter-text-vlevel"><?php echo $experience[ $i ] > 0 ? $rsf->getLevel( $experience[ $i ] ) : "?"; ?></div>
                                                    <div class="meter-text-exp"><?php echo $experience[ $i ] > 0 ? number_format( $experience[ $i ] ) : "?"; ?></div>
                                                    <div
                                                        class="meter-text-tnl"><?php echo $rsf->getLevel( $experience[ $i ] ) != 126 ? $experience[ $i ] > 0 ? number_format( $rsf->getExperience( ( $rsf->getLevel( $experience[ $i ] ) + 1 ) ) - $experience[ $i ] ) : "?" : 0; ?></div>
                                                    <div class="meter-text-remaining"><?php echo $experience[ $i ] > 0 ? number_format( $experienceRemaining[ $i ] ) : "?"; ?></div>
                                                    <div class="meter-text-percentage" style="color:<?php echo $percentcolor; ?>;"><?php echo $width > 0 ? $width . "%" : "?"; ?></div>
                                                </div>
                                            </div>
                                            <div class="toggleBox" id="<?php echo $skillnames[ $i ]; ?>-box"></div>
                                        </div>
                                    <?php
                                    } elseif($i == 27) {
                                        if($levels[$i] < 99) {
                                            $width = number_format(($experience[$i] / $rsf->getExperience(99, "invention")) * 100, 2);
                                        } else {
                                            $width = number_format(($experience[$i] / $rsf->getExperience(120, "invention")) * 100, 2);
                                        }
                                        ?>
                                        <div class="skill"
                                             data-number="<?php echo $skill[ 'Number' ]; ?>"
                                             data-skill="<? echo $skillnames[ $i ]; ?>"
                                             data-rank="<?php echo $ranks[ $i ]; ?>"
                                             data-level="<?php echo $levels[ $i ]; ?>"
                                             data-vlvl="<?php echo $rsf->getLevel( $experience[ $i ] , "invention" ); ?>"
                                             data-exp="<?php echo sprintf( '%09d', $experience[ $i ] ); ?>"
                                             data-tnl="<?php echo $rsf->getLevel( $experience[ $i ], "invention" ) != 150 ? sprintf( "%09d", $rsf->remainingXP($experience[$i], null, "invention") ) : 0; ?>"
                                             data-remaining="<?php echo $experienceRemaining[ $i ]; ?>"
                                             data-percentage="<?php echo $width; ?>"
                                            >
                                            <div class="meter-wrap" id="<?php echo $skill[ 'Name' ] ?>" onclick="toggleBox('<?php echo $skill[ 'Name' ]; ?>', <?php echo $experience[ $i ]; ?>);">
                                                <div class="meter-value" style="width: <?php echo $width > 100 ? 100 : $width; ?>%;">
                                                    <img class="meter-text-icon" src="<?php echo $dbf->basefilepath; ?>images/<?php echo strtolower( $skill[ 'Name' ] ); ?>.png">
                                                    <div class="meter-text-skill"><?php echo $skill[ 'Name' ]; ?></div>
                                                    <div class="meter-text-rank"><?php if ( $ranks[ $i ] != 0 ) { ?><a target="_blank"
                                                                                                                       href="http://services.runescape.com/m=hiscore/a=161/ranking?category_type=0&table=<?php echo $i; ?>&rank=<?php echo $ranks[ $i ]; ?>"><?php echo number_format( $ranks[ $i ] ); ?></a><?php } else { ?>?<?php } ?>
                                                    </div>
                                                    <div class="meter-text-level"><?php echo $experience[ $i ] > 0 ? $levels[ $i ] : "?"; ?></div>
                                                    <div class="meter-text-vlevel"><?php echo $experience[ $i ] > 0 ? $rsf->getLevel( $experience[ $i ], "invention" ) : "?"; ?></div>
                                                    <div class="meter-text-exp"><?php echo $experience[ $i ] > 0 ? number_format( $experience[ $i ] ) : "?"; ?></div>
                                                    <div
                                                        class="meter-text-tnl"><?php echo $rsf->getLevel( $experience[ $i ] ) != 126 ? $experience[ $i ] > 0 ? number_format( $rsf->remainingXP($experience[$i], null, "invention"), 0) : "?" : 0; ?></div>
                                                    <div class="meter-text-remaining"><?php echo $experience[ $i ] > 0 ? number_format( $experienceRemaining[ $i ] ) : "?"; ?></div>
                                                    <div class="meter-text-percentage" style="color:<?php echo $percentcolor; ?>;"><?php echo $width > 0 ? $width . "%" : "?"; ?></div>
                                                </div>
                                            </div>
                                            <div class="toggleBox" id="<?php echo $skillnames[ $i ]; ?>-box"></div>
                                        </div>
                            <?php
                                    }
                                }
                            ?>
                        </div>

                        <?php
                            if ( $response == "cached" ) {
                                $lastlookup = $dbf->queryToText( "SELECT TimeFetched FROM apicache WHERE RSN='$safeUsername'" );
                                $lastlookup = strtotime( $lastlookup );

                                $timeremaining = time() - $lastlookup;
                                ?>
                                <div class="alert-message info">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Cached Stats: </strong>The stats shown here are cached. <?php echo time_elapsed_string( $lastlookup ); ?></p>
                                </div>
                            <?php
                            }
                        ?>
                    </div>

                    <div id="chart-container"></div>

                    <div class="ad <?php echo $haschart ? "haschart" : ""; ?>">
                        <?php $dbf->ad("3119017200"); ?>
                    </div>

                    <div id="footer">
                        <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
                    </div>

                    <script>
                        var chart = $("#chart-container");
                        if(chart.children().length == 0) {
                            chart.css("height", "0px");
                        }
                    </script>
                </body>
            </html>

            <?php
            $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        } else {
            echo "Cannot find or connect to database.";
            $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        }
    } else {
        ?>
        <script>
            document.location = "/nr";
        </script>
        <?php
        die();
    }
