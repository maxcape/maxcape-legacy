<?php
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database." );

    $milestone = mysql_real_escape_string( $_POST[ 'milestone' ] );

    $playerlist = $dbf->getAllAssocResults( "SELECT RSN FROM apicache WHERE Milestone='$milestone'" );

    if ( count( $playerlist ) > 0 ) {
        foreach ( $playerlist as $player ) {
            ?>
            <li><a href="<?php echo $dbf->basefilepath; ?>calc/<?php echo urlencode( $player[ 'RSN' ] ); ?>"><?php echo $player[ 'RSN' ]; ?></a></li>
        <?php
        }
    } else {
        echo 0;
    }

    $dbf->disconnectFromDatabase( $db[ 'handle' ] );