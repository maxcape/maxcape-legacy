<?php
    require_once "dbfunctions.php";
    $dbf = new dbfunctions();

    $nameValidOverride = "9x782m23";

    $oldName = $_GET['old'];
    $newName = $_GET['new'];

    $oldID = $dbf->queryToText("SELECT ClanMemberID FROM clanmembers WHERE RSN = '$oldName'");
    $newID = $dbf->queryToText("SELECT ClanMemberID FROM clanmembers WHERE RSN = '$newName'");

    if($oldID == "") {
        echo "Old name not found";
        die();
    }

    if($newID == "") {
        echo "New name not found";
        die();
    }

    $lastUpdateOnOldName = $dbf->queryToText("SELECT MAX(StatUpdateID) FROM stathistory WHERE ClanMemberID = '$oldID' AND LEFT(HSLiteData,1) != '<'");
    $firstUpdateOnNewName = $dbf->queryToText("SELECT MIN(StatUpdateID) FROM stathistory WHERE ClanMemberID = '$newID'");

    if(intval($lastUpdateOnOldName) + 1 == intval($firstUpdateOnNewName)) {
        $lastUpdateOldHSLiteData = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE StatUpdateID = '$lastUpdateOnOldName' AND ClanMemberID='$oldID'");
        $firstUpdateNewHSLiteData = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE StatUpdateID = '$firstUpdateOnNewName' AND ClanMemberID='$newID'");

        $lastStats = preg_split("/\s+/", $lastUpdateOldHSLiteData);
        $lastxp = explode(",", $lastStats[0])[2];
        $lastxp = intval($lastxp);

        $firstStats = preg_split("/\s+/", $firstUpdateNewHSLiteData);
        $firstxp = explode(",", $firstStats[0])[2];
        $firstxp = intval($firstxp);

        $plus10Per = $lastxp + ($lastxp * 0.1);

        if(($firstxp >= $lastxp && $firstxp < $plus10Per) || $_GET['override'] === $nameValidOverride) {
            //Change old data
            $dbf->query("UPDATE stathistory SET ClanMemberID='$newID' WHERE ClanMemberID='$oldID' AND StatUpdateID < '$firstUpdateOnNewName'");

            //clean up old name and dead 404 stats
            $dbf->query("DELETE FROM stathistory WHERE ClanMemberID = '$oldID'");
            $dbf->query("DELETE FROM clanmembers WHERE ClanMemberID='$oldID'");

            echo "$oldName successfully combined with $newName";
        } else {
            echo "Name change not valid: Experience too far apart";
        }
    } else {
        echo "Name change not valid - Updates don't line up";
    }
?>