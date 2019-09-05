<?php
    session_start();
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "../../userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];
    $page_title = "Skill Calculators - Add a Training Method";

    if ( !$loggedin ) {
        header( "Location: ../../?noredirect&notloggedin" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

    if ( $db[ 'found' ] ) {
        $userid      = $_SESSION[ 'userid' ];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if ( $permissions < 4 ) {
            header( "Location: ../?noredirect&permissionerror" );
            die();
        }

        $statistics = $dbf->getAllAssocResults( "SELECT s.Number, s.SkillID, s.Name, COUNT(sc.SkillID) AS SkillTotal FROM skillcalcs sc RIGHT OUTER JOIN skills s ON sc.SkillID = s.SkillID GROUP BY 1" );
        $skills     = $dbf->getAllAssocResults( "SELECT * FROM skills ORDER BY Number" );

        if ( isset( $_GET[ 'skill' ] ) ) {
            $selectedSkill = $_GET[ 'skill' ];
        }

        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Skill Calculators - Add a Training Method</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                <link rel="stylesheet" href="skillcalc.css">

                <style>
                    .sidebar p {
                        text-align:center;
                        margin:0;
                        width:100%;
                        clear:both;
                        color:white;
                    }

                    .sidebar ul {
                        overflow:hidden;
                        float:left;
                    }
                    .sidebar ul li {
                        width: 50%;
                        float: left;
                        position: relative;
                    }

                    .sidebar ul li:nth-of-type(odd) {
                        width: 49.6%;
                        border-right: 1px solid #777;
                    }

                    .sidebar ul li:nth-of-type(even) {
                        width: 49.6%;
                        border-left: 1px solid #333;
                    }

                    .sidebar ul li:nth-of-type(25) {
                        border-bottom: none;
                    }
                </style>

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    function set() {
                        var val = $("#skill").val();

                        location.href = "?skill=" + val;
                    }

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

                    function popup(id) {
                        $(".blinder").css("visibility", "visible");
                        center();

                        $.post("getskills.php", {
                            skillid: id
                        }, function (data) {
                            var container = $("#container");

                            var old_h = container.height(), old_w = container.width();
                            container.empty().append(data);
                            $("#requirementform").hide().fadeIn(1000);
                            var new_h = container.height(), new_w = container.width();

                            container.css("height", old_h + "px").css("width", old_w + "px").animate({
                                width: new_w + "px",
                                height: new_h + "px"
                            }, {
                                duration: 250,
                                step: function () {
                                    center();
                                },
                                complete: function () {
                                    $(".buttoncontainer").css("width", "100%");
                                    center();
                                }
                            });
                        });
                    }

                    function hideblinder() {
                        $("#container").empty().append('<img style="margin-top:4px;" src="<?php echo $dbf->basefilepath; ?>images/load.gif" />').css("width", "auto").css("height", "auto");
                        $(".blinder").css("visibility", "hidden");
                    }

                    function showSkill(id) {
                        popup(id);
                    }

                    $(document).ready(function () {
                        $("#skillcalcadd").submit(function (e) {
                            e.preventDefault();

                            $(this).find("button").each(function () {
                                $(this).prop("disabled", true);
                                $(this).empty().append("<img src='../../images/loader.gif'>");
                            });

                            $.post("add.php", $(this).serialize(), function (data) {
                                $("#skillcalcadd").find("button").each(function () {
                                    $(this).prop("disabled", false);
                                    $(this).empty().text("Submit");
                                });

                                $("#reset").click();
                                $("#level").focus();

                                $("#sidebar").find("ul").each(function () {
                                    $(this).empty().append(data);
                                });
                            });
                        });
                    });
                </script>
            </head>

            <body>
                <?php require_once( "../../masthead.php" ); ?>

                <div id="content">
                    <div class="innercontent" style="margin-bottom:10px; text-align:center;">
                        <a href="../add">New Requirement</a>
                        &bull; <a href="../edit">Edit Requirements</a>
                        &bull; <a>Skill Calc Entries</a>
                        &bull; <a href="../flags">Manage Designer Flags</a>
                        <?php
                        if ( $permissions > 1 ) {
                            ?>
                            &bull; <a href="../post">Manage Front Page Posts</a>
                        <?php
                        }
                        ?>
                    </div>

                    <div id="maincontent">
                        <div class="innercontent">
                            <h2 style="text-align:center; display:block;">Skill Calculators</h2>

                            <form method="post" id="skillcalcadd">
                                <label for="skill">Skill:</label>
                                <select name="skill" id="skill">
                                    <?
                                    for ( $i = 1; $i < count( $skills ); $i++ ) {
                                        if ( $skills[ $i ][ "SkillID" ] != $selectedSkill ) {
                                            ?>
                                            <option value="<? echo $skills[ $i ][ "SkillID" ]; ?>"><? echo $skills[ $i ][ "Name" ]; ?></option>
                                        <?
                                        } else {
                                            ?>
                                            <option value="<? echo $skills[ $i ][ "SkillID" ]; ?>" selected="selected"><? echo $skills[ $i ][ "Name" ]; ?></option>
                                        <?
                                        }
                                    }
                                    ?>
                                </select> <a href="javascript:void(0)" onclick="set();">Set</a>

                                <label for="level">Level:</label>
                                <input type="number" id="level" name="level" value="1" min="1" max="99" step="1" required="required">

                                <label for="method">Method Name:</label>
                                <input type="text" id="method" name="method" required="required">

                                <label for="expea">XP Each:</label>
                                <input type="text" id="expea" name="expea" required="required">

                                <button type="submit" name="submit">Add Method</button>

                                <input type="reset" id="reset" style="display:none;">
                            </form>
                        </div>
                    </div>

                    <div class="sidebar" id="sidebar">
                        <h2>Skill Calcs</h2>
                        <p>Click on a skill to edit</p>
                        <ul>
                            <?php
                            foreach ( $statistics as $statistic ) {
                                ?>
                                <li onclick="showSkill(<?php echo $statistic[ "SkillID" ]; ?>);">
                                    <span style="color:#FFF;"><?php echo $statistic[ "Name" ]; ?></span> <span class="datetext"><span
                                            style="color:<?php echo $statistic[ "SkillTotal" ] > 0 ? "#0F0" : "#F00"; ?>"><?php echo $statistic[ "SkillTotal" ]; ?> Training Methods</span></span>
                                </li>
                            <?php
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
        </html>
        <?php
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "cannot connect to database";
    }