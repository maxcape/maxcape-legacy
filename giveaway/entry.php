<?php
    require_once("../dbfunctions.php");
    $dbf = new dbfunctions;
    require_once("../rsfunctions.php");
    $rsf = new rsfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $name = mysql_real_escape_string($_POST['rsn']);
    $id = mysql_real_escape_string($_POST['id']);

    $exists = $dbf->getAllAssocResults("SELECT * FROM giveawayentries WHERE Name = '$name' AND GiveawayID = '$id'");

    if(count($exists) == 0) {
        $dbf->query("INSERT INTO giveawayentries (Name, GiveawayID) VALUES ('$name', '$id')");
        echo "You have successfully entered the drawing as '$name'";
    } else {
        echo "Records indicate that '$name' is already entered for this drawing!";
    }