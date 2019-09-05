<?php
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

    if ( $db[ 'found' ] ) {
        $skill  = mysql_real_escape_string( $_POST[ 'skill' ] );
        $level  = mysql_real_escape_string( $_POST[ 'level' ] );
        $method = mysql_real_escape_string( $_POST[ 'method' ] );
        $expea  = mysql_real_escape_string( $_POST[ 'expea' ] );

        $dbf->query( "INSERT INTO skillcalcs (SkillID, LevelRequirement, Name, Experience) VALUES ('$skill', '$level', '$method', '$expea')" );

        $statistics = $dbf->getAllAssocResults( "SELECT s.Number, s.SkillID, s.Name, COUNT(sc.SkillID) AS SkillTotal FROM skillcalcs sc RIGHT OUTER JOIN skills s ON sc.SkillID = s.SkillID GROUP BY 1" );

        foreach ( $statistics as $statistic ) {
            ?>
            <li onclick="showSkill(<?php echo $statistic["SkillID"]; ?>);">
                <span style="color:#FFF;"><?php echo $statistic[ "Name" ]; ?></span> <span class="datetext"><span
                        style="color:<?php echo $statistic[ "SkillTotal" ] > 0 ? "#0F0" : "#F00"; ?>"><?php echo $statistic[ "SkillTotal" ]; ?> Training Methods</span></span>
            </li>
        <?php
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "Cannot connect to database";
    }