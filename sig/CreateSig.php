<?php
    set_time_limit( 0 );

    function getStats( $rsn ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, "http://hiscore.runescape.com/index_lite.ws?player=" . $rsn );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

        $data = curl_exec( $ch );
        curl_close( $ch );

        return $data;
    }

    function getRGB($hex) {
        $rgb = str_split($hex, 2);

        $r = hexdec($rgb[0]);
        $g = hexdec($rgb[1]);
        $b = hexdec($rgb[2]);

        return array($r, $g, $b);
    }

    function createImg( $rsn, $goal, $hiscoredata ) {
        require_once( "../rsfunctions.php" );
        require_once( "colors.php" );
        $rsf = new rsfunctions;
        require_once("../dbfunctions.php");
        $dbf = new dbfunctions;

        $dbf->connectToDatabase($dbf->database);
        $safersn = mysql_real_escape_string($rsn);
        $bgcolor = $dbf->queryToText("SELECT SigBGColor FROM users WHERE RSN='$safersn'");
        $blackcolor = $dbf->queryToText("SELECT SigTxtColor FROM users WHERE RSN='$safersn'");

        if($bgcolor == "") {
            $bgcolor = "E0E0E0";
        }

        if($blackcolor == "") {
            $blackcolor = "000000";
        }


        $colorpallete = $ms[ $goal ];
        $wasmax       = false;
        $wascomp      = false;
        $type         = "";

        if ( $goal == "max" ) {
            $goal   = 99;
            $wasmax = true;
        }
        else if ( $goal == "comp-regular" || $goal == "comp-trimmed" ) {
            $type    = explode( "-", $goal );
            $type    = $type[ 1 ];
            $goal    = 99;
            $wascomp = true;
        }

        $maxxp    = $wascomp ? ( $rsf->getExperience( $goal ) * 25 ) + $rsf->getExperience( 120 ) : $rsf->getExperience( $goal ) * 26;
        $goalxp   = $rsf->getExperience( $goal );
        $maxtotal = $wascomp ? ( $goal * 25 ) + 120 : $goal * 26;

        $capexpTotal = 0;
        $levelTotal  = 0;

        $ranks      = array();
        $levels     = array();
        $experience = array();

        $data = preg_split( "/\s+/", $hiscoredata );

        foreach ( $data as $i => $skill ) {
            if ( $i <= 26 ) {
                $thisSkill = explode( ",", $skill );

                if ( $thisSkill[ 0 ] == -1 ) {
                    $thisSkill[ 0 ] = 0;
                }
                if ( $thisSkill[ 1 ] == -1 ) {
                    $thisSkill[ 1 ] = 0;
                }
                if ( $thisSkill[ 2 ] == -1 ) {
                    $thisSkill[ 2 ] = 0;
                }

                $ranks[ ]      = $thisSkill[ 0 ];
                $levels[ ]     = $thisSkill[ 1 ];
                $experience[ ] = $thisSkill[ 2 ];
            }
        }


        for ( $i = 1; $i <= 26; $i++ ) {
            if ( $i != 25 ) {
                if ( $experience[ $i ] < $goalxp ) {
                    $capexpTotal += $experience[ $i ];
                    $levelTotal += $levels[ $i ];
                }
                else {
                    $capexpTotal += $goalxp;
                    $levelTotal += $goal;
                }
            }
            else {
                if ( $wascomp ) {
                    if ( $experience[ $i ] < $rsf->getExperience( 120 ) ) {
                        $capexpTotal += $experience[ $i ];
                        $levelTotal += $levels[ $i ];
                    }
                    else {
                        $capexpTotal += $rsf->getExperience( 120 );
                        $levelTotal += 120;
                    }
                }
                else {
                    if ( $experience[ $i ] < $goalxp ) {
                        $capexpTotal += $experience[ $i ];
                        $levelTotal += $levels[ $i ];
                    }
                    else {
                        $capexpTotal += $goalxp;
                        $levelTotal += $goal;
                    }
                }
            }
        }

        if ( $wascomp || $wasmax ) {
            if ( $wasmax ) {
                $capeimgfilepath = "img/max.png";
            }
            else {
                $capeimgfilepath = "img/comp-$type.png";
            }
        }
        else {
            $capeimgfilepath = "img/$goal.png";
        }

        $capeicon  = imagecreatefrompng( $capeimgfilepath );
        $copyright = imagecreatefrompng( "img/copyright.png" );
        $capex     = imagesx( $capeicon );
        $capey     = imagesy( $capeicon );
        $image     = imagecreatetruecolor( 1200, 235 );
        $image2    = imagecreatetruecolor( 300, 58 );

        $outercolor = imagecolorallocate( $image, $colorpallete->outer->r, $colorpallete->outer->g, $colorpallete->outer->b );
        $innercolor = imagecolorallocate( $image, $colorpallete->inner->r, $colorpallete->inner->g, $colorpallete->inner->b );
        $ringcolor  = imagecolorallocate( $image, $colorpallete->ring->r, $colorpallete->ring->g, $colorpallete->ring->b );

        $bgarr = getRGB($bgcolor);
        $blackarr = getRGB($blackcolor);

        $bg         = imagecolorallocate( $image, $bgarr[0], $bgarr[1], $bgarr[2] );
        $black      = imagecolorallocate( $image, $blackarr[0], $blackarr[1], $blackarr[2] );



        imagefill( $image, 0, 0, $bg );

        $font = "/var/www/sig/OpenSans-CondLight.ttf";

        $capescale = ( 140 / $capey );

        $iconh   = $capey * $capescale;
        $iconw   = $capex * $capescale;
        $percent = number_format( ( $capexpTotal / $maxxp ) * 100, 0 );

        //Surrounding Circle
        imagefilledellipse( $image, 116, 116, 224, 224, $outercolor );

        //Background Circle
        imagefilledellipse( $image, 116, 116, 204, 204, $innercolor );

        //Percentage Arc
        imagesetthickness( $image, 8 );
        imagearc( $image, 116, 116, 200, 200, 90, $percent < 100 ? ( 360 * ( $percent / 100 ) ) + 90 : 449, $ringcolor );

        //Cape Icon
        imagecopyresampled( $image, $capeicon, ( ( ( 204 - $iconw ) / 2 ) + 16 ), 46, 0, 0, $iconw, $iconh, $capex, $capey );

        //Username Text
        imagettftext( $image, 60, 0, 235, 92, $black, $font, $rsn );

        //Total Level Text
        imagettftext( $image, 52, 0, 780, 92, $black, $font, number_format( $levelTotal ) . "/" . number_format( $maxtotal ) );

        $rsnbbox  = imagettfbbox( 60, 0, $font, $rsn );
        $rsnright = $rsnbbox[ 4 ] + 235;

        $ttllvlbbox  = imagettfbbox( 52, 0, $font, number_format( $levelTotal ) . "/" . number_format( $maxtotal ) );
        $ttllvlleft  = $ttllvlbbox[ 0 ] + 780;
        $ttllvlright = $ttllvlbbox[ 4 ] + 780;

        imagesetthickness( $image, 4 );
        imageline( $image, $rsnright + 20, 92, $ttllvlleft - 13, 92, $black );
        imageline( $image, $ttllvlright + 20, 92, 1150, 92, $black );

        //Seperator Lines

        imageline( $image, 228, 104, 1170, 104, $black );
        imageline( $image, 229, 116, 1100, 116, $black );
        imageline( $image, 228, 128, 1050, 128, $black );

        //Xp Left Text
        if ( $capexpTotal < $maxxp ) {
            $str = number_format( $maxxp - $capexpTotal ) . " XP left";
        }
        else {
            $str = "Goal Complete!";
        }

        imagettftext( $image, 58, 0, 235, 200, $black, $font, $str );

        $strbbox  = imagettfbbox( 58, 0, $font, $str );
        $strright = $strbbox[ 4 ] + 235;

        imageline( $image, $strright + 20, 140, 1010, 140, $black );

        imagecopy( $image, $copyright, 1200 - imagesx( $copyright ), 235 - imagesy( $copyright ), 0, 0, imagesx( $copyright ), imagesy( $copyright ) );


        imagecopyresampled( $image2, $image, 0, 0, 0, 0, 300, 58, 1200, 235 );

        $rsn = strtolower( $rsn );
        header( "Content-Type: image/png" );
        if ( !$wasmax && !$wascomp ) {
            imagepng( $image2, "sigs/$rsn-$goal.png" );
        }
        else {
            if ( $wasmax ) {
                imagepng( $image2, "sigs/$rsn-max.png" );
            }
            else {
                imagepng( $image2, "sigs/$rsn-comp-$type.png" );
            }
        }

        imagepng( $image2 );
        imagedestroy( $image );
        imagedestroy( $image2 );
        imagedestroy( $capeicon );
        imagedestroy( $copyright );
    }

    function createunkownImg( $rsn, $goal ) {
        require_once( "colors.php" );

        $colorpallete = $ms[ $goal ];
        $wasmax       = false;
        $wascomp      = false;
        $type         = "";

        if ( $goal == "max" ) {
            $goal   = 99;
            $wasmax = true;
        }
        else if ( $goal == "comp-regular" || $goal == "comp-trimmed" ) {
            $type    = explode( "-", $goal );
            $type    = $type[ 1 ];
            $goal    = 99;
            $wascomp = true;
        }

        $maxtotal = $wascomp ? ( $goal * 25 ) + 120 : $goal * 26;

        if ( $wascomp || $wasmax ) {
            if ( $wasmax ) {
                $capeimgfilepath = "img/max.png";
            }
            else {
                $capeimgfilepath = "img/comp-$type.png";
            }
        }
        else {
            $capeimgfilepath = "img/$goal.png";
        }

        $capeicon  = imagecreatefrompng( $capeimgfilepath );
        $copyright = imagecreatefrompng( "img/copyright.png" );
        $capex     = imagesx( $capeicon );
        $capey     = imagesy( $capeicon );
        $image     = imagecreatetruecolor( 1200, 235 );
        $image2    = imagecreatetruecolor( 300, 58 );

        $outercolor = imagecolorallocate( $image, $colorpallete->outer->r, $colorpallete->outer->g, $colorpallete->outer->b );
        $innercolor = imagecolorallocate( $image, $colorpallete->inner->r, $colorpallete->inner->g, $colorpallete->inner->b );
        $ringcolor  = imagecolorallocate( $image, $colorpallete->ring->r, $colorpallete->ring->g, $colorpallete->ring->b );
        $bg         = imagecolorallocate( $image, 225, 225, 225 );
        $black      = imagecolorallocate( $image, 0, 0, 0 );
        imagefill( $image, 0, 0, $bg );

        $font = "/var/www/sig/OpenSans-CondLight.ttf";

        $capescale = ( 140 / $capey );

        $iconh   = $capey * $capescale;
        $iconw   = $capex * $capescale;
        $percent = 0;

        //Surrounding Circle
        imagefilledellipse( $image, 116, 116, 224, 224, $outercolor );

        //Background Circle
        imagefilledellipse( $image, 116, 116, 204, 204, $innercolor );

        //Percentage Arc
        imagesetthickness( $image, 8 );
        imagearc( $image, 116, 116, 200, 200, 90, $percent < 100 ? ( 360 * ( $percent / 100 ) ) + 90 : 449, $ringcolor );

        //Cape Icon
        imagecopyresampled( $image, $capeicon, ( ( ( 204 - $iconw ) / 2 ) + 16 ), 46, 0, 0, $iconw, $iconh, $capex, $capey );

        //Username Text
        imagettftext( $image, 60, 0, 235, 92, $black, $font, $rsn );

        //Total Level Text
        imagettftext( $image, 52, 0, 780, 92, $black, $font, $maxtotal < 1000 ? "???/" . number_format( $maxtotal ) : "????/" . number_format( $maxtotal ) );

        $rsnbbox  = imagettfbbox( 60, 0, $font, $rsn );
        $rsnright = $rsnbbox[ 4 ] + 235;

        $ttllvlbbox  = imagettfbbox( 52, 0, $font, $maxtotal < 1000 ? "???/" . number_format( $maxtotal ) : "????/" . number_format( $maxtotal ) );
        $ttllvlleft  = $ttllvlbbox[ 0 ] + 780;
        $ttllvlright = $ttllvlbbox[ 4 ] + 780;

        imageline( $image, $rsnright + 20, 92, $ttllvlleft - 13, 92, $black );
        imageline( $image, $ttllvlright + 20, 92, 1150, 92, $black );

        //Seperator Lines
        imagesetthickness( $image, 4 );
        imageline( $image, 228, 104, 1170, 104, $black );
        imageline( $image, 229, 116, 1100, 116, $black );
        imageline( $image, 228, 128, 1050, 128, $black );

        //Xp Left Text
        $str = "??? XP left";

        imagettftext( $image, 58, 0, 235, 200, $black, $font, $str );

        $strbbox  = imagettfbbox( 58, 0, $font, $str );
        $strright = $strbbox[ 4 ] + 235;

        imageline( $image, $strright + 20, 140, 1010, 140, $black );

        imagecopy( $image, $copyright, 1200 - imagesx( $copyright ), 235 - imagesy( $copyright ), 0, 0, imagesx( $copyright ), imagesy( $copyright ) );


        imagecopyresampled( $image2, $image, 0, 0, 0, 0, 300, 58, 1200, 235 );

        $rsn = strtolower( $rsn );
        header( "Content-Type: image/png" );
        if ( !$wasmax && !$wascomp ) {
            imagepng( $image2, "sigs/$rsn-$goal.png" );
        }
        else {
            if ( $wasmax ) {
                imagepng( $image2, "sigs/$rsn-max.png" );
            }
            else {
                imagepng( $image2, "sigs/$rsn-comp-$type.png" );
            }
        }

        imagepng( $image2 );
        imagedestroy( $image );
        imagedestroy( $image2 );
        imagedestroy( $capeicon );
        imagedestroy( $copyright );
    }


    $username  = urldecode($_GET[ 'rsn' ]);
    $levelgoal = strtolower( $_GET[ 'goal' ] );

    $valid = array( 10, 20, 30, 40, 50, 60, 70, 80, 90, "max", "comp-regular", "comp-trimmed" );
    if ( in_array( $levelgoal, $valid ) ) {
        $filename = strtolower($username);
        if ( !file_exists( "sigs/$filename-$levelgoal.png" ) ) {
            $stats = getStats( $username );
            if ( $stats != "" && !strstr( $stats, "<html>" ) ) {
                createImg( $username, $levelgoal, $stats );
            }
            else {
                createunkownImg( $username, $levelgoal );
            }
        }
        else {
            $seconds = strtotime( "now" ) - filemtime( "sigs/$filename-$levelgoal.png" );
            $minutes = $seconds / 60;
            $hours   = $minutes / 60;
            if ( $hours >= 6 ) {
                $stats = getStats( $username );
                if ( $stats != "" && !strstr( $stats, "<html>" ) ) {
                    createImg( $username, $levelgoal, $stats );
                }
                else {
                    createunkownImg( $username, $levelgoal );
                }
            }
            else {
                header( "Content-Type: image/png" );
                $image = imagecreatefrompng( "sigs/$filename-$levelgoal.png" );
                imagepng( $image );
                imagedestroy($image);
            }
        }
    }
    else {
        echo "invalid milestone";
    }