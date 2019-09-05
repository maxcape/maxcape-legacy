<script>
    function deleteCape(id) {
        var cnfrm = confirm("Are you sure you want to delete the cape?\nThere is NO way to undo this action.");

        if (cnfrm) {
            $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/deletecape.php", {"id": id}, function (data) {
                if (data != "") {
                    console.log(data);
                }
            });
        }
    }
</script>