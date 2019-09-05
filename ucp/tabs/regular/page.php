<?php
    $regular = $dbf->getAllAssocResults("SELECT * FROM requirements WHERE CapeType=1");
?>
<div class="innercontent" id="regular">
    <form id="reg">
        <div id="requirementscontainer">
            <?php
                foreach ($regular as $req) {
                    $reqid = $req['RequirementID'];
                    $subrequirements = $dbf->getAllAssocResults("SELECT s.SubrequirementID, s.Text, s.Number, IFNULL(u.Value, 0) Value FROM subrequirements s LEFT OUTER JOIN userrequirements u ON s.SubrequirementID = u.SubrequirementID AND UserID='$userid' WHERE RequirementID='$reqid'");

                    ?>
                    <div class="requirement" id="req-<?php echo $req['RequirementID']; ?>">
                        <div class="title bluebg">
                            <h2><?php echo $req['Text']; ?></h2>

                            <button class="checkall">All</button>
                        </div>
                        <div class="subrequirements blackbg">
                            <ul>
                                <?php
                                    foreach ($subrequirements as $sub) {
                                        ?>
                                        <li>
                                            <?php
                                                if ($sub['Number'] == 1) {
                                                    ?>
                                                    <div class="squaredThree">
                                                        <input id="sub-<?php echo $sub['SubrequirementID']; ?>" name="sub-<?php echo $sub['SubrequirementID']; ?>"
                                                               type="checkbox" <?php echo $sub['Value'] == 1 ? "checked='checked'" : ""; ?>>
                                                        <label for="sub-<?php echo $sub['SubrequirementID']; ?>"><span><?php echo str_replace("\\", "", $sub['Text']); ?></span></label>
                                                    </div>
                                                <?php
                                                } else {
                                                    ?>
                                                    <input max-value="<?php echo $sub['Number']; ?>" id="sub-<?php echo $sub['SubrequirementID']; ?>"
                                                           name="sub-<?php echo $sub['SubrequirementID']; ?>"
                                                           type="text" value="<?php echo $sub['Value']; ?>">
                                                    <label
                                                        for="sub-<?php echo $sub['SubrequirementID']; ?>">/<?php echo number_format($sub['Number']) . " " . str_replace("\\", "", $sub['Text']); ?></label>
                                                <?php
                                                }
                                            ?>
                                        </li>
                                    <?php
                                    }
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php
                }
            ?>
        </div>

        <div id="btncontainer" class="blackbg">
            <button type="submit">Save Changes</button>
        </div>
    </form>
</div>