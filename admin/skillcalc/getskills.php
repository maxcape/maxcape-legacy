<?php
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

    if ( $db[ 'found' ] ) {
        $skillid = mysql_real_escape_string( $_POST[ 'skillid' ] );

        $trainingmethods = $dbf->getAllAssocResults( "SELECT * FROM skillcalcs WHERE SkillID='$skillid' ORDER BY LevelRequirement ASC" );

        ?>
        <form id="save" method="post">
            <?php
            foreach ( $trainingmethods as $trainingmethod ) {
                ?>
            <fieldset>
                <legend><?php echo $trainingmethod["Name"]; ?></legend>

                <label class="delete"><input type="checkbox" name="del-<?php echo $trainingmethod["SkillCalcID"]; ?>" id="del-<?php echo $trainingmethod["SkillCalcID"]; ?>"> Delete this method</label>

                <label for="tm-<?php echo $trainingmethod['SkillCalcID']; ?>">Training Method:</label>
                <input type="text" id="tm-<?php echo $trainingmethod['SkillCalcID']; ?>" name="tm-<?php echo $trainingmethod['SkillCalcID']; ?>" value="<?php echo $trainingmethod["Name"]; ?>">

                <label for="lr-<?php echo $trainingmethod['SkillCalcID']; ?>">Level Requirement:</label>
                <input type="number" max="99" min="1" step="1" id="lr-<?php echo $trainingmethod['SkillCalcID']; ?>" name="lr-<?php echo $trainingmethod['SkillCalcID']; ?>" value="<?php echo $trainingmethod["LevelRequirement"]; ?>">

                <label for="ee-<?php echo $trainingmethod['SkillCalcID']; ?>">Exp Each:</label>
                <input type="text" id="ee-<?php echo $trainingmethod['SkillCalcID']; ?>" name="ee-<?php echo $trainingmethod['SkillCalcID']; ?>" value="<?php echo $trainingmethod["Experience"]; ?>">
            </fieldset>
            <?php
            }
            ?>

            <div class="buttoncontainer">
                <button type="submit">Save Changes</button>
                <button type="button" onclick="hideblinder();">Cancel</button>
            </div>
        </form>

        <script>
            $("#save").submit(function(e) {
                e.preventDefault();

                $.post("edit.php", $(this).serialize(), function(data) {
                    $("#sidebar").find("ul").each(function() {
                        $(this).empty().append(data);
                    });
                    hideblinder();
                })
            });

            $(".delete").find("input").each(function() {
                $(this).change(function() {
                    if($(this).prop("checked")) {
                        $(this).parents("fieldset").find("input[type='text'], input[type='number']").each(function() {
                            $(this).prop("disabled", true);
                        });
                    } else {
                        $(this).parents("fieldset").find("input[type='text'], input[type='number']").each(function() {
                            $(this).prop("disabled", false);
                        });
                    }
                });
            });
        </script>
        <?php

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "Cannot connect to database";
    }