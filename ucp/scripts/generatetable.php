<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if($db['found']) {
        $checkpointid = mysql_real_escape_string($_POST[ 'checkpointid' ]);
        $checkpointdata = $dbf->queryToAssoc("SELECT * FROM checkpoints WHERE CheckpointID='$checkpointid'");
        $checkpoint = $dbf->getAllAssocResults("SELECT Rank, Level, Experience, s.Name As Skillname
                                                FROM checkpointdata c
                                                JOIN skills s
                                                    ON s.SkillID = c.SkillID
                                                WHERE CheckpointID = '$checkpointid'
                                                ");
        ?>
        <h4>Viewing Checkpoint from: <?php echo date( 'F jS Y', strtotime( $checkpointdata['Time'] ) ); ?></h4>
        <button onclick="setDefault(<?php echo $checkpointid; ?>);">Set as Default</button>
        <table border="1">
            <thead>
                <tr>
                    <th>Skill</th>
                    <th>Level</th>
                    <th>Experience</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($checkpoint as $skill) {
                    ?>
                    <tr>
                        <td><?php echo $skill['Skillname']; ?></td>
                        <td><?php echo $skill['Level']; ?></td>
                        <td><?php echo number_format($skill['Experience']); ?></td>
                        <td><?php echo ($skill['Rank'] == "") ? "??" : number_format($skill['Rank']); ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
<?php

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }