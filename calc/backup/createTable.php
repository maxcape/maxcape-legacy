<?
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;

    require_once( "../rsfunctions.php" );
    $r = new rsfunctions;

    $level99 = 13034431;
    $level120 = 104273166;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $skill      = mysql_real_escape_string( $_POST[ 'skill' ] );
        $experience = $_POST[ 'experience' ];

        $tnl = $r->getExperience( $r->getLevel( $experience ) + 1 ) - $experience;

        if ( $skill != "Dungeoneering" ) {
            if ( $experience < $level99 ) {
                $toMax = $level99 - $experience;
            }
            else {
                $toMax = 0;
            }

        }
        else {
            if ( $r->getLevel( $experience ) < 99 ) {
                if ( $experience < $level99 ) {
                    $toMax = $level99 - $experience;
                }
                else {
                    $toMax = 0;
                }
            }
            else if ( $r->getLevel( $experience ) > 99 && $r->getLevel( $experience ) < 120 ) {
                if ( $experience < $level120 ) {
                    $toMax = $level120 - $experience;
                }
                else {
                    $toMax = 0;
                }
            }
            else {
                $toMax = 0;
            }
        }

        $methods = $dbf->getAllAssocResults( "SELECT *
                                            FROM skillcalcs
                                            WHERE SkillID = (
                                                SELECT SkillID
                                                FROM skills
                                                WHERE Name = '$skill'
                                            )
                                            ORDER BY LevelRequirement ASC, Experience ASC" );

        if ( count( $methods ) > 0 ) {
            ?>

            <table class="skillcalc">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Method</th>
                        <th>Exp Each</th>
                        <th># for TNL</th>
                        <th># for Max</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    for ( $i = 0; $i < count( $methods ); $i++ ) {
                        ?>
                        <tr>
                            <td><? echo $methods[ $i ][ 'LevelRequirement' ]; ?></td>
                            <td><? echo $methods[ $i ][ 'Name' ]; ?></td>
                            <td><? echo number_format( $methods[ $i ][ 'Experience' ], 1 ); ?></td>
                            <td><? echo number_format( ceil( $tnl / $methods[ $i ][ 'Experience' ] ) ); ?></td>
                            <td><? echo number_format( ceil( $toMax / $methods[ $i ][ 'Experience' ] ) ); ?></td>
                        </tr>
                    <?
                    }
                    ?>
                </tbody>
            </table>

        <?
        }
        else {
            ?>
            <h1>No data</h1>
        <?
        }
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "Unable to connect to database!";
    }
