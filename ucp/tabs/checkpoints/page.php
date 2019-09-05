<?php
    $checkpoints = $dbf->getAllAssocResults("SELECT * FROM checkpoints WHERE UserID='$userid'");
    $firstcheckpointid = $checkpoints[0]['CheckpointID'];
    $firstcheckpointdate = $checkpoints[0]['Time'];
?>
<div class="innercontent" id="checkpoints">
    <div class="selectcontainer">
        <button id="newcheckpoint" onclick='createCheckpoint();'>New Checkpoint</button>
        <select id="checkpointselect">
            <?php
                if (count($checkpoints) > 0) {
                    foreach ($checkpoints as $i => $checkpoint) {
                        ?>
                        <option value="<?php echo $checkpoint['CheckpointID']; ?>"><?php echo date('F jS Y', strtotime($checkpoint['Time'])); ?></option>
                    <?php
                    }
                } else {
                    ?>
                    <option>No checkpoints available</option>
                <?php
                }
            ?>
        </select>
    </div>

    <div class="tablecontainer">
        <?php
            $checkpoint = $dbf->getAllAssocResults("SELECT Rank, Level, Experience, s.Name AS Skillname FROM checkpointdata c JOIN skills s ON s.SkillID = c.SkillID WHERE CheckpointID = '$firstcheckpointid' ");
        ?>
        <h4>Viewing Checkpoint from: <?php echo date('F jS Y', strtotime($firstcheckpointdate)); ?></h4>
        <button onclick="setDefault(<?php echo $firstcheckpointid; ?>);">Set as Default</button>
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
                foreach ($checkpoint as $skill) {
                    ?>
                    <tr>
                        <td><?php echo $skill['Skillname']; ?></td>
                        <td><?php echo $skill['Level']; ?></td>
                        <td><?php echo number_format($skill['Experience']); ?></td>
                        <td><?php echo ($skill['Rank'] == "") ? "???" : number_format($skill['Rank']); ?></td>
                    </tr>
                <?php
                }
            ?>
            </tbody>
        </table>
    </div>