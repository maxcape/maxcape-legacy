<?php
    require_once("../../rsfunctions.php");
    require_once("../../dbfunctions.php");
    require_once("../../userfunctions.php" );;
    $rsf = new rsfunctions;
    $dbf = new dbfunctions;
    $uf = new userfunctions;


    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION['username'];

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");
    $logid = mysql_real_escape_string($_GET['logid']);

    $viewedlog = $dbf->queryToAssoc("SELECT * FROM logs WHERE LogID='$logid'");

    if (count($viewedlog) == 0) {
        header("Location: ../?badlog=1&logid=$logid");
    }


    $author = $dbf->queryToText("SELECT Username FROM users WHERE UserID ='" . $viewedlog['UserID'] . "'");

    if ($viewedlog['LogType'] == 1 || $viewedlog['LogType'] == 2 || $viewedlog['LogType'] == 3) {
        $logitems = $dbf->getAllAssocResults("SELECT LogItemID, s.ItemID, ItemName, ItemDescription, Amount, ItemPrice, (Amount * ItemPrice) AS TotalValue FROM logitems s JOIN grandexchange g ON s.ItemID = g.ItemID WHERE LogID='$logid' ORDER BY CategoryNumber ASC, ItemName ASC");
    } else {
        $logitems = $dbf->getAllAssocResults("SELECT LogItemID, s.ItemID, ItemName, ItemDescription, Amount, ItemPrice, (Amount * ItemPrice) AS TotalValue FROM logitems s JOIN grandexchange g ON s.ItemID = g.ItemID WHERE LogID IN (SELECT SecondaryLogID FROM logscumulative WHERE PrimaryLogID = '$logid')  ORDER BY CategoryNumber ASC, ItemName ASC");
    }
    $totalvalue = 0;

    foreach ($logitems as $item) {
        $totalvalue += $item['TotalValue'];
    }

    $isfavorited = $dbf->queryToArray("SELECT * FROM logsfavorites WHERE LogID='$logid' AND UserID='" . $_SESSION['userid'] . "'");

    if(count($isfavorited) > 0) {
        $isfavorited = true;
    } else {
        $isfavorited = false;
    }

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $viewedlog['LogTitle']; ?> - Max/Comp Cape Calc</title>

    <link rel="stylesheet" href="/css/base/wrapper.css"/>
    <link rel="stylesheet" href="/css/special/logs.css"/>
    <script src="/js/jquery.js"></script>


</head>
<body>
    <?php require_once("../../masthead.php"); ?>

    <div id="content">
        <div class="innercontent">
            <h1 style="float:left;"><?php if($loggedin) { ?> <i title="Add to Favorites" class="<?php echo $isfavorited ? "favorited icon-star" : "icon-star-empty" ?>" id="favoriteLog"></i> <?php } ?><?php echo $viewedlog['LogTitle']; ?></h1>

            <h1 style="float:right;">Total Value: <?php echo number_format($totalvalue); ?></h1>

            <span style="float:left; margin:4px 0 0 0; clear:left;">A log by <?php echo $author; ?></span>
            <span style="float:right; margin:4px 0 0 0; clear:right;"><?php echo $viewedlog['LogDescription']; ?></span>

            <?php
                if ($viewedlog['LogType'] == 1 || $viewedlog['LogType'] == 4) {
                    if ($viewedlog['LogType'] == 1) {
                        $logitems = $dbf->getAllAssocResults("SELECT LogItemID, s.ItemID, ItemName, ItemDescription, Amount, ItemPrice, IconURL, (Amount * ItemPrice) AS TotalValue FROM logitems s JOIN grandexchange g ON s.ItemID = g.ItemID WHERE LogID='$logid' AND Amount > 0 ORDER BY CategoryNumber ASC, ItemName ASC");
                    } else {
                        $logitems = $dbf->getAllAssocResults("SELECT LogItemID, s.ItemID, ItemName, ItemDescription, ItemPrice, IconURL, COUNT(*) AS Amount, (COUNT(*) * ItemPrice) AS TotalValue FROM logitems s JOIN grandexchange g ON s.ItemID = g.ItemID WHERE LogID IN (SELECT SecondaryLogID FROM logscumulative WHERE PrimaryLogID = '$logid') AND Amount > 0 GROUP BY s.ItemID ORDER BY CategoryNumber ASC, ItemName ASC");
                    }
                    ?>
                    <div class="itemlist">
                        <?php
                            foreach ($logitems as $item) {
                                $itemName = $item['ItemName'];

                                $filepath = "../icons/$itemName.gif";

                                if (!file_exists($filepath)) {
                                    if($item['IconURL'] != "") {
                                        $filepath = $item['IconURL'];
                                    } else {
                                        $filepath = "";
                                    }
                                }

                                ?>
                                <div class="item" data-item="<?php echo strtolower($item['ItemName']); ?>" data-number="<?php echo $item['Amount']; ?>">
                                    <div class="item-information">
                                        <p class="item-title"><?php echo $item['ItemName']; ?></p>
                                        <?php
                                            if ($filepath != "") {
                                                ?>
                                                <img src="<?php echo $filepath; ?>"/>
                                            <?php
                                            } else {
                                                ?>
                                                <span class="imgreplacement">?</span>
                                            <?php
                                            }
                                        ?>
                                        <p class="item-amount"><?php echo number_format($item['Amount']); ?></p>
                                        <p class="item-price"><?php echo number_format($item['ItemPrice']); ?></p>
                                        <p class="item-totalvalue"><?php echo number_format($item['TotalValue']); ?></p>
                                    </div>
                                </div>
                            <?php

                            }
                        ?>
                    </div>
                <?php
                } else {
                    $trips = $dbf->getAllAssocResults("SELECT * FROM logtrips WHERE LogID='$logid'");

                    if($viewedlog['LogType'] == 2) {
                        $triptxt = "Trip";
                    } else {
                        $triptxt = "Kill";
                    }

                    foreach ($trips as $i => $trip) {

                        ?>
                        <div class="itemlist">
                            <div class="trip">
                            <h3><?php echo "$triptxt #" . $trip['TripNumber'] ?></h3>
                            <?php
                                $lid = $trip['LogTripID'];
                                $logitems = $dbf->getAllAssocResults("SELECT LogItemID, s.ItemID, ItemName, ItemDescription, Amount, ItemPrice, IconURL, (Amount * ItemPrice) AS TotalValue FROM logitems s JOIN grandexchange ge ON s.ItemID = ge.ItemID WHERE Amount > 0 AND LogItemID IN (SELECT LogItemID FROM logtripitems WHERE LogTripID = '$lid')");
                                foreach($logitems as $item) {
                                    $itemName = $item['ItemName'];

                                    $filepath = "../icons/$itemName.gif";

                                    if (!file_exists($filepath)) {
                                        if($item['IconURL'] != "") {
                                            $filepath = $item['IconURL'];
                                        } else {
                                            $filepath = "";
                                        }
                                    }
                                    ?>
                                    <div class="item" data-item="<?php echo strtolower($item['ItemName']); ?>" data-number="<?php echo $item['Amount']; ?>">
                                        <div class="item-information">
                                            <p class="item-title"><?php echo $item['ItemName']; ?></p>
                                            <?php
                                                if ($filepath != "") {
                                                    ?>
                                                    <img src="<?php echo $filepath; ?>"/>
                                                <?php
                                                } else {
                                                    ?>
                                                    <span class="imgreplacement">?</span>
                                                <?php
                                                }
                                            ?>
                                            <p class="item-amount"><?php echo number_format($item['Amount']); ?></p>
                                            <p class="item-price"><?php echo number_format($item['ItemPrice']); ?></p>
                                            <p class="item-totalvalue"><?php echo number_format($item['TotalValue']); ?></p>
                                        </div>
                                    </div>
                                <?php

                                }
                            ?>
                            </div>
                        </div>
                    <?php
                    }
                }
            ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#favoriteLog").click(function() {
                var icon = $(this);
                if(!icon.hasClass("favorited")) {
                    $.post("/ucp/scripts/favoriteLog.php", {"logid": <?php echo $logid; ?>, "action": "favorite"}, function(data) {
                        icon.addClass("favorited").removeClass("icon-star-empty").addClass("icon-star");
                    });
                } else {
                    $.post("/ucp/scripts/favoriteLog.php", {"logid": <?php echo $logid; ?>, "action": "unfavorite"}, function() {
                        icon.removeClass("favorited").removeClass("icon-star").addClass("icon-star-empty");
                    });
                }
            });
        });
    </script>
</body>
</html>