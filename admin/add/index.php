<?php
    session_start();
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "../../userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION['username'];

    if ( !$loggedin ) {
        header( "Location: ../../?noredirect&notloggedin" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $userid      = $_SESSION['userid'];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if ( $permissions < 4 ) {
            header( "Location: ../../?noredirect&permissionerror" );
            die();
        }

        if ( isset( $_POST[ 'submit' ] ) ) {
            $reqText = mysql_real_escape_string( $_POST[ 'req-text' ] );
            $reqType = mysql_real_escape_string( $_POST[ 'req-type' ] );

            $dbf->query( "INSERT INTO requirements ( CapeType, Text ) VALUES ( '$reqType', '$reqText' )" );
            $reqID = mysql_insert_id();

            $subreqNumbers = $_POST[ 'sub-number' ];
            $subreqText    = $_POST[ 'sub-text' ];

            for ( $i = 0; $i < count( $subreqNumbers ); $i++ ) {
                $valid = true;

                if ( $subreqNumbers[ $i ] == "" || $subreqText[ $i ] == "" || !is_numeric( $subreqNumbers[ $i ] ) || $subreqNumbers[ $i ] <= 0 ) {
                    $valid = false;
                }

                if ( $valid ) {
                    $text = mysql_real_escape_string( $subreqText[ $i ] );
                    $num  = mysql_real_escape_string( $subreqNumbers[ $i ] );

                    $dbf->query( "INSERT INTO subrequirements ( RequirementID, Text, Number ) VALUES ( '$reqID', '$text', '$num')" );
                }
            }

            header( "Location: ../edit/?quickview=$reqID" );
            die();
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Add a Requirement</title>
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    var count = 1;
                    function addSub() {
                        if (!$("#req-delete").prop("checked")) {
                            count++;

                            var addon = $("<div>")
                                .attr("class", "subrequirement-inner")
                                .attr("id", "new-sub-" + count);
                            var num = $("<input>")
                                .attr("type", "number")
                                .attr("min", "0")
                                .attr("id", "sub-number-" + count)
                                .attr("name", "sub-number[]")
                                .attr("style", "width: 50px;");
                            var numLabel = $("<label>")
                                .attr("for", "sub-number-" + count)
                                .text("Number: ");
                            var text = $("<input>")
                                .attr("type", "text").attr("id", "sub-text-" + count)
                                .attr("name", "sub-text[]")
                                .attr("style", "width:73.6%;");
                            var textLabel = $("<label>")
                                .attr("for", "sub-text-" + count)
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
                        }
                    }

                    function deleteNewSub(id) {
                        var input = confirm("Are you sure you want to delete this new subrequirement?");

                        if (input) {
                            $("#new-sub-" + id).slideUp(250, function () {
                                $(this).remove();
                            });
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

                <style>
                    #req input[type='text'] {
                        width: 95%;
                    }

                    #req select {
                        width: 95%;
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
            </head>
            <body>
                <?php require_once("../../masthead.php"); ?>

                <div id="content">
                    <div class="innercontent" style="margin-bottom:10px; text-align:center;">
                        <a>New Requirement</a>
                        &bull; <a href="../edit">Edit Requirements</a>
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
                        <form method="post">
                            <fieldset id="req">
                                <legend><h3 style="text-align:center; margin:0; padding:0;">Requirement</h3></legend>
                                <label for="req-text">Text:</label>
                                <input type="text" id="req-text" name="req-text" />

                                <br />

                                <label for="type">Type:</label>
                                <select id="type" name="req-type">
                                    <option value="1">Regular</option>
                                    <option value="2">Trimmed</option>
                                </select>
                            </fieldset>

                            <fieldset id="subreq">
                                <legend><h3 style="text-align:center; margin:0; padding:0;">Subrequirements</h3></legend>
                                <div id="subrequirements">
                                    <div class="subrequirement-inner">
                                        <label for="sub-number-1">Number:</label>
                                        <input id="sub-number-1" name="sub-number[]" style="width:50px;" type="number" min="0">
                                        <label>Text:</label>
                                        <input style="width: 81%;" id="sub-text-1" name="sub-text[]" type="text">

                                    </div>
                                </div>
                                <a id="add-sub" href="javascript:void(0);" onclick="addSub();">Add Subrequirement</a>
                            </fieldset>

                            <input type="submit" name="submit" value="Create Requirement">
                        </form>
                    </div>
                </div>
            </body>
        </html>
        <?php

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "Cannot connect to database.";
    }