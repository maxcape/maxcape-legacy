<?php
    set_time_limit(0);

    require_once "sitefunctions.php";
    $dbf = new sitefunctions();
    $dbf->Update_Clan_List();

    $clanList = $dbf->getAllAssocResults("SELECT * FROM clanmembers WHERE NameChanged = 0");

    $dbf->query("INSERT INTO statupdates (TimeFetched) VALUES(NOW())");
    $updateID = mysql_insert_id();

    foreach($clanList as $member) {
        $rsn = $member['RSN'];
        $memberID = $member['ClanMemberID'];

        $ch = curl_init("http://hiscore.runescape.com/index_lite.ws?player=$rsn");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        if($result != "") {
            $dbf->query("INSERT INTO stathistory (ClanMemberID, HSLiteData, StatUpdateID) VALUES ('$memberID', '$result', '$updateID')");

            echo "Stats successfully fetched for member: $rsn";
        } else {
            echo "Stats unavailable for member: $rsn";
        }

        sleep(5); //Delay 5 seconds between requests
    }

    $dbf->query("UPDATE statupdates SET Finished='1' WHERE StatUpdateID='$updateID'");