<?php
    session_start();
    require_once( "../../dbfunctions.php" );

    $dbf = new dbfunctions;

    require_once( "../../userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];
    $userid = $_SESSION[ 'userid' ];

    if ( !$loggedin ) {
        header( "Location: ../../nr?notloggedin" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Unable to connect to database" );

    $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
    if ( $permissions < 4 ) {
        header( "Location: ../../nr?permissionerror" );
        die();
    }

?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Manage Flags</title>

            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
            <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/admin.css">

            <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

            <script>
                function keep(id) {
                    $.post("test.php", function(data) {
                        console.log(data);
                    });

                    $.post("flaghandler.php", {"id": id, "do": 1}, function(data) {
                        console.log(data);
                        var tr = $("#" + id);
                        tr.remove();
                    });
                }

                function del(id) {
                    $.post("flaghandler.php", {"id": id, "do": 0}, function(data) {
                        console.log(data);
                        var tr = $("#" + id);
                        tr.remove();
                    });
                }
            </script>
        </head>

        <body>
            <?php require_once( "../../masthead.php" ); ?>

            <div id="content">
                <div class="innercontent" style="margin-bottom:10px; text-align:center;">
                    <a href="../add">New Requirement</a>
                    &bull; <a href="../edit">Edit Requirements</a>
                    &bull; <a href="../skillcalc">Skill Calc Entries</a>
                    &bull; <a>Manage Designer Flags</a>
                    <?php
                    if ( $permissions > 1 ) {
                        ?>
                        &bull; <a href="../post">Manage Front Page Posts</a>
                    <?php
                    }
                    ?>
                </div>

                <div class="innercontent">
                    <table cellspacing="0">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Colors</th>
                            <th># of flags</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        $flags = $dbf->getAllAssocResults( "SELECT * FROM capes WHERE CapeID IN (SELECT CapeID FROM capeflags ORDER BY Date ASC)" );

                        if(count($flags) == 0) {
                        ?>
                            <tr>
                                <td colspan="5">There are currently no flagged capes.</td>
                            </tr>
                            <?php
                        }

                        foreach ( $flags as $i => $flagged ) {
                            $capeid = $flagged[ 'CapeID' ];
                            $colors = $dbf->getAllAssocResults( "SELECT H, S, L FROM capecolors cc JOIN colors c ON c.ColorID = cc.ColorID WHERE CapeID = '$capeid'" );
                            ?>
                            <tr id="<?php echo $capeid; ?>">
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo $flagged['Title']; ?></td>
                                <td>
                                    <?php
                                    foreach ( $colors as $color ) {
                                        ?>
                                        <div class="microcolor" style="background-color:hsl(<?php echo $color['H']; ?>, <?php echo $color['S']; ?>%, <?php echo $color['L']; ?>%);"></div>
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td>1</td>
                                <td>
                                    <a href="#" onclick="keep(<?php echo $capeid; ?>);" title="Keep"><span class="icon-check-sign"></span></a>
                                    <a href="#" onclick="del(<?php echo $capeid; ?>);" title="Remove"><span class="icon-remove-sign"></span></a>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
            </div>
        </body>
    </html>

<?php

    $dbf->disconnectFromDatabase( $db[ 'handle' ] );