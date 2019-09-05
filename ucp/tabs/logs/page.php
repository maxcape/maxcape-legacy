<?php
    $mylogs = $dbf->getAllAssocResults("SELECT * FROM logs WHERE UserID = '" . $_SESSION['userid'] . "'");
?>

<div class="innercontent">
    <h2>My Logs</h2>

    <div id="createlog">
        <form method="post" id="createlogform">
            <fieldset>
                <legend>Create a Log</legend>
                <label for="logname">Log Name</label> <input id="logname" name="logname" type="text" required="required" placeholder="Required"/>

                <label for="logdesc">Log Description</label> <textarea id="logdesc" name="logdesc" placeholder="Optional"></textarea>

                <label>Log Type<i id="ast">*</i></label>

                <div class="optgroup">
                    <label style="width:auto;"><input type="radio" name="logtype[]" value="1" checked="checked"/>Bank Tab</label>
                    <label style="width:auto;"><input type="radio" name="logtype[]" value="2"/>Trip Log</label>
                    <label style="width:auto;"><input type="radio" name="logtype[]" value="3"/>Kill Log</label>
                    <label style="width:auto;"><input type="radio" name="logtype[]" value="4"/>Cumulative</label>
                </div>

                <div style="display:none;" id="cumulative">
                    <label for="cumulativelogs">Logs to include (for cumulative)<br/>&bull; Select one or more (hold ctrl)</label>
                    <select multiple="multiple" id="cumulativelogs" name="cumulativelogs[]">
                        <?php
                            if (count($mylogs) > 0) {
                                foreach ($mylogs as $log) {
                                    ?>
                                    <option value="<?php echo $log['LogID']; ?>"><?php echo $log['LogTitle']; ?></option>
                                <?php
                                }
                            } else {
                                ?>
                                <option value="0">You need at least one non-cumulative log</option>
                            <?php
                            }
                        ?>
                    </select>
                </div>
                <button style="margin-top:5px;" type="submit">Create Log</button>
            </fieldset>
        </form>

        <p><i>*</i> Log Descriptions:</p>
        <ul>
            <li id="logDesc1" class="highlighted">Bank Tab: Acts like an in-game bank tab. Total value of all items displayed at top.</li>
            <li id="logDesc2">Trip Log: Allows you to log individual trips with all loot.</li>
            <li id="logDesc3">Kill Log: Allows you to log individual kills. Keeps track of # of kills.</li>
            <li id="logDesc4">Cumulative: Creates a Bank Tab log that automatically includes all logged drops from selected Trip or Kill logs.</li>
        </ul>

        <h2>My Logs</h2>
        <table border="1" style="margin:0 auto; border-collapse:collapse;">
            <tr>
                <th style="width:250px;">Log title</th>
                <th style="width:250px;">Link</th>
                <th style="width:100px">Delete</th>
            </tr>
            <?php
                $mylogs = $dbf->getAllAssocResults("SELECT * FROM logs WHERE UserID = '" . $_SESSION['userid'] . "'");
                foreach ($mylogs as $log) {
                    ?>
                <tr>
                    <td><?php echo $log['LogTitle']; ?></td>
                    <td style="text-align: center;"><a href="/logs/view/<?php echo $log['LogID']; ?>">View</a></td>
                    <td style="text-align: center;"><a href="javascript:deleteLog(<?php echo $log['LogID'] ?>);">Delete</a></td>
                </tr>
            <?php
                }
            ?>
        </table>
    </div>
</div>