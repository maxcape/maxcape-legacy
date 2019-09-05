<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions();
    require_once("../../userfunctions.php");
    $uf = new userfunctions();

    $loggedin = $uf->isLoggedIn();
    $userid = $_SESSION['userid'];

    if ($loggedin) {
        $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

        $logid = mysql_real_escape_string($_POST['id']);
        $mylog = $dbf->queryToArray("SELECT * FROM logs WHERE LogID='$logid' AND UserID='$userid'");

        if (count($mylog) > 0) {
            $items = $_POST['items'];
            $logtype = $dbf->queryToText("SELECT LogType FROM logs WHERE LogID='$logid'");

            if ($logtype == 1) {
                $item = mysql_real_escape_string($items[0]["item"]);
                $item = htmlentities($item);
                $amount = mysql_real_escape_string($items[0]["amount"]);

                $itemid = $dbf->queryToText("SELECT ItemID FROM grandexchange WHERE ItemName='$item'");

                if($itemid == 0) {
                    $lowestuntradeableID = $dbf->queryToText("SELECT ItemID FROM grandexchange WHERE ItemID > 900000 ORDER BY ItemID ASC LIMIT 1");

                    if($lowestuntradeableID == "") {
                        $itemid = "999999999";
                    } else {
                        $itemid = intval($lowestuntradeableID) - 1;
                    }

                    $dbf->query("INSERT INTO grandexchange (CategoryNumber, ItemID, ItemName, ItemDescription, ItemPrice) VALUES (38, '$itemid', '$item', 'Unknown Description', 0)");
                }
                $exists = $dbf->queryToArray("SELECT * FROM logitems WHERE ItemID='$itemid' and LogID='$logid'");

                if (count($exists) == 0) {
                    $dbf->query("INSERT INTO logitems (LogID, ItemID, Amount) VALUES ('$logid', '$itemid', '$amount')");
                } else {
                    if ($amount[0] == "+") {
                        $duplicate = "Amount = Amount + " . substr($amount, 1);
                    } elseif ($amount[0] == "-") {
                        $duplicate = "Amount = Amount - " . substr($amount, 1);
                    } else {
                        $duplicate = "Amount = " . $amount;
                    }

                    $dbf->query("UPDATE logitems SET $duplicate WHERE LogID='$logid' AND ItemID='$itemid'");
                }
            } else {
                $tripnumber = $dbf->queryToText("SELECT IFNULL(MAX(TripNumber) + 1, 1) FROM logtrips WHERE LogID ='$logid'");
                $dbf->query("INSERT INTO logtrips (LogID, TripNumber) VALUES ('$logid', '$tripnumber')");
                $tripid = mysql_insert_id();

                foreach ($items as $thisitem) {
                    $item = mysql_real_escape_string($thisitem["item"]);
                    $item = htmlentities($item);
                    $amount = mysql_real_escape_string($thisitem["amount"]);

                    $itemid = $dbf->queryToText("SELECT ItemID FROM grandexchange WHERE ItemName='$item'");
                    if($itemid == 0) {
                        $lowestuntradeableID = $dbf->queryToText("SELECT ItemID FROM grandexchange WHERE ItemID > 900000 ORDER BY ItemID ASC LIMIT 1");

                        if($lowestuntradeableID == "") {
                            $itemid = "999999999";
                        } else {
                            $itemid = intval($lowestuntradeableID) - 1;
                        }

                        $dbf->query("INSERT INTO grandexchange (CategoryNumber, ItemID, ItemName, ItemDescription, ItemPrice) VALUES (38, '$itemid', '$item', 'Unknown Description', 0)");
                    }

                    $dbf->query("INSERT INTO logitems (LogID, ItemID, Amount) VALUES ('$logid', '$itemid', '$amount')");

                    $logitemid = mysql_insert_id();

                    $dbf->query("INSERT INTO logtripitems (logTripId, logItemID) VALUES ('$tripid', '$logitemid')");
                }
            }
        }
    }