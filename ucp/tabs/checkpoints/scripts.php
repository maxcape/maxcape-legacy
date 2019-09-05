<script>
    //Scripts for Tab 1 -- Checkpoints
    $(document).ready(function () {
        $("#checkpointselect").change(function () {
            $(".tablecontainer").empty().append("<img src='<?php echo $dbf->basefilepath; ?>images/load.gif'>");
            $(this).prop("disabled", true);
            $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/generatetable.php", {
                checkpointid: $(this).val()
            }, function (data) {
                $("#checkpointselect").prop("disabled", false);
                $(".tablecontainer").empty().append(data);
            })
        });
    });

    function createCheckpoint() {
        $("#newcheckpoint").prop("disabled", true).empty().prepend("<img src='<?php echo $dbf->basefilepath; ?>images/loader.gif'>");
        $.ajax({
            url: "<?php echo $dbf->basefilepath; ?>ucp/scripts/newcheckpoint.php"
        }).done(function (data) {
            $("#newcheckpoint").prop("disabled", false).empty().prepend("New Checkpoint");
            alert(data);
        });
    }

    function setDefault(id) {

        $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/setdefault.php", {
            checkpointid: id
        }, function () {

        });
    }
</script>