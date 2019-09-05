<?php
    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf = new userfunctions;
    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];
    $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database." );

    $max = $dbf->queryToText( "SELECT MAX(Milestone) FROM apicache LIMIT 1" );

    $playerlist = $dbf->getAllAssocResults( "SELECT RSN FROM apicache WHERE Milestone='$max'" );
?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Milestones - Max/Completionist Cape Calculator</title>
            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
            <link rel="stylesheet" href="milestones.css">

            <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

            <script>
                $(document).ready(function () {
                    $("#cape").change(function () {
                        var val = $(this).val();

                        $.post("list.php", {"milestone": val}, function (data) {
                            var container = $("#players");
                            if (data !== "0") {
                                container.empty().append($("<ul>").addClass("m" + val));
                                container.find("ul").append(data);
                            } else {
                                container.empty().append($("<h3>None</h3>"));
                            }
                        });
                    });
                });
            </script>
        </head>
        <body>
            <?php require_once( "../masthead.php" ); ?>

            <div id="content">
                <div id="maincontent" style="margin-bottom:10px;">
                    <div class="innercontent">
                        <div id="milestone-header">
                            <h2>Players with Milestone:</h2>

                            <div class="dropdown dropdown-dark">
                                <select id="cape" class="dropdown-select">
                                    <?php
                                    for ( $i = 1; $i <= $max; $i++ ) {
                                        if ( $i != $max ) {
                                            if ( $i < 10 ) {
                                                ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?>0</option>
                                            <?php
                                            } else {
                                                if ( $i == 10 ) {
                                                    ?>
                                                    <option value="<?php echo $i; ?>">Max</option>
                                                <?php
                                                } else {
                                                    ?>
                                                    <option value="<?php echo $i; ?>">Completionist</option>
                                                <?php
                                                }
                                            }
                                        } else {
                                            if ( $i < 10 ) {
                                                ?>
                                                <option selected="selected" value="<?php echo $i; ?>"><?php echo $i; ?>0</option>
                                            <?php
                                            } else {
                                                if ( $i == 10 ) {
                                                    ?>
                                                    <option selected="selected" value="<?php echo $i; ?>">Max</option>
                                                <?php
                                                } else {
                                                    ?>
                                                    <option selected="selected" value="<?php echo $i; ?>">Completionist</option>
                                                <?php
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div id="players">
                            <ul class="m<?php echo $max; ?>">
                                <?php
                                foreach ( $playerlist as $player ) {
                                    ?>
                                    <li><a href="<?php echo $dbf->basefilepath; ?>calc/<?php echo urlencode( $player[ 'RSN' ] ); ?>"><?php echo $player[ 'RSN' ]; ?></a></li>
                                <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="sidebar">
                    <?php
                    $withmilestone = $dbf->queryToText( "SELECT COUNT(*) FROM apicache WHERE Milestone > 0 AND Active = 1" );
                    $maxedplayers = $dbf->queryToText( "SELECT COUNT(*) FROM apicache WHERE Milestone = 10 AND Active = 1" );
                    $compedplayers = $dbf->queryToText( "SELECT COUNT(*) FROM apicache WHERE Milestone = 11 AND Active = 1" );
                    ?>
                    <div class="stats">
                        <h2>Statistics</h2>

                        <p>Names with Milestones</p>

                        <h3><?php echo number_format( $withmilestone ); ?></h3>

                        <p>Maxed Players</p>

                        <h3><?php echo number_format( $maxedplayers ); ?></h3>

                        <p>Completionist Players</p>

                        <h3><?php echo number_format( $compedplayers ); ?></h3>
                    </div>
                </div>

                

                <div id="footer">
                    <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
                </div>

        </body>
    </html>
<?php
    $dbf->disconnectFromDatabase( $dbf->database );