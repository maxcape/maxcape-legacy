<?php
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

    $methods = array(
        "tm"  => "name",
        "lr"  => "level",
        "ee"  => "exp",
        "del" => "del"
    );

    for ( $i = 0; $i < count( $_POST ); $i++ ) {
        $key  = key( $_POST );
        $data = explode( "-", $key );
        $do   = $methods[$data[ 0 ]];
        $id   = $data[ 1 ];
        $val  = $_POST[ $key ];

        $tmdata = $dbf->queryToAssoc("SELECT * FROM skillcalcs WHERE SkillCalcID='$id'");

        if($do == "name") {
            $dbf->query("UPDATE skillcalcs SET Name='$val' WHERE SkillCalcID='$id'");
        } else if ($do == "level") {
            $dbf->query("UPDATE skillcalcs SET LevelRequirement='$val' WHERE SkillCalcID='$id'");
        } else if ($do == "exp") {
            $dbf->query("UPDATE skillcalcs SET Experience='$val' WHERE SkillCalcID='$id'");
        } else if ($do == "del") {
            $dbf->query("DELETE FROM skillcalcs WHERE SkillCalcID='$id'");
        }

        next( $_POST );
    }

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