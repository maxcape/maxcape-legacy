<script src="<?php echo $dbf->basefilepath; ?>js/jscolor/jscolor.js"></script>
<script>
    function savemisc() {
        var hidersn = $("input[name='showrsn']:checked").val();
        var sigbgcolor = $("#sigbgcolor").val();
        var sigtxtcolor = $("#sigtxtcolor").val();
        var commentbgcolor = $("#commentbgcolor").val();

        var button = $("#savemiscbtn");

        button.prop("disabled", true).empty().append("<img src='<?php echo $dbf->basefilepath; ?>images/loader.gif'>");

        $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/savemisc.php", {"rsnvis": hidersn, "sigbg": sigbgcolor, "sigtxt": sigtxtcolor, "cmntbg": commentbgcolor}, function (data) {
            if (data != "") {
                console.log(data);
            }

            button.empty().append("<span>Saved!</span>");
            button.find("span").delay(500).fadeOut(500, function () {
                button.prop("disabled", false).append("Save Changes");
            });
        })
    }

    function test() {
        alert("Test")
    }
</script>