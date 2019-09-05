<script>
    var logtypedata = 0;
    $(document).ready(function() {
        $("input:radio").change(function() {
            if($(this).attr("name") == "logtype[]") {
                //alert($(this).val());
                $("li[id*='logDesc']").removeClass("highlighted");
                $("#logDesc" + $(this).val()).addClass("highlighted");

                logtypedata = $(this).val();

                if($(this).val() == 4) {
                    $("#cumulative").slideDown();
                } else {
                    $("#cumulative").slideUp();
                }
            }
        });

        $("#createlogform").submit(function(e) {
            e.preventDefault();

            var data = $(this).serialize();

            if($("#cumulativelogs").val() != "" && logtypedata == 4) {
                $.post("/ucp/scripts/createlog.php", data, function(data) {
                    location.reload();
                });
            } else if(logtypedata != 4) {
                $.post("/ucp/scripts/createlog.php", data, function(data) {
                    location.reload();
                });
            } else {
                alert("Please select one or more logs for your cumulative log.");
            }
        });
    });

    function deleteLog(id) {
        var con = confirm("Are you sure you want to delete this log and all its contents?\nThere is NO way to reverse this action.");

        if(con) {
            $.post("/ucp/scripts/deleteLog.php", {"logid": id}, function() {
                location.reload();
            });
        }
    }
</script>