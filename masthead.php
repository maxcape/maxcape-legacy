<link rel="stylesheet" href="/css/smoothness/jquery-ui-1.10.4.custom.css"/>

<?php
    function str_replace_first( $search, $replace, $subject ) {
        $pos = strpos( $subject, $search );
        if ( $pos !== false ) {
            $subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
        }
        return $subject;
    }

    $file = str_replace_first( $dbf->basefilepath, "", $_SERVER[ 'PHP_SELF' ] );
    $filepath = dirname( $file );

    $userid = isset( $_SESSION[ 'userid' ] ) ? $_SESSION[ 'userid' ] : NULL;

    if ( $userid != NULL ) {
        $accesslevel = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid'" );
    } else {
        $accesslevel = 0;
    }
?>

<?php
    if ( !strstr( $filepath, "admin" ) ) {
        ?>
        <script>
            $(document).ready(function () {
                $('.twitter, .facebook, .googleplus, .tumblr').click(function (event) {
                    var width = 575,
                        height = 400,
                        left = ($(window).width() - width) / 2,
                        top = ($(window).height() - height) / 2,
                        url = this.href,
                        opts = 'status=1' +
                            ',width=' + width +
                            ',height=' + height +
                            ',top=' + top +
                            ',left=' + left;

                    window.open(url, 'twitter', opts);

                    return false;
                });

                $(".close").click(function () {
                    $(this).parent().fadeOut();
                });
            });
        </script>
    <?php
    }
    ?>

    <script>
        $(document).ready(function() {
            var list = $('#toolsList');

            list.bind('mouseover', openSubMenu);
            list.bind('mouseout', closeSubMenu);

            if(list.find("li").hasClass("active")) {
                list.addClass("active");
            }

            function openSubMenu() {
                var ddl = $(this).find("ul");

                ddl.css('visibility', 'visible');
                var left = ($(this).offset().left - $(".nav").offset().left);
                var top = ($(this).offset().top - $(".nav").offset().top) + ($(this).height() + 10);
                ddl.css("left", left + "px");
                ddl.css("top", top + "px");
            }

            function closeSubMenu() {
                $(this).find('ul').css('visibility', 'hidden');
            }
        });
    </script>

<?php require_once( "analytics.php" ); ?>

    <div id="masthead">
        <div class="header">

            <?php
                if ( $filepath == "profile" ) {
                    ?>
                    <img class="icon" src="http://services.runescape.com/m=avatar-rs/<?php echo $userdata[ 'RSN' ]; ?>/chat.png">

                    <a style="float:left;" href="<?php echo $dbf->basefilepath; ?>nr"><h1>MAX/COMP CAPE PROFILES</h1></a>
                <?php
                } else {
                    if ( $_GET[ 'beta' ] != 1 ) {
                        ?>
                        <img src="<?php echo $dbf->basefilepath; ?>images/TL_icon.png" class="icon">
                        <a style="float:left;" href="<?php echo $dbf->basefilepath; ?>nr"><h1>MAX/COMP CAPE CALC</h1></a>
                    <?php
                    } else {
                        ?>
                        <div class="logo-1">
                            <div class="bar"></div>
                        </div>
                        <div class="logo-2">
                            <div class="bar"></div>
                        </div>
                        <div class="logo-3">
                            <div class="bar"></div>
                        </div>
                        <a style="float:left;" href="<?php echo $dbf->basefilepath; ?>nr"><h1>Maxcape</h1></a>
                    <?php
                    }
                }
            ?>

            <?php
                if ( $filepath == "calc" ) {
                    ?>
                    <a href="javascript:void(0);" class="s3d addlog"><span class="icon-pencil"></span></a>

                    <a href="http://twitter.com/share?url=<?php echo urlencode( $dbf->getUrl() ); ?>&via=The__Orange&hashtags=RuneScape&text=<?php echo urlencode( "I'm " . number_format( ( ( $maxcapexp - $xpToMaxCape ) / $maxcapexp ) * 100, 2 ) . "% of the way to Max Cape, and " . number_format( ( ( $compcapexp - $xpToCompCape ) / $compcapexp ) * 100, 2 ) . "% of the way to Comp Cape!" ) ?>"
                       class="s3d twitter"> <span class="icon-twitter"></span> </a>

                    <a href="http://www.tumblr.com/share/link?url=<?php echo urlencode( $dbf->getUrl() ); ?>&name=<?php echo urlencode( "$username - Max/Comp Cape Calculator" ); ?>&description=<?php echo urlencode( "I'm " . number_format( ( ( $maxcapexp - $xpToMaxCape ) / $maxcapexp ) * 100, 2 ) . "% of the way to Max Cape, and " . number_format( ( ( $compcapexp - $xpToCompCape ) / $compcapexp ) * 100, 2 ) . "% of the way to Comp Cape!" ) ?>"
                       class="s3d tumblr"> <span class="icon-tumblr"></span> </a>

                    <a href="http://www.facebook.com/sharer.php?s=100&p[title]=<?php echo urlencode( "Check out my RuneScape Max/Comp Cape progress!" ); ?>&p[summary]=<?php echo urlencode( "I'm " . number_format( ( ( $maxcapexp - $xpToMaxCape ) / $maxcapexp ) * 100, 2 ) . "% of the way to Max Cape, and " . number_format( ( ( $compcapexp - $xpToCompCape ) / $compcapexp ) * 100, 2 ) . "% of the way to Comp Cape!" ) ?>&p[url]=<?php echo urlencode( $dbf->getUrl() ); ?>&p[images][0]="
                       class="s3d facebook"> <span class="icon-facebook"></span> </a>


                    <a href="https://plus.google.com/share?url=<?php echo urlencode( $dbf->getUrl() ); ?>" class="s3d googleplus"> <span class="icon-google-plus"></span> </a>
                <?php
                } elseif ( !strstr( $filepath, "admin" ) ) {
                    ?>
                    <div class="social">
                        <a href="javascript:void(0);" class="s3d addlog"><span class="icon-pencil"></span></a>

                        <a href="http://twitter.com/share?url=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>&via=The__Orange&hashtags=RuneScape&text=<?php echo urlencode( "RuneScape Max/Completionist Cape Calculator - " ) ?>"
                           class="s3d twitter"> <span class="icon-twitter"></span> </a>

                        <a href="http://www.tumblr.com/share/link?url=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>&name=<?php echo urlencode( "RuneScape Max/Completionist Cape Calculator" ); ?>&description=<?php echo urlencode( "The Max/Completionist Cape Calculator is a RuneScape tool that will help you figure out just how far (or close) you are from maxing your skills." ) ?>"
                           class="s3d tumblr"> <span class="icon-tumblr"></span> </a>

                        <a href="http://www.facebook.com/sharer.php?s=100&p[title]=<?php echo urlencode( "RuneScape Max/Completionist Cape Calculator" ); ?>&p[summary]=<?php echo urlencode( "The Max/Completionist Cape Calculator is a RuneScape tool that will help you figure out just how far (or close) you are from maxing your skills." ); ?> &p[url]=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>"
                           class="s3d facebook"> <span class="icon-facebook"></span> </a>

                        <a href="https://plus.google.com/share?url=<?php echo urlencode( "http://www.maxcape.com/nr" ); ?>" class="s3d googleplus"> <span class="icon-google-plus"></span> </a>

                    </div>
                <?php
                }
            ?>

        </div>

        <div class="nav">
            <ul>
                <li <?php echo $filepath == "." || $filepath == "\\" ? "class='active'" : ""; ?> style="margin-right:-4px;"><a style="padding:5px 15px;" href="<?php echo $dbf->basefilepath; ?>nr"><span class="icon-home"></span></a></li>
                <li id="toolsList">
                    <ul class="navdropdown" style="visibility: hidden;">
                        <li <?php echo $filepath == "designer" ? "class='active'" : ""; ?>>
                            <a href="<?php echo $dbf->basefilepath; ?>designer/">Designer</a></li>
                        <li <?php echo $filepath == "logs" || $filepath == "logs/view" ? "class='active'" : ""; ?>>
                            <a href="<?php echo $dbf->basefilepath; ?>logs/">Logs</a></li>
                        <li <?php echo $filepath == "sig" ? "class='active'" : ""; ?>>
                            <a href="<?php echo $dbf->basefilepath; ?>sig/">Signatures</a></li>
                        <li <?php echo $filepath == "milestones" ? "class='active'" : ""; ?>>
                            <a href="<?php echo $dbf->basefilepath; ?>milestones/">Milestones</a></li>
                    </ul>
                    <a href="javascript:void(0);" style="display:block;">Tools <span class="icon-double-angle-down"></span></a>

                <?php
                    if ( $loggedin ) {
                        ?>
                        <li <?php echo $filepath == "profile" && $username == $myusername ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>profile/<?php echo $myusername; ?>">My Profile</a>
                    <?php
                    } else {
                        ?>
                        <li <?php echo $filepath == "login" && $action == "login" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>user/login">Login</a>
                        <li <?php echo $filepath == "login" && $action == "register" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>user/register">Register</a>
                    <?php
                    }
                ?>
                <li <?php echo $filepath == "search" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>search/">Search Profiles</a>
                <?php
                    if ( $loggedin ) {
                        ?>
                        <li <?php echo $filepath == "ucp" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/0">User Control Panel</a>
                    <?php
                    }

                    if ( $accesslevel > 3 ) {
                        ?>
                        <li <?php echo strstr( $filepath, "admin" ) ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>admin/">Admin</a>
                    <?php
                    }
                ?>
                <li <?php echo $filepath == "forums" ? "class='active'" : ""; ?>><a href="<?php echo $dbf->basefilepath; ?>forums/">Forums</a>
                <li class="nostyle">


                    <?php
                        if ( $filepath == "calc" ) {
                            ?>
                            <form action="/calc/" method="get" onsubmit="(function(e) { e.preventDefault(); document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#name').val().replace(/ /g, '+'); })(event)">

                                <?php
                                    if ( isset( $_COOKIE[ 'maxcompcapename' ] ) && $_COOKIE[ 'maxcompcapename' ] == $username ) {
                                        ?>
                                        <label id="defnameBtn" class="defnameset"onclick="defname('<?php echo $username; ?>');">&#x2713;</label>
                                    <?php
                                    } else {
                                        ?>
                                        <label id="defnameBtn" class="defnamenotset" onclick="defname('<?php echo $username; ?>');">&#x2717;</label>
                                    <?php
                                    }
                                ?>
                                <input type="text" name="name" id="name" placeholder="Search another name" required="required">
                                <label class="searchbtn" onclick="(function(e) { e.preventDefault(); document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#name').val().replace(/ /g, '+'); })(event)"><i
                                        class="icon-search"></i></label>
                            </form>
                        <?php
                        } else {
                            ?>
                            <form style="" action="<?php echo $dbf->basefilepath; ?>calc/" method="get" onsubmit="(function(e) { e.preventDefault(); document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#name').val().replace(/ /g, '+'); })(event)">
                                <label for="name">RSN</label>
                                <input type="text" name="name" id="name" placeholder="Search..." required="required">
                                <label class='searchbtn' onclick="(function(e) { e.preventDefault(); document.location = '<?php echo $dbf->basefilepath; ?>calc/' + $('#name').val().replace(/ /g, '+'); })(event)"><i
                                        class="icon-search"></i></label>
                            </form>
                        <?php
                        }
                    ?></li>
            </ul>
        </div>

    </div>

    <div class="latestNews">
        <?php
            $latest = $dbf->queryToAssoc( "SELECT * FROM posts WHERE Visible=1 ORDER BY Date DESC LIMIT 1" );
        ?>
        <h3>Latest News: <a href="<?php echo $dbf->basefilepath; ?>post/<?php echo $latest[ 'PostID' ]; ?>"><?php echo $latest[ 'Headline' ]; ?></a></h3>

        <?php
            if ( $loggedin ) {
                ?>
                <div id="user-options">
                    <p>Welcome, <?php echo $myusername; ?> | <a href="<?php echo $dbf->basefilepath; ?>user/logout">Logout</a></p>
                </div>
            <?php
            }
        ?>
    </div>

    <script src="<?php echo $dbf->basefilepath; ?>js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="<?php echo $dbf->basefilepath; ?>js/timer.js"></script>
    <div id="timer" class="minimized blackbg">
        <div id="timer-header">
            <span id="running-icon" class="icon-time"></span>

            <span>Global Timer</span>

            <i class="icon-double-angle-up"></i>
        </div>

        <div id="timer-container">
            <span id="minutes">00</span><span id="seperator">:</span><span id="seconds">00</span>
        </div>

        <div id="timer-tools">
            <label for="minutes-set">Min:</label>
            <input type="text" id="minutes-set" value="0">
            <button type="button" onclick="startTimer(); toggleTimer(true);" id="startbtn">Start</button>
            <label for="seconds-set">Sec:</label>
            <input type="text" id="seconds-set" value="0">
            <button type="button" onclick="stopTimer();">Stop</button>
        </div>
    </div>

    <div id="sounddummy"></div>

    <script>
        var running = localStorage.getItem("running");

        if (localStorage.getItem("timerState") == "up") {
            toggleTimer(false);
        }

        if (running == "1") {
            startTimer();
        }

        $("#timer-header").click(function () {
            toggleTimer(true);
        });
    </script>

<?php
    $donator = $dbf->queryToText( "SELECT Donator FROM users WHERE UserID='$userid'" );

    if ( $donator == 1 ) {
        ?>
        <style>
            .ad {
                display: none !important;
            }
        </style>
    <?php
    }


    $mylogs = $dbf->getAllAssocResults("SELECT * FROM logs WHERE LogType != 4 AND UserID='" . $_SESSION['userid'] . "'");
?>

<div id="addlogblinder">
    <div id="addlogcontent" class="innercontent">
        <?php
            if($loggedin) {
                if(count($mylogs) == 0) {
                    ?>
                    <script>
                        $(document).ready(function() {
                            $("#addtologform").find("*").attr("disabled", true);
                        });
                    </script>
                    <?php
                }
        ?>
        <h2 style="width:100%; text-align:center;">Logs</h2>
        <p style="text-align: center; margin:0;"><a href="/ucp/tab/6">Create a Log</a></p>
        <form method="post" id="addtologform">
            <fieldset>
                <legend>Add to Log</legend>
                <label for="addtolog">Add to Log</label>
                <select id="addtolog">
                    <option value="">Select a log</option>
                    <?php
                        foreach ($mylogs as $log) {
                            if($filepath == "logs/view") {
                                $vid = $_GET['logid'];
                            } else {
                                $vid = 0;
                            }
        ?>
                            <option <?php echo $log['LogID'] == $vid ? "selected='selected'" : "" ?> value="<?php echo $log['LogID']; ?>" data-logtype="<?php echo $log['LogType']; ?>"><?php echo $log['LogTitle']; ?></option>
                        <?php
    }
?>
                </select>

                <div id="logtype-1">
                    <label for="itemname">Item Name</label>
                    <input type="text" id="itemname" placeholder="Item Name"/>

                    <label for="itemnumber">Amount</label>
                    <input type="text" id="itemnumber" placeholder="#"/>

                    <button type="submit">Add to log</button>
                </div>

                <div id="logtype-2-3" style="display:none;">
                    <label for="itemNameTB">
                        <input type="text" placeholder="Item Name" style="float:left; width:130px; margin-top:0;" id="itemNameTB"/>
                        <input type="text" placeholder="#" style="float:left; clear:left; margin-top:-4px; width:130px;" id="itemNumberTB"/>
                        <button type="button" style="float:left;" onclick="addToItemList();">Add to List</button>
                        <button type="button" style="float:left; margin-top:4px;" onclick="removeSelected();">Remove Selected</button>
                    </label>
                    <select size="10" id="items"></select>

                    <button type="submit">Add to log</button>
                </div>
            </fieldset>
        </form>

        <div id="tipscontainer">
            <p>Tips:</p>
            <ul>
                <li>If amount is set to a number (I.E '123'), it will set that item to that amount.</li>
                <li>If amount is set to + or - a number (I.E '+10'/'-10'), it will add or subtract from that items current amount.</li>
            </ul>
        </div>
        <?php
            } else {
        ?>
                <h2 style="text-align:center; width:100%;">Logs</h2>
                <p>Logs are a great way to keep track of your drops, character value, or anything between. This feature is only available to users, so <a href="user/register">register for free now!</a></p>
                <p>Features:</p>
                <ul>
                    <li>Four types of logs:
                        <ul>
                            <li>Bank Tab: Keep track of all the items in your bank and their value</li>
                            <li>Kill Log: Log all your drops individually</li>
                            <li>Trip Log: Log all the drops from a trip</li>
                            <li>Cumulative: Create a Bank Tab that includes drops from selected other logs</li>
                        </ul>
                    </li>
                    <li>Share your logs with your friends</li>
                    <li>View others logs</li>
                    <li>Keep track of your value without having to check each items price</li>
                    <li>Easily add to any log from any page via the pencil icon in the top right.</li>
                </ul>

                <p>Still not convinced? <a href="/logs/">View other users logs here</a></p>

                <p>Already have an account? <a href="/user/login">Click here to login</a>.</p>
        <?php
            }
        ?>
    </div>
</div>

<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>

<script>
    function centeraddLog() {
        //Center the container on the screen.
        var target = $("#addlogcontent"),
            h = target.height() + 20,
            w = target.width() + 20,
            leftPos = (($(window).width() / 2) - (w / 2)),
            topPos = (($(window).height() / 2) - (h / 2));

        target.css("left", leftPos + "px").css("top", topPos + "px");
    }

    function addToItemList() {
        var item = $("#itemNameTB").val(),
            number = $("#itemNumberTB").val();

        if($("#items option[data-item='" + item + "']").length == 0) {
            var option = $("<option>").attr("value", $("#items").find("option").length + 1).attr("data-item", item).attr("data-number", number).text(item + " [" + number + "]");
            $("#items").append(option);
        } else {
            var opt = $("#items option[data-item='" + item + "']");
            opt.attr("data-number", number).text(item + " [" + number + "]");
        }

        $("#itemNameTB").val("");
        $("#itemNumberTB").val("");
    }

    function removeSelected() {
        var select = $("#items"),
            selected = select.val();

        var opt = $("#items option[value='" + selected + "']");

        opt.remove();
    }

    $(document).ready(function() {
        var itemlist = [];
        var type = "";
        var id = "";

        $.get("/ucp/scripts/generateItemList.php", function(data) {
            itemlist = data.split(",");

            $("#itemname, #itemNameTB").autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(itemlist, request.term);
                    response(results.slice(0, 10));
            }});
        });

        $(".addlog").click(function() {
            $("#addlogblinder").css("display", "block");
            centeraddLog();
        });

        $("#addlogblinder").click(function() {
            $("#addlogblinder").css("display", "none");
        });

        $("#addlogcontent").click(function(e) {
            e.stopPropagation();
        });

        $(window).resize(function() {
            centeraddLog();
        });

        $("#items").change(function() {
            var selected = $(this).val();
            var option = $("#items option[value='" + selected + "']");

            $("#itemNameTB").val(option.attr("data-item"));
            $("#itemNumberTB").val(option.attr("data-number"));
        });

        $("#addtolog").change(function() {
            var selected = $("#addtolog option[value='" + $(this).val() + "']");
            type = selected.attr("data-logtype");
            id = selected.val();

            if(type == "1") {
                $("#logtype-1").css("display", "block");
                $("#logtype-2-3").css("display", "none");
                $("#tipscontainer").css("display","block");
            } else if (type == "2") {
                $("#logtype-1").css("display", "none");
                $("#logtype-2-3").css("display", "block");
                $("#tipscontainer").css("display","none");
            } else if (type == "3") {
                $("#logtype-1").css("display", "none");
                $("#logtype-2-3").css("display", "block");
                $("#tipscontainer").css("display","none");
            } else {

            }
        });


        $("#addtolog").trigger("change");

        $("#addtologform").submit(function(e) {
            e.preventDefault();
            var data = {};

            if(id != "") {
                if(type == 1) {
                    data = {
                        "id": id,
                        "items": [
                            {
                                "item": $("#itemname").val(),
                                "amount": $("#itemnumber").val()
                            }
                        ]
                    };
                } else {
                    var items = [];
                    $("#items").find("option").each(function() {
                        var name = $(this).attr("data-item"),
                            amount = $(this).attr("data-number");
                        items.push({"item": name, "amount": amount});
                    });

                    data = {
                        "id": id,
                        "items": items
                    }
                }

                if(data.items != [] && data.items != "") {
                    $.post("/ucp/scripts/addToLog.php", data, function(data){
                        $("#items").find("option").each(function() {
                            $(this).remove();
                        });
                        $("#itemNameTB").val("");
                        $("#itemNumberTB").val("");
                        $("#itemname").val("");
                        $("#itemnumber").val("");
                    });
                } else {
                    alert("You must add at least one item to the drop list");
                }
            } else {
                alert("Please select a log to add this drop to");
                $("#addtolog").focus();
            }
        });

        $('#addtologform').keypress(function(e) {
            if(e.which == 13) { // Checks for the enter key
                 // Stops IE from triggering the button to be clicked
                if($("#itemNameTB").is(":focus") || $("#itemNumberTB").is(":focus")) {
                    e.preventDefault();
                    addToItemList();
                }
            }
        });
    });
</script>