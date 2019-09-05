<?php
    session_start();
    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    $myusername = $_SESSION[ 'username' ];

    $dbf->connectToDatabase( $dbf->database ) or die( "cannot connect to database" );

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Giveaways - Max/Comp Cape Calc</title>

        <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

        <style>
            .start, .end, .entered {
                margin: 0;
            }

            .giveaway-date {
                color: #076932;
            }

            #giveaway-info {
                float: left;
                width: 70%;
                min-height: 75px;
                margin-bottom: 10px;
            }

            #giveaway-info p {
                margin: 0;
            }

            .giveaway-title {
                margin: 0;
            }

            .current {
                text-align: center;
                margin: 0;
            }

            #date-container {
                overflow: hidden;
                float: right;
                width: 30%;
                min-height: 75px;
            }

            .list {
                width: 25%;
                float: left;
            }

            .list h3 {
                margin: 0;
            }

            .list ol {
                margin-top: 0;
            }

            .entry {
                width: 40% !important;
                margin-right: 10%;
            }

            .entry p {
                margin: 0 0 25px 0;

            }

            .entry-desc {
                font-size: 10px;
                display: block;
                margin: 0;
            }

            .entry input[type='text'] {
                box-sizing: border-box;
                width: 70%;

                background-color: #DDD;
                background-image: linear-gradient(bottom, #D6D6D6 35%, #C2C2C2 68%);
                background-image: -o-linear-gradient(bottom, #D6D6D6 35%, #C2C2C2 68%);
                background-image: -moz-linear-gradient(bottom, #D6D6D6 35%, #C2C2C2 68%);
                background-image: -webkit-linear-gradient(bottom, #D6D6D6 35%, #C2C2C2 68%);
                background-image: -ms-linear-gradient(bottom, #D6D6D6 35%, #C2C2C2 68%);
                background-image: -webkit-gradient(linear, left bottom, left top, color-stop(0.35, #D6D6D6), color-stop(0.68, #C2C2C2));
                border: thin solid #AAA;
                height: 30px;
                padding: 5px;
            }

            .entry input[type='text']:focus {
                outline: none;
            }

            .entry input[type='text']:valid {
                color: green;
                background: #DDD url("/images/checkmark.png") no-repeat 98% center;
            }

            .entry input[type='text']:invalid {
                color: red;
                background: #DDD url("/images/x.png") no-repeat 98% center;
            }

            .entry button {
                height: 30px;
                padding: 5px;
                background-color: #005734;
                background-image: linear-gradient(bottom, #076932 11%, #005734 50%);
                background-image: -o-linear-gradient(bottom, #076932 11%, #005734 50%);
                background-image: -moz-linear-gradient(bottom, #076932 11%, #005734 50%);
                background-image: -webkit-linear-gradient(bottom, #076932 11%, #005734 50%);
                background-image: -ms-linear-gradient(bottom, #076932 11%, #005734 50%);
                background-image: -webkit-gradient(linear, left bottom, left top, color-stop(0.11, #076932), color-stop(0.5, #005734));
                border: thin solid #AAA;
                color: #AAA;
                width: 20%;
            }

            #entry-result {
                display: block;
                color: red;
                margin-top: 5px;
            }

            .claimed {
                color:green;
            }
        </style>

        <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
    </head>

    <body>
        <?php require_once( "../masthead.php" ); ?>

        <?php
            $currentGiveaway = $dbf->queryToAssoc( "SELECT * FROM giveaways WHERE StartDate <= NOW() AND EndDate >= NOW()" );
            $currentGiveawayID = $currentGiveaway[ 'GiveawayID' ];
            $startTime = strtotime( $currentGiveaway[ 'StartDate' ] );
            $endTime = strtotime( $currentGiveaway[ 'EndDate' ] );

            $prizes = $dbf->getAllAssocResults( "SELECT * FROM giveawayprizes WHERE GiveawayID = '$currentGiveawayID'" );

            $namecount = $dbf->queryToText( "SELECT COUNT(*) FROM giveawayentries WHERE GiveawayID='$currentGiveawayID'" );

        ?>
        <div id="content">
            
            <div class="innercontent">
                <?php
                    if( count( $currentGiveaway ) != 0 ) {
                        ?>
                        <h1 class="current">Current Giveaway</h1>
                        <hr>

                        <div id="giveaway-info">
                            <h2 class="giveaway-title"><?php echo $currentGiveaway[ 'Title' ]; ?></h2>

                            <p><?php echo $currentGiveaway[ 'Description' ]; ?></p>
                        </div>
                        <div id="date-container">
                            <p class="start">Starts: <span class="giveaway-date"><?php echo date( "F jS, Y", $startTime ); ?></span></p>

                            <p class="end">Ends: <span class="giveaway-date"><?php echo date( "F jS, Y", $endTime ); ?></span></p>

                            <p class="entered">Total Names Entered: <?php echo number_format( $namecount ); ?></p>
                        </div>

                        <div class="entry list">
                            <h3>Entry</h3>

                            <p>To enter the giveaway, simply input your RuneScape Character name in the box below and click enter. You may enter as many names as you have, but please only enter for yourself.</p>

                            <input id="rsn-entry" type="text" required="required" pattern="[a-zA-Z0-9-_ ]{1,12}"/>
                            <button type="button" onclick="submitEntry(<?php echo $currentGiveawayID; ?>);">Enter</button>
                            <span class="entry-desc">RSN's must be 1 - 12 characters using only A-Z, 0-9, -, _, or a space.</span>

                            <span id="entry-result"></span>
                        </div>

                        <div class="prizes list">
                            <h3>Prizes</h3>
                            <ol id="giveaway-prizes">
                                <?php
                                    $numberPrizes = 0;
                                    foreach( $prizes as $prize ) {
                                        $numberPrizes++;
                                        ?>
                                        <li><?php echo $prize[ 'Prize' ]; ?></li>
                                    <?php
                                    }
                                ?>
                            </ol>
                        </div>

                        <div class="winners list">
                            <h3>Winners</h3>
                            <ol id="giveaway-winners">
                                <?php
                                    for( $i = 0; $i < $numberPrizes; $i++ ) {
                                        ?>
                                        <li>&nbsp;</li>
                                    <?php
                                    }
                                ?>
                            </ol>
                        </div>
                    <?php
                    } else {
                        $currentGiveaway   = $dbf->queryToAssoc( "SELECT * FROM giveaways ORDER BY EndDate DESC LIMIT 1" );
                        $currentGiveawayID = $currentGiveaway[ 'GiveawayID' ];
                        $startTime         = strtotime( $currentGiveaway[ 'StartDate' ] );
                        $endTime           = strtotime( $currentGiveaway[ 'EndDate' ] );

                        $prizes = $dbf->getAllAssocResults( "SELECT * FROM giveawayprizes WHERE GiveawayID = '$currentGiveawayID'" );

                        $namecount = $dbf->queryToText( "SELECT COUNT(*) FROM giveawayentries WHERE GiveawayID='$currentGiveawayID'" );

                        ?>
                        <h1 class="current">Last Giveaway</h1>
                        <hr>

                        <div id="giveaway-info">
                            <h2 class="giveaway-title"><?php echo $currentGiveaway[ 'Title' ]; ?> (Ended)</h2>

                            <p><?php echo $currentGiveaway[ 'Description' ]; ?></p>
                        </div>
                        <div id="date-container">

                            <p class="end">Ended: <span class="giveaway-date"><?php echo date( "F jS, Y", $endTime ); ?></span></p>



                            <p class="entered">Total Names Entered: <?php echo number_format( $namecount ); ?></p>
                        </div>

                        <div class="entry list">
                            <h3>Entry</h3>

                            <p>To enter the giveaway, simply input your RuneScape Character name in the box below and click enter. You may enter as many names as you have, but please only enter for yourself.</p>

                            <span class="entry-desc">Entries are now closed</span>
                        </div>

                        <div class="prizes list">
                            <h3>Prizes</h3>
                            <ol id="giveaway-prizes">
                                <?php
                                    $numberPrizes = 0;
                                    foreach( $prizes as $prize ) {
                                        $numberPrizes++;
                                        ?>
                                        <li><?php echo $prize[ 'Prize' ]; ?></li>
                                    <?php
                                    }
                                ?>
                            </ol>
                        </div>

                        <div class="winners list">
                            <h3>Winners</h3>
                            <ol id="giveaway-winners">
                                <?php
                                    $winners = $dbf->getAllAssocResults( "SELECT Name, Claimed FROM giveawaywinners gw JOIN giveawayentries g ON gw.GiveawayEntryID = g.GiveawayEntryID WHERE gw.GiveawayID='$currentGiveawayID'" );
                                    if( count( $winners ) > 0 ) {
                                        for( $i = 0; $i < $numberPrizes; $i++ ) {
                                            $claimed = $winners[$i]['Claimed']
                                            ?>
                                            <li <?php echo $claimed == "1" ? "class='claimed'" : ""; ?>><?php echo $winners[$i]['Name']; ?></li>
                                        <?php
                                        }
                                    } else {
                                        for( $i = 0; $i < $numberPrizes; $i++ ) {
                                            ?>
                                            <li>&nbsp;</li>
                                        <?php
                                        }
                                    }
                                ?>
                            </ol>
                        </div>
                    <?php
                    }
                ?>

                <div id="rules" style="clear:both;">
                    <h3 style="margin:0;">Rules</h3>

                    <p style="margin:0; font-size:75%;">You may enter as many RSN's as you like, as long as you yourself has access to that account. Rewards will only be given to an account using the winning display name or is previously known as that name. You have one week to make contact with The Orange after the winners are chosen to claim your prize.</p>
                </div>
            </div>

            <div id="footer">
                <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
            </div>
        </div>

        <script>
            jQuery.extend(jQuery.expr[':'], {
                valid: function (elem, index, match) {
                    var valids = document.querySelectorAll(':valid'),
                        result = false,
                        len = valids.length;

                    if (len) {
                        for (var i = 0; i < len; i++) {
                            if (elem === valids[i]) {
                                result = true;
                                break;
                            }
                        }
                    }
                    return result;
                }
            });


            function submitEntry(giveawayID) {
                var entry = $("#rsn-entry");

                if (entry.is(":valid")) {
                    var name = entry.val();

                    $.post("entry.php", {rsn: name, id: giveawayID}, function (data) {
                        $("#entry-result").text(data);
                    });
                } else {
                    $("#entry-result").text("'" + entry.val() + "' is not a valid RSN!");
                }
            }
        </script>
    </body>
</html>