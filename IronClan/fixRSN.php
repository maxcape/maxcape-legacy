<?php
    require_once "dbfunctions.php";
    $dbf = new dbfunctions();

    $list = $dbf->getAllAssocResults("SELECT * FROM clanmembers");

    foreach($list as $member) {
        $rsn = $member['RSN'];

        echo $rsn . "<br>";
        $rsn = urlencode($rsn);
        echo $rsn . "<br>";
        $rsn = str_replace("%A0", "+", $rsn);
        echo $rsn . "<br>";
        $rsn = urldecode($rsn);
        echo $rsn . "<br>";

        echo "<br>";

        $dbf->query("UPDATE clanmembers SET RSN='$rsn' WHERE ClanMemberID = ". $member['ClanMemberID']);
    }

?>