<?php
    require_once("dbfunctions.php");
    $dbf = new dbfunctions;

    function clean_rsn($r) {
        $name_regex = "/[^a-zA-Z0-9-_ ]/";

        preg_match($name_regex, $r, $matches);

        if(preg_match($name_regex, $r)) {
            if(strlen($r) > 12) {
                return 3;
            } else {
                return 1;
            }

        } else {
            if(strlen($r) > 12) {
                return 2;
            } else {
                return 0;
            }
        }
    }

    $res = array("Valid", "Invalid Character", "Too Long", "Invalid Character + Too Long");

    $dbf->connectToDatabase($dbf->database) or die("Cannnot connect to database!");

    $name_list = $dbf->getAllAssocResults("SELECT RSN, COUNT(*) AS Number FROM searches GROUP BY RSN");

    $errors = 0;

    for($i = 0; $i < count($name_list); $i++) {
        $db_rsn = $name_list[$i]['RSN'];
        $rsn = mysql_real_escape_string($db_rsn);
        $valid = clean_rsn($db_rsn);

        $times_searched = $name_list[$i]['Number'];
        $last_searched = $dbf->queryToText("SELECT MAX(Time) FROM searches WHERE RSN='$rsn'");

        if($valid != 0) {
            echo $errors . ": " . $res[$valid] . " - " . $db_rsn . " - " . $times_searched . " - " . $last_searched . "<br>";
            $errors++;
        }

    }