<?php
    error_reporting( 0 );
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $search = ucwords( mysql_real_escape_string( $_POST[ 'search' ] ) );

        $result = $dbf->getAllAssocResults( "SELECT Username, RSN, ProfileViews, ProfileVisible
										FROM users
										WHERE RSN LIKE '%$search%'
										OR Username LIKE '%$search%'" );
        ?>
        <table>
            <tr>
                <th>Username</th>
                <th>Character Name</th>
                <th>Profile Views</th>
            </tr>

            <?php
            for ( $i = 0; $i < count( $result ); $i++ ) {

                if ( $result[ $i ][ 'ProfileVisible' ] != 0 ) {
                    $username = str_replace( $search, '<b>' . $search . '</b>', $result[ $i ][ 'Username' ] );
                    $username = str_replace( strtolower( $search ), '<b>' . strtolower( $search ) . '</b>', $username );

                    $rsn = str_replace( $search, '<b>' . $search . '</b>', $result[ $i ][ 'RSN' ] );
                    $rsn = str_replace( strtolower( $search ), '<b>' . strtolower( $search ) . '</b>', $rsn );
                    ?>
                    <tr onclick="goToProfile('<?php echo $result[ $i ][ 'Username' ]; ?>');">
                        <td><?php echo $username; ?></td>
                        <td><?php echo $rsn; ?></td>
                        <td><?php echo $result[ $i ][ 'ProfileViews' ]; ?></td>
                    </tr>
                <?php
                }
            }
            ?>
        </table>
        <?php
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
