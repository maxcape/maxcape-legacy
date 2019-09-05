<script>
    $(document).ready(function () {
        $("#requirementscontainer").masonry({
            itemSelector: '.requirement',
            columnWidth: function (containerWidth) {
                return containerWidth / 2;
            }
        });

        $(".checkall").click(function (e) {
            e.preventDefault();
            var container = $(this).parents(".requirement").children(".subrequirements");

            if ($(this).text() == "All") {
                container.find("input").each(function () {
                    if ($(this).attr("type") == "text") {
                        $(this).attr("old-value", $(this).val());
                        $(this).val($(this).attr("max-value"));
                    } else if ($(this).attr("type") == "checkbox") {
                        $(this).attr("old-value", $(this).prop("checked"));
                        $(this).prop("checked", true);
                    }

                });

                $(this).text("Undo");
            } else {
                container.find("input").each(function () {
                    if ($(this).attr("type") == "text") {
                        if ($(this).attr("old-value") !== null) {
                            $(this).val($(this).attr("old-value"));
                        }
                        $(this).prop("disabled", false);
                    } else if ($(this).attr("type") == "checkbox") {
                        if ($(this).attr("old-value") !== null) {
                            var checked = ($(this).attr("old-value") === "true");
                            $(this).prop("checked", checked);
                        }
                    }


                });

                $(this).text("All");
            }
        });

        $(".subrequirements input").change(function () {
            var all = $(this).parents(".requirement").children(".title").find("button");

            if (all.text() == "Undo") {
                all.text("All");
            }
        });

        $("#main input[type='text']").on('input', function () {
            var boxvalue = $(this).val();

            if (isNaN(boxvalue.charAt(boxvalue.length - 1))) {
                $(this).val(boxvalue.slice(0, -1));
            }

            if (parseInt($(this).val()) > $(this).attr("max-value")) {
                $(this).val($(this).attr("max-value"));
            }
        });

        $("#<?php echo $tab == 2 ? "reg" : "trim"; ?>").submit(function (e) {
            e.preventDefault();

            var button = $("#btncontainer").find("button");
            button.prop("disabled", true).empty().append("<img src='<?php echo $dbf->basefilepath; ?>images/loader.gif'>");

            var form = $(this).serializeArray();
            form.push({name: "type", value: <?php echo $tab == 2 ? 1 : 2; ?>});

            $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/savereqs.php", form, function (data) {
                button.empty().append("<span>Saved!</span>");
                button.find("span").delay(500).fadeOut(500, function () {
                    button.prop("disabled", false).append("Save Changes");
                });
            })
        });
    });

    $(window).scroll(function () {
        var top = $(this).scrollTop();
        var container = $("#btncontainer");
        var newtop = 0;

        if (top < 425) {
            newtop = 350;
        } else {
            newtop = top - 120;
        }

        container.css("top", newtop + "px");
    });
</script>