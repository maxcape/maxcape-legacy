<?php
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

if ( $db[ 'found' ] ) {
    $reqid = mysql_real_escape_string( $_POST[ 'reqid' ] );

    $requirement = $dbf->queryToText( "SELECT Text FROM requirements WHERE RequirementID='$reqid'" );
    $subreqs     = $dbf->getAllAssocResults( "SELECT * FROM subrequirements WHERE RequirementID='$reqid' ORDER BY Text ASC" );
    $type        = $dbf->queryToText( "SELECT CapeType FROM requirements WHERE RequirementID='$reqid'" );

    ?>
    <form id="requirementform" method="post">
        <input type="hidden" value="<?php echo $reqid; ?>" name="reqID"/>
        <fieldset id="req">
            <legend><h3 style="text-align:center; margin:0; padding:0;">Requirement</h3></legend>

            <label class="checkLabel" for="req-delete"><input type="checkbox" id="req-delete" name="req-delete-<? echo $reqid; ?>">Delete Requirement</label>

            <br/><br/>

            <label for="req-text">Text:</label>
            <input type="text" id="req-text" name="req-text-<?php echo $reqid; ?>" value="<?php echo $requirement; ?>"/>

            <br/>

            <label for="type">Type:</label>
            <select id="type" name="req-type-<?php echo $reqid; ?>">
                <option value="1" <?php echo $type == 1 ? "selected='selected'" : false; ?>>Regular</option>
                <option value="2" <?php echo $type == 2 ? "selected='selected'" : false; ?>>Trimmed</option>
            </select>
        </fieldset>

        <br/>

        <fieldset id="subreq">
            <legend><h3 style="text-align:center; margin:0; padding:0;">Subrequirements</h3></legend>
            <div id="subrequirements">
                <?php
                foreach ( $subreqs as $subreq ) {
                    ?>
                    <div class="subrequirement-inner">
                        <label for="sub-number-<?php echo $subreq[ 'SubrequirementID' ]; ?>">Number:</label>
                        <input id="sub-number-<?php echo $subreq[ 'SubrequirementID' ]; ?>" name="sub-number-<?php echo $subreq[ 'SubrequirementID' ]; ?>" style="width:50px;" type="number" min="0"
                               value="<?php echo $subreq[ 'Number' ]; ?>">
                        <label>Text:</label>
                        <input style="width: 250px;" id="sub-text-<?php echo $subreq[ 'SubrequirementID' ]; ?>" name="sub-text-<?php echo $subreq[ 'SubrequirementID' ]; ?>" type="text"
                               value="<?php echo $subreq[ 'Text' ]; ?>">
                        <label for="sub-delete-<?php echo $subreq[ 'SubrequirementID' ]; ?>"><input id="sub-delete-<?php echo $subreq[ 'SubrequirementID' ]; ?>"
                                                                                                    name="sub-delete-<?php echo $subreq[ 'SubrequirementID' ]; ?>" type="checkbox"/>Delete
                        </label>
                    </div>
                <?php
                }
                ?>
            </div>
            <a id="add-sub" href="javascript:void(0);" onclick="addSub();">Add Subrequirement</a>
        </fieldset>
    </form>

    <button class="cancel" onclick="hideblinder()">Cancel</button>
    <button class="accept" onclick="save(); $(this).next('img').css('visibility', 'visible'); $(this).prop('disabled', true); $('.cancel').prop('disabled', true); ">Save Changes</button>
<img src="<?php echo $dbf->basefilepath; ?>images/loader.gif" style="height:20px; float:right; margin-top:2px; visibility:hidden;"/>

    <script>
        $("#req-delete").change(function () {
            $("#requirementform input, #requirementform select").prop("disabled", $(this).prop("checked"));
            $("fieldset#subreq input[type='checkbox']").prop("checked", $(this).prop("checked"));
            $("#add-sub").css("color", $(this).prop("checked") ? "gray" : "#003A61").css("cursor", $(this).prop("checked") ? "default" : "pointer");
            $(".removeLink").css("color", $(this).prop("checked") ? "gray" : "#003A61").css("cursor", $(this).prop("checked") ? "default" : "pointer");
            $(this).prop("disabled", false);
        });

        function save() {
            var formData = $("#requirementform").serialize();

            $.post("saveEdit.php", formData, function () {

                var reqid = <?php echo $reqid; ?>;
                var link = $("#req-link-" + reqid);
                var type, text = $("#req-text").val();
                var oldtype = link.children("span").text();

                $("#type").val() == 1 ? type = "(R)" : type = "(T)";

                link.children("span.type").text(type);
                link.children("span.text").text(text);

                if (type != oldtype) {
                    if (type == "(R)") {
                        link.parent("li").prependTo($("#reg"));
                        $("#reg>li").tsort('span.text');
                    } else {
                        link.parent("li").prependTo($("#trim"));
                        $("#trim>li").tsort('span.text');
                    }
                }

                if ($("#req-delete").prop("checked")) {
                    link.parent("li").remove();
                }
                hideblinder();
            });
        }
    </script>
    <?php
    $dbf->disconnectFromDatabase( $db[ 'handle' ] );
}
else {
    $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    echo "Cannot connect to DB";
}