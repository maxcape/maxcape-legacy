<?
    require( "dbfunctions.php" );
    date_default_timezone_set( "America/Los_Angeles" );

    class rsfunctions extends dbfunctions {
        public $levels = Array(
            0, 83, 174, 276, 388, 512, 650, 801, 969, 1154, 1358, 1584, 1833, 2107, 2411, 2746, 3115, 3523, 3973, 4470, 5018, 5624, 6291, 7028, 7842, 8740, 9730, 10824, 12031, 13363, 14833, 16456, 18247, 20224, 22406,
            24815, 27473, 30408, 33648, 37224, 41171, 45529, 50339, 55649, 61512, 67983, 75127, 83014, 91721, 101333, 111945, 123660, 136594, 150872, 166636, 184040, 203254, 224466, 247886, 273742, 302288, 333804,
            368599, 407015, 449428, 496254, 547953, 605032, 668051, 737627, 814445, 899257, 992895, 1096278, 1210421, 1336443, 1475581, 1629200, 1798808, 1986068, 2192818, 2421087, 2673114, 2951373, 3258594, 3597792,
            3972294, 4385776, 4842295, 5346332, 5902831, 6517253, 7195629, 7944614, 8771558, 9684577, 10692629, 11805606, 13034431, 14391160, 15889109, 17542976, 19368992, 21385073, 23611006, 26068632, 28782069,
            31777943, 35085654, 38737661, 42769801, 47221641, 52136869, 57563718, 63555443, 70170840, 77474828, 85539082, 94442737, 104273167, 115126838, 127110260, 140341028, 154948977, 171077457, 188884740
        );

        public $inventionXPTable = array(0, 830, 1861, 2902, 3980, 5126, 6390, 7787, 9400, 11275, 13605, 16372, 19656, 23546, 28138, 33520, 39809,
            47109, 55535, 64802, 77190, 90811, 106221, 123573, 143025, 164742, 188893, 215651, 245196, 277713, 316311, 358547, 404634,
            454796, 509259, 568254, 632019, 700797, 774834, 854383, 946227, 1044569, 1149696, 1261903, 1381488, 1508756, 1644015, 1787581,
            1939773, 2100917, 2283490, 2476369, 2679907, 2894505, 3120508, 3358307, 3608290, 3870846, 4146374, 4435275, 4758122, 5096111,
            5449685, 5819299, 6205407, 6608473, 7028694, 7467354, 7924122, 8399751, 8925664, 9472665, 10041285, 10632061, 11245538, 11882262,
            12542789, 13227679, 13937496, 14672812, 15478994, 16313404, 17176661, 18069395, 18992239, 19945843, 20930821, 21947856, 22997593,
            24080695, 25259906, 26475754, 27728955, 29020233, 30350318, 31719944, 33129852, 34580790, 36073511, 37608773, 39270442, 40978509,
            42733789, 44537107, 46389292, 48291180, 50243611, 52247435, 54303504, 56412678, 58575823, 60793812,  63067521, 65397835, 67785643,
            70231841, 72737330, 75303019, 77929820, 80618654, 83370455, 86186124, 89066630, 92012904, 95025896, 98106559, 101255855, 104474750,
            107764216, 111125230, 114558777, 118065845, 121647430, 125304532, 129038159, 132849323, 136739041, 140708338, 144758242, 148889790,
            153104021, 157401983, 161784728, 166253312, 170808801, 175452262, 180184770, 185007406, 189921255, 19492740);

        public function remainingXP( $currentXP, $targetLevel = NULL, $scale = "normal" ) {
            if ( $targetLevel == NULL ) {
                $targetLevel = $this->getLevel( $currentXP, $scale ) + 1;
            }

            return $this->getExperience( $targetLevel, $scale ) - $currentXP;
        }

        public function getLevel( $e, $scale = "normal" ) {
            if($scale == "normal") {
                return $this->closest( $this->levels, $e );
            } else {
                return $this->closest( $this->inventionXPTable, $e);
            }
        }

        function closest( $array, $number ) {
            for ( $i = 0; $i <= count( $array ); $i++ ) {
                if ( $number == $array[ $i ] ) {
                    return $i + 1;
                }

                if ( $number > $array[ $i ] && $number < $array[ $i + 1 ] ) {
                    return $i + 1;
                }
            }
            return count( $array );
        }

        public function getExperience( $l, $scale = "normal" ) {
            if($scale == "normal") {
                return $this->levels[ $l - 1 ];
            } else {
                return $this->inventionXPTable[ $l - 1 ];
            }
        }

        public function updatePlayer($rsn, $recursed = false, $override = false) {
            $ch = curl_init( "http://hiscore.runescape.com/index_lite.ws?player=$rsn" );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

            $result = curl_exec( $ch );
            curl_close( $ch );
            return array("updated", $result);
        }

//        public function updatePlayer( $rsn, $recursed = false, $override = false ) {
//            //Check for cached stats
//            $return = array();
//            $exists = $this->queryToAssoc( "SELECT * FROM apicache WHERE RSN='$rsn'" );
//
//            if ( count( $exists ) > 0 ) {
//                //Cached Stats found
//                $updateTime = strtotime( $exists[ 'TimeFetched' ] );
//
//                if ( $exists[ 'Active' ] != 2 ) {
//                    //These stats haven't been marked as inactive
//                    if ( strtotime( 'now' ) - $updateTime > 7200 || $override == true ) {
//                        //Stats are over 2 hours old OR override is true
//                        //Grab the least used, active IP from the Database
//
//                        $ch = curl_init( "http://hiscore.runescape.com/index_lite.ws?player=The+Orange" );
//                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
//
//                        $result = curl_exec( $ch );
//                        curl_close( $ch );
//                        if ( !strstr( $result, "<html>" ) && $result != "" ) {
//                            //Response Returned
//                            //Update the cache
//                            $this->query("UPDATE apicache SET Response='$result', TimeFetched=NOW() WHERE RSN='$rsn'");
//                            //Return stats/updated flag
//                            $return = array(
//                                "updated", $result
//                            );
//                        } else {
//                            if ( strtotime( 'now' ) - $updateTime < ( 86400 * 28 ) ) {
//                                //Return cached stats for up to 28 days
//                                //Return old stats and outdated flag
//                                $return = array(
//                                    "outdated", $exists["Response"]
//                                );
//                                //Flag this user as outdated
//                                $this->query("UPDATE apicache SET Active=2, LastChecked=NOW() WHERE RSN='$rsn'");
//                            } else {
//                                //These stats are old, return blank and not in use flag
//                                $this->query("UPDATE apicache SET Active=0, LastChecked=NOW() WHERE RSN='$rsn'");
//                                $return = array(
//                                    "not in use", ""
//                                );
//                            }
//                        }
////                        $ip = $this->queryToText( "SELECT IP FROM jagexip WHERE Active = 1 ORDER BY TimesUsed ASC LIMIT 1" );
////
////                        if ( $ip != "" ) {
////                            //There's an active IP
////                            //Setup curl
////                            $ch = curl_init( "http://$ip/index_lite.ws?player=$rsn" );
////                            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
////                            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Host: hiscore.runescape.com" ) );
////
////                            $result = curl_exec( $ch );
////                            curl_close( $ch );
////
////                            if ( !strstr( $result, "<html>" ) && $result != "" ) {
////                                //Response Returned
////                                //Update the cache
////                                $this->query( "UPDATE apicache SET Response='$result', TimeFetched=NOW() WHERE RSN='$rsn'" );
////                                //Return stats/updated flag
////                                $return = array(
////                                    "updated", $result
////                                );
////                            } else {
////                                if ( $result == "" ) {
////                                    //Blank result usually means inactive IP or blocked
////                                    //Deactivate this IP
////                                    $this->query( "UPDATE jagexip SET LastChecked=NOW(), Active = 0 WHERE IP='$ip'" );
////                                } else {
////                                    //404 page (stats not found)
////                                    if ( strtotime( 'now' ) - $updateTime < ( 86400 * 28 ) ) {
////                                        //Return cached stats for up to 28 days
////                                        //Return old stats and outdated flag
////                                        $return = array(
////                                            "outdated", $exists[ "Response" ]
////                                        );
////                                        //Flag this user as outdated
////                                        $this->query( "UPDATE apicache SET Active=2, LastChecked=NOW() WHERE RSN='$rsn'" );
////                                    } else {
////                                        //These stats are old, return blank and not in use flag
////                                        $this->query( "UPDATE apicache SET Active=0, LastChecked=NOW() WHERE RSN='$rsn'" );
////                                        $return = array(
////                                            "not in use", ""
////                                        );
////                                    }
////                                }
////                            }
//
//                            //Tell the database that this IP has been used
////                            $this->query( "UPDATE jagexip SET TimesUsed = TimesUsed + 1 WHERE IP='$ip'" );
////                        } else {
////                            //No active IP, update IP list and recurse
////                            $this->updateIPs();
////                            if ( !$recursed ) {
////                                return $this->updatePlayer( $rsn, true );
////                            } else {
////                                return array(
////                                    "no IP", ""
////                                );
////                            }
////                        }
////                    } else {
////                        //Cached stats under 2 hours old, send them and cached flag
////                        $return = array(
////                            "cached", $exists[ "Response" ]
////                        );
////                    }
//                } else {
//                    //These stats have been marked as inactive previous
//                    if ( strtotime( 'now' ) - $updateTime > ( 86400 * 10 ) /* 10 days */ ) {
//                        //Stats are over 10 days old
//                        //Grab the least used, active IP from the Database
//                        $ip = $this->queryToText( "SELECT IP FROM jagexip WHERE Active = 1 ORDER BY TimesUsed ASC LIMIT 1" );
//
//                        if ( $ip != "" ) {
//                            //There's an active IP
//                            //Setup curl
//                            $ch = curl_init( "http://$ip/index_lite.ws?player=$rsn" );
//                            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
//                            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Host: hiscore.runescape.com" ) );
//
//                            $result = curl_exec( $ch );
//                            curl_close( $ch );
//
//                            if ( !strstr( $result, "<html>" ) && $result != "" ) {
//                                //Response Returned
//                                //Update the cache
//                                $this->query( "UPDATE apicache SET Response='$result', TimeFetched=NOW(), Active=1 WHERE RSN='$rsn'" );
//                                //Return stats/updated flag
//                                $return = array(
//                                    "updated", $result
//                                );
//                            } else {
//                                if ( $result == "" ) {
//                                    //Blank result usually means inactive IP or blocked
//                                    //Deactivate this IP
//                                    $this->query( "UPDATE jagexip SET LastChecked=NOW(), Active = 0 WHERE IP='$ip'" );
//                                } else {
//                                    //404 page (stats not found)
//                                    if ( strtotime( 'now' ) - $updateTime < ( 86400 * 28 ) ) {
//                                        //Return cached stats for up to 28 days
//                                        //Return old stats and outdated flag
//                                        $return = array(
//                                            "outdated", $exists[ "Response" ]
//                                        );
//                                    } else {
//                                        //These stats are old, return blank and not in use flag
//                                        $this->query( "UPDATE apicache SET Active=0 WHERE RSN='$rsn'" );
//                                        $return = array(
//                                            "not in use", ""
//                                        );
//                                    }
//                                }
//                            }
//
//                            //Tell the database that this IP has been used
//                            $this->query( "UPDATE jagexip SET TimesUsed = TimesUsed + 1 WHERE IP='$ip'" );
//                        } else {
//                            //No active IP, update IP list and recurse
//                            $this->updateIPs();
//                            if ( !$recursed ) {
//                                return $this->updatePlayer( $rsn, true );
//                            } else {
//                                return array(
//                                    "no IP", ""
//                                );
//                            }
//                        }
//                    } else {
//                        $return = array(
//                            "outdated", $exists[ "Response" ]
//                        );
//                    }
//                }
//            } else {
//                //User has no cached stats
//                //Get least used, active IP
//                $ip = $this->queryToText( "SELECT IP FROM jagexip WHERE Active = 1 ORDER BY TimesUsed ASC LIMIT 1" );
//
//                if ( $ip != "" ) {
//                    //There is at least one valid IP
//
//                    //Setup curl
//                    $ch = curl_init( "http://$ip/index_lite.ws?player=$rsn" );
//                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
//                    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Host: hiscore.runescape.com" ) );
//
//                    $result = curl_exec( $ch );
//                    curl_close( $ch );
//
//                    if ( !strstr( $result, "<html>" ) && $result != "" ) {
//                        //Player found
//                        //Insert stats into the cache
//                        $this->query( "INSERT INTO apicache (RSN, Response, TimeFetched, Active ) VALUES ('$rsn', '$result', NOW(), 1)" );
//                        //Return stats and an updated flag
//                        $return = array(
//                            "updated", $result
//                        );
//                    } else {
//                        if ( $result == "" ) {
//                            //Blank result usually means inactive IP or blocked
//                            //Deactivate this IP
//                            $this->query( "UPDATE jagexip SET LastChecked=NOW(), Active = 0 WHERE IP='$ip'" );
//                        }
//                        //return blank and not in use flag
//                        $return = array(
//                            "not in use", ""
//                        );
//                    }
//
//                    //Tell the database that this IP has been used
//                    $this->query( "UPDATE jagexip SET TimesUsed = TimesUsed + 1 WHERE IP='$ip'" );
//                } else {
//                    //No active IP, update IP list and recurse
//                    $this->updateIPs();
//                    if ( !$recursed ) {
//                        return $this->updatePlayer( $rsn, true );
//                    } else {
//                        return array(
//                            "no IP", ""
//                        );
//                    }
//                }
//            }
//
//            //Return the result determined above
//            return $return;
//        }

        public function updateIPs() {
            $db = $this->connectToDatabase( $this->database );

            if ( $db[ 'found' ] ) {
                $domain    = 'hiscore.runescape.com';
                $dnsrecord = dns_get_record( $domain, DNS_A );
                $iplist    = array();
                $newip     = false;

                foreach ( $dnsrecord as $dns ) {
                    $ip     = $dns[ 'ip' ];
                    $exists = $this->query( "SELECT * FROM jagexip WHERE IP='$ip'" );

                    if ( mysql_num_rows( $exists ) > 0 ) {
                        //IP exists in database
                        //Setup Curl
                        $ch = curl_init( "http://$ip/index_lite.ws" );
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Host: hiscore.runescape.com" ) );
                        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
                        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
                        $result = curl_exec( $ch );
                        curl_close( $ch );

                        if ( $result != "" ) {
                            //Response recieved, set as active
                            $iplist[ ] = $ip;
                            $this->query( "UPDATE jagexip SET Lastchecked=NOW(), Active=1 WHERE IP='$ip'" );
                        } else {
                            //No response was recieved, set it as inactive
                            $this->query( "UPDATE jagexip SET Lastchecked=NOW(), Active=0 WHERE IP='$ip'" );
                        }
                    } else {
                        //This is a brand new IP
                        $newip = true;

                        //Setup curl
                        $ch = curl_init( "http://$ip/index_lite.ws" );
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Host: hiscore.runescape.com" ) );
                        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
                        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
                        $result = curl_exec( $ch );
                        curl_close( $ch );

                        if ( $result != "" ) {
                            //Response recieved, put in database
                            $iplist[ ] = $ip;
                            $this->query( "INSERT INTO jagexip (IP, LastChecked) VALUES ('$ip', NOW())" );
                        }
                        //No need to put inactive IPs in the database
                    }
                }

                if ( $newip ) {
                    //A new IP was found, means an old one is now inactive.
                    $storedips = $this->getAllAssocResults( "SELECT IP FROM jagexip WHERE Active = 1" );

                    foreach ( $storedips as $dbip ) {
                        //loop through all active IP's in the DB
                        $ip = $dbip[ 'IP' ];
                        if ( !in_array( $ip, $iplist ) ) {
                            //If this stored IP is not in the currently found set of IPs, deactivate it.
                            $this->query( "UPDATE jagexip SET LastChecked=NOW(), Active = 0 WHERE IP='$ip'" );
                        }
                    }
                }
            }
        }
    }