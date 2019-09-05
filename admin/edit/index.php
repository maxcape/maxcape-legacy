<?php
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "../../userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];

    if ( !$loggedin ) {
        header( "Location: ../../?noredirect&notloggedin" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $userid      = $_SESSION[ 'userid' ];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if ( $permissions < 4 ) {
            header( "Location: ../../?noredirect&permissionerror" );
            die();
        }

        $requirements = $dbf->getAllAssocResults( "SELECT * FROM requirements ORDER BY CapeType ASC, Text ASC" );
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Edit Requirements</title>
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <style>
                    label {
                        -webkit-user-select: none;
                        -moz-user-select: none;
                        user-select: none;
                    }

                    li {
                        text-align: left;
                    }

                    ul.columns {
                        -webkit-column-width: 225px;
                        -webkit-column-gap: 10px;
                        -webkit-column-rule: none !important;

                        -moz-column-width: 225px;
                        -moz-column-gap: 10px;
                        -moz-column-rule: none !important;

                        column-width: 225px;
                        column-gap: 10px;
                        column-rule: none !important;
                    }

                    span.type {
                        color: red;
                        font-weight: bold;
                    }

                    span.firsttrim {
                        margin-top: 15px;
                    }

                    .blinder {
                        position: fixed;
                        top: 0;
                        left: 0;
                        height: 100%;
                        width: 100%;
                        z-index: 9998;
                        background-color: rgba(0, 0, 0, 0.4);
                        visibility: hidden;
                    }

                    .centered {
                        position: absolute;
                        z-index: 9999;
                        box-shadow: 0px 0px 12px 5px #000;
                    }

                    .selected {
                        font-weight: bold;
                    }

                    .accept {
                        float: right;
                    }

                    .cancel {
                        float: right;
                    }

                    #req label {
                        display: inline-block;
                        width: 15%;
                    }

                    .checkLabel {
                        display: inline !important;
                    }

                    #req input[type='text'] {
                        width: 82%;
                    }

                    #req select {
                        width: 83.4%;
                    }

                    fieldset {
                        box-shadow: 0px 4px 9px 0px #BBB;
                    }

                    #subreq {
                        margin-bottom: 5px;
                    }

                    #add-sub {
                        float: right;
                        clear: both;
                        margin-top: 6px;
                    }

                    .removeLink {
                        color: #003A61;
                        font-size: 90%;
                        text-decoration: underline;
                        cursor: pointer;
                        margin-left: -3px;
                    }
                </style>

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.tinysort.min.js"></script>

                <script>
                    var count = 0;

                    $(window).resize(function () {
                        if ($(".blinder").css("visibility") != "hidden") {
                            center();
                        }
                    });

                    function center() {
                        var target = $("#container"),
                            h = target.height() + 20,
                            w = target.width() + 20,
                            leftPos = (($(window).width() / 2) - (w / 2)),
                            topPos = (($(window).height() / 2) - (h / 2));

                        target.css("left", leftPos + "px").css("top", topPos + "px");
                    }

                    function popup(element, id) {
                        $(element).addClass("selected");
                        $(".blinder").css("visibility", "visible");


                        $.post("getreqinfo.php", {
                            reqid: id
                        }, function (data) {
//                            var old_h = $("#container").height(), old_w = $("#container").width();
                            $("#container").empty().append(data);
                            center();
//                            $("#requirementform").hide().fadeIn(1000);
//                            var new_h = $("#container").height(), new_w = $("#container").width();
//
//                            $("#container").css("height", old_h + "px").css("width", old_w + "px").animate({
//                                width: new_w + "px",
//                                height: new_h + "px"
//                            }, {
//                                duration: 250,
//                                step: function () {
//                                    center();
//                                }
//                            }, function () {
//                                center();
//                            });
                        });
                    }

                    function hideblinder() {
                        $(".selected").removeClass("selected");
                        $("#container").empty().append('<img style="margin-top:4px;" src="<?php echo $dbf->basefilepath; ?>images/load.gif" />').css("width", "auto").css("height", "auto");
                        $(".blinder").css("visibility", "hidden");
                        count = 0;
                    }

                    function addSub() {
                        if (!$("#req-delete").prop("checked")) {
                            count++;

                            var addon = $("<div>")
                                .attr("class", "subrequirement-inner")
                                .attr("id", "new-sub-" + count);
                            var num = $("<input>")
                                .attr("type", "number")
                                .attr("min", "0").attr("id", "new-number-" + count)
                                .attr("name", "new-number-n[]").attr("style", "width: 50px;");
                            var numLabel = $("<label>")
                                .attr("for", "sub-new-number" + count)
                                .text("Number: ");
                            var text = $("<input>")
                                .attr("type", "text").attr("id", "new-text-" + count)
                                .attr("name", "new-text-n[]")
                                .attr("style", "width:250px;");
                            var textLabel = $("<label>")
                                .attr("for", "sub-new-text-" + count)
                                .text(" Text: ");
                            var del = $("<label>")
                                .attr("onmouseover", "highlight(" + count + ")")
                                .attr("onmouseout", "revert(" + count + ")")
                                .attr("class", "removeLink")
                                .append(
                                    $("<input>")
                                        .attr("style", "visibility:hidden;")
                                        .attr("id", "new-sub-delete-" + count)
                                        .attr("type", "checkbox")
                                        .attr("onclick", "deleteNewSub(" + count + ")")
                                );

                            $("#subrequirements").append(addon.append(numLabel).append(num).append(textLabel).append(text).append(del.append("Remove").prepend("")));
                            $("#new-sub-" + count).hide().slideDown(250);

                            var oldH = $("#container").height();
                            var newH = oldH + 26;
                            $("#container").animate({
                                height: newH + "px"
                            }, {
                                duration: 250,
                                step: function () {
                                    center();
                                }
                            });
                            center();
                        }
                    }

                    function deleteNewSub(id) {
                        var input = confirm("Are you sure you want to delete this new subrequirement?");

                        if (input) {
                            $("#new-sub-" + id).slideUp(250, function () {
                                $(this).remove();
                            });
                            var oldH = $("#container").height();
                            var newH = oldH - 26;
                            $("#container").animate({
                                height: newH + "px"
                            }, {
                                duration: 250,
                                step: function () {
                                    center();
                                }
                            });
                            center();
                        } else {
                            $("#new-sub-delete-" + id).prop("checked", false);
                        }
                    }

                    function highlight(id) {
                        if (!$("#req-delete").prop("checked")) {
                            $("#new-sub-" + id).css("background-color", "red");
                        }
                    }

                    function revert(id) {
                        $("#new-sub-" + id).css("background-color", "transparent");
                    }
                </script>
            </head>
            <body>
                <?php require_once( "../../masthead.php" ); ?>

                <div id="content">
                    <div class="innercontent" style="margin-bottom:10px; text-align:center;">
                        <a href="../add">New Requirement</a>
                        &bull; <a>Edit Requirements</a>
                        &bull; <a href="../skillcalc">Skill Calc Entries</a>
                        &bull; <a href="../flags">Manage Designer Flags</a>
                        <?php
                        if ( $permissions > 1 ) {
                            ?>
                            &bull; <a href="../post">Manage Front Page Posts</a>
                        <?php
                        }
                        ?>
                    </div>

                    <div class="innercontent">
                        <h1 style="text-align:center; margin:0; padding:0;">Edit Requirements</h1>

                        <h3 style="text-align:center; margin:0; padding:0;">Choose what requirement you want to edit</h3>


                        <ul class="columns" id="reg">
                            <?
                            $lastwasreg = true;

                            for ( $i = 0; $i < count( $requirements ); $i++ ) {

                            $requirements[ $i ][ 'CapeType' ] == 1 ? $type = "R" : $type = "T";

                            if ( $type == "T" && $lastwasreg ) {
                            ?>
                        </ul>
                        <hr>
                        <ul class="columns" id="trim">
                            <?
                            $lastwasreg = false;
                            }
                            ?>
                            <li>
                                <a id="req-link-<?php echo $requirements[ $i ][ 'RequirementID' ]; ?>" href="javascript:void(0);" onclick="popup(this, <? echo $requirements[ $i ][ 'RequirementID' ]; ?>)">
                                    <? echo "<span class='type'>(" . $type . ")</span> <span class='text'>" . $requirements[ $i ][ 'Text' ] . "</span>"; ?>
                                </a>
                            </li>
                            <?
                            }
                            ?>
                        </ul>
                    </div>
                </div>

                <div class="blinder">
                    <div id="container" class="centered innercontent">
                        <img style="margin-top:4px;" src="<?php echo $dbf->basefilepath; ?>images/load.gif"/>
                    </div>
                </div>

            </body>
            <?php
            if ( isset( $_GET[ 'quickview' ] ) ) {
                ?>
                <script>

                    $("#req-link-<?php echo mysql_real_escape_string($_GET['quickview']); ?>").click();

                </script>
            <?php
            }
            ?>
        </html>
        <?
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        ?>
        <h1>Cannot find or connect to database</h1>
    <?
    }