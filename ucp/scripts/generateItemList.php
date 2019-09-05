<?php
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database);

    $items = $dbf->getAllAssocResults("SELECT * FROM grandexchange ORDER BY ItemName ASC");

    foreach ($items as $i => $item) {
        if($i != count($items)) {
            echo "" . $item['ItemName'] . ",";
        } else {
            echo "" . $item['ItemName'] . "";
        }
    }
