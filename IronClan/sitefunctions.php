<?php
    require_once "dbfunctions.php";
    class sitefunctions extends dbfunctions {
        public static $FLAGS = array(
            "New Member" => 1,
            "Rank Change" => 2,
            "Lost Member" => 3,
            "Inactive Member" => 4,
            "Capped at Citadel" => 5,
            "New Event" => 6,
            "Event Attendance" => 7
        );

        public static $CLANRANKS = array(
            "Owner" => 1,
            "Deputy Owner" => 2,
            "Overseer" => 3,
            "Coordinator" => 4,
            "Organiser" => 5,
            "Admin" => 6,
            "General" => 7,
            "Captain" => 8,
            "Lieutenant" => 9,
            "Sergeant" => 10,
            "Corporal" => 11,
            "Recruit" => 12
        );

        function Update_Clan_List() {
            $oldList = $this->getAllAssocResults("SELECT * FROM clanmembers");

            $ch = curl_init("http://services.runescape.com/m=clan-hiscores/members_lite.ws?clanName=Iron+Dragon");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            //print_r($result);

            $clanlist = explode("\n", $result);

            //Check New List
            $newList = array();
            foreach ($clanlist as $i => $clanmember) {
                //First line of file is headers, skip it
                if ($i > 0) {
                    if ($clanmember != "") {
                        //Break the clan member string into an array
                        $clanmember = explode(",", $clanmember);
                        $rsn = $clanmember[0];

                        $rsn = urlencode($rsn);
                        $rsn = str_replace("%A0", "+", $rsn);
                        $rsn = urldecode($rsn);

                        //Add the RSN to the list to check against the old list later
                        $newList[] = $rsn;
                        $rank = $clanmember[1];

                        //Get the Database ID for this members rank.
                        $rankID = self::$CLANRANKS[$rank];

                        //Check if this user is an old member or a new member
                        $exists = $this->queryToAssoc("SELECT * FROM clanmembers WHERE RSN='$rsn'");
                        if (count($exists) == 0) {
                            //New Member

                            $this->query("INSERT INTO clanmembers (RSN, ClanRankID, DateJoined) VALUES ('$rsn', '$rankID', NOW())");
                            echo mysql_error();
                        } else {
                            //Old Member

                            //Check if rank has changed
                            $clanmemberid = $exists['ClanMemberID'];
                            if ($exists['ClanRankID'] != $rankID) {
                                //Update to new rank
                                $this->query("UPDATE clanmembers SET ClanRankID='$rankID' WHERE ClanMemberID='$clanmemberid'");
                            }
                        }
                    }
                }
            }

            //Check Old List
            foreach($oldList as $member) {
                $rsn = $member['RSN'];

                //Check each name to see if it's in the new list
                if(!in_array($rsn, $newList)) {
                    $memberid = $member['ClanMemberID'];
                    $this->query("UPDATE clanmembers SET NameChanged = 1 WHERE ClanMemberID = '$memberid'");
                }
            }
        }
    }