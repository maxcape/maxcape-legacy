<?
    require_once("../rsfunctions.php");
    require_once("../dbfunctions.php");
    $r = new rsfunctions;
    $dbf = new dbfunctions;

    $level99 = 13034431;
    $level120 = 104273166;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $skill      = mysql_real_escape_string( $_POST[ 'skill' ] );
        $experience = $_POST[ 'experience' ];

        if ( $skill != "Dungeoneering" && $skill != "Invention" ) {
            $tnl = $r->remainingXP( $experience, $r->getLevel($experience) + 1);

            if ( $experience < $level99 ) {
                $toMax = $level99 - $experience;
            }
            else {
                $toMax = 0;
            }
        }
        else if ($skill == "Dungeoneering") {
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
        } else {
            if($experience < $r->getExperience(99, "invention")) {
                $toMax = $r->getExperience(99, "invention") - $experience;
            } elseif($experience < $r->getExperience(120, "invention")) {
                $toMax = $r->getExperience(120, "invention") - $experience;
            } else {
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
            if($skill != "Invention") {
            ?>

            <table class="skillcalc">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Method</th>
                        <th>Exp Each</th>
                        <th># for <?php echo $r->getLevel($experience) + 1; ?></th>
                        <th># for Max Level</th>
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
                            <?php if($r->getLevel($experience) != 126) { ?>
                            <td><? echo number_format( ceil( $tnl / $methods[ $i ][ 'Experience' ] ) ); ?></td>
                            <?php } ?>
                            <td><? echo number_format( ceil( $toMax / $methods[ $i ][ 'Experience' ] ) ); ?></td>
                        </tr>
                    <?
                    }
                    ?>
                </tbody>
            </table>

        <?
            } else {
                ?>

                <table class="skillcalc">
                    <thead>
                    <tr>
                        <th>Level</th>
                        <th>Method</th>
                        <th>Exp Each</th>
                        <th># for <?php echo $r->getLevel($experience, "invention") + 1; ?></th>
                        <th># for Max Level</th>
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
                                <?php if($r->getLevel($experience, "Invention") != 150) { ?>
                                    <td><? echo number_format( ceil( $tnl / $methods[ $i ][ 'Experience' ] ) ); ?></td>
                                <?php } ?>
                                <td><? echo number_format( ceil( $toMax / $methods[ $i ][ 'Experience' ] ) ); ?></td>
                            </tr>
                        <?
                        }
                    ?>
                    </tbody>
                </table>

            <?
            }

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
