<script>
    //Scripts for Tab 0 -- Account
    $(document).ready(function () {
        $("#acct").submit(function (e) {
            e.preventDefault();

            $("#acctsave").prop("disabled", true).empty().prepend("<img src='<?php echo $dbf->basefilepath; ?>images/loader.gif'>");

            $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/saveacct.php", $("#acct").serialize(), function () {
                $("#acctsave").prop("disabled", false).empty().prepend("Save Changes");
            });
        });

        $("#pswd").submit(function (e) {
            e.preventDefault();

            $("#changepswd").prop("disabled", true).empty().prepend("<img src='<?php echo $dbf->basefilepath; ?>images/loader.gif'>");

            $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/changepswd.php", $("#pswd").serialize(), function (data) {
                $("#changepswd").prop("disabled", false).empty().prepend("Change Password");
                switch (data) {
                    case "0":
                        $("#pswd")[0].reset();
                        $("#currentpswd").focus();
                        break;
                    case "1":
                        alert("User does not exist"); //This should _never_ happen!
                        break;
                    case "2":
                        alert("Current password is incorrect");
                        $("#currentpswd").focus();
                        break;
                    case "3":
                        alert("Passwords do not match");
                        $("#newpswd").focus();
                        break;
                }
            });
        });
    });

    function logout() {
        window.location = "<?php echo $dbf->basefilepath; ?>user/logout";
    }

    function overrideCache() {
        $("#ovrbtn").prop("disabled", true).empty().prepend("<img src='<?php echo $dbf->basefilepath; ?>images/loader.gif'>");

        $.post("<?php echo $dbf->basefilepath; ?>ucp/scripts/override.php", {}, function (data) {

            console.log(data);


            switch (data) {
                case "0":
                    $("#ovrbtn").empty().text("4 hours remaining");
                    break;
                case "1":
                    $("#ovrbtn").empty().text("Error");
                    alert("You have already overridden your cache in the last 4 hours.");
                    break;
                case "2":
                    $("#ovrbtn").empty().text("Error");
                    alert("Your stats do not need to be overridden. Please just use the calc normally.");
                    break;
                case "3":
                    $("#ovrbtn").empty().text("Error");
                    alert("Unable to read users id. Please log out and back in.");
                    break;
                case "4":
                    $("#ovrbtn").empty().text("Error");
                    alert("It looks like you aren't logged in. Please log in.");
            }
        });
    }
</script>