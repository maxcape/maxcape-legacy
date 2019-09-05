<?php
    require_once("../dbfunctions.php");
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database.");

    $giveawayID = $dbf->queryToText("SELECT GiveawayID FROM giveaways ORDER BY GiveawayID DESC LIMIT 1");

    $entries = $dbf->getAllAssocResults("SELECT Name, GiveawayEntryID FROM giveawayentries WHERE GiveawayID='$giveawayID'");

    $numberOfPrizes = $dbf->queryToText("SELECT COUNT(*) FROM giveawayprizes WHERE GiveawayID='$giveawayID'");

    $winners = array();
    $winnerID = array();

    for($i = 1; $i <= $numberOfPrizes; $i++) {
        echo "Selecting name.<br>";
        shuffle($entries);

        $choose = mt_rand(0, count($entries) - 1);

        $name = $entries[$choose]['Name'];
        $id = $entries[$choose]['GiveawayEntryID'];

        if(!in_array($name, $winners)) {
            $winners[] = $name;

            $prevwinner = $dbf->queryToAssoc("SELECT GiveawayEntryID, Place, Claimed FROM giveawaywinners WHERE Place='$i' AND GiveawayID='$giveawayID'");

            if(count($prevwinner) == 0) {
                echo "$i: No previous winner.<br>";
                $dbf->query("INSERT INTO giveawaywinners (GiveawayID, Place, GiveawayEntryID) VALUES ('$giveawayID', '$i', '$id')");
            } else {
                if(intval($prevwinner['Claimed']) != 1) {
                    echo "$i: Reward unclaimed, selecting new winner.<br>";
                    $dbf->query("UPDATE giveawaywinners SET GiveawayEntryID='$id' WHERE GiveawayID='$giveawayID' AND Place='$i'");
                } else {
                    echo "$i: Reward claimed, ignoring<br>";
                }
            }
        } else {
            $i--;
        }
    }