<?php
    require_once "../dbfunctions.php";
    require_once "../userfunctions.php";
    $dbf = new dbfunctions();
    $uf = new userfunctions();

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION['username'];

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Logs</title>
        <link rel="stylesheet" href="/css/base/wrapper.css"/>
        <link rel="stylesheet" href="/css/special/logs.css"/>
        <link rel="stylesheet" href="/designer/css/jquery.mCustomScrollbar.css"/>

        <script src="/js/jquery.js"></script>
        <script src="/designer/js/jquery.mCustomScrollbar.concat.min.js"></script>

        <style>
            #preview-headers {
                margin-top:1%;
                width:100%;
                overflow:hidden;
                margin-bottom:10px;
            }

            #preview-headers h2 {
                text-align:center;
                margin:0 2% 0 0;
                float:left;
                padding:1%;
                width:30%;
                border-radius:5px;

                background: #efefef;
                background: linear-gradient(top, #efefef 0%, #bbbbbb 100%);
                background: -moz-linear-gradient(top, #efefef 0%, #bbbbbb 100%);
                background: -webkit-linear-gradient(top, #efefef 0%,#bbbbbb 100%);

                color:#1C1C1C;
            }

            #preview-headers h2:last-of-type {
                margin:0;
            }

            .preview-container {
                width:32%;
                margin-right:2%;
                padding:5px 0;
                float:left;
                height:235px;
                border-radius:5px;

                background: #4b545f;
                background: linear-gradient(top, #4f5964 0%, #5f6975 40%);
                background: -moz-linear-gradient(top, #4f5964 0%, #5f6975 40%);
                background: -webkit-linear-gradient(top, #4f5964 0%,#5f6975 40%);
                color:#1C1C1C;
            }

            .preview-container:last-of-type {
                margin-right:0;
            }

            .scroll {
                position:relative;
                width:100%;
                height:100%;
            }

            .listitem {
                position:relative;
                height:30px;
                line-height:30px;
                padding-left:2px;
                cursor:pointer;
            }

            .listitem:nth-of-type(even) {
                background-color:rgba(125,125,125, 0.3);
            }

            .listitem:hover {
                background-color: #044A70;
                background-image: linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -o-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -moz-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -webkit-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -ms-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                color: #AAA;
            }

            #mylogs .logname, #myfaves .logname {
                width:70% !important;
            }

            #mylogs .logtype, #myfavs .logtype {
                width:27% !important;
            }

            .logname {
                display:inline-block;
                width:45%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size:80%;
            }

            .authorname {
                display:inline-block;
                width: 27%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size:80%;
            }

            .logtype {

                display:inline-block;
                width:25%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size:80%;
            }

            .vcent p {
                width: 100%;
                padding: 0;
                margin: 0;
                text-align: center;
            }

            .vcent a {
                color:#003A61;
            }

            #search {
                float:right;
            }

            #descriptioncontent, #searchresults {
                clear:both;
                padding-top:1px;
            }

            #searchresults {
                height:328px;
                overflow-y: scroll;
            }

            #searchresults table {
                border-collapse:collapse;
            }

            #searchresults table th {
                text-align:center;
            }

            #searchresults table td {
                width:25%;
                text-align:left;
                word-break: normal;
                cursor:pointer;
            }

            #searchresults table td:nth-of-type(2) {
                width:44%;
            }

            #searchresults table td:nth-of-type(3) {
                width:20%;
            }

            #searchresults table td:nth-of-type(4) {
                width:15%;
            }

            #searchresults table tr:nth-of-type(odd):not(:first-of-type) {
                background-color:rgba(125,125,125,0.4);
            }

            #searchresults table tr:hover:not(:first-of-type) {
                background-color: #044A70;
                background-image: linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -o-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -moz-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -webkit-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                background-image: -ms-linear-gradient(bottom, #044A70 11%, #003A61 50%);
                color: #AAA;
            }

            #closesearches {
                color:darkred;
                cursor:pointer;
            }
        </style>
    </head>
    <body>
        <?php require_once "../masthead.php"; ?>

        <div id="content">
            <div id="maincontent">
                <div class="innercontent">
                    <h2 style="float:left;">Drop Logs</h2>

                    <div id="search">
                        <i style="display:none;" onclick="closeSearches();" class="icon-remove" id="closesearches"></i>
                        <input type="text" placeholder="Search Logs" id="searchtb"/>
                        <button id="searchbtn">Search</button>
                    </div>

                    <div id="descriptioncontent">
                        <p>Logs are a great way to keep track of your drops, character value, or anything between. This feature is only available to users, so <a href="user/register">Register</a> or <a href="#">Login</a>.</p>
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

                        <p>You can create a log in the <a href="/ucp/tab/6">User Control Panel</a>, under &quot;My Logs&quot;.</p>
                    </div>
                    <div id="searchresults" style="display:none;">

                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="stats">
                    <?php
                        $totallogs = $dbf->queryToText("SELECT COUNT(*) FROM logs");
                        $uniqueusers = $dbf->queryToText("SELECT COUNT(DISTINCT UserID) FROM logs");
                    ?>
                    <h2>Statistics</h2>
                    <p>Total Logs Created</p>
                    <h3><?php echo number_format($totallogs); ?></h3>

                    <p>Users with Logs</p>
                    <h3><?php echo number_format($uniqueusers); ?></h3>
                </div>
            </div>

            <div class="sidebar" style="margin-top:8px; height:261px;">
                <div class="ad">
                    <?php $dbf->ad("5808301204"); ?>
                </div>
            </div>

        </div>
        <div id="preview-headers">
            <h2>Most Recent</h2>
            <h2>My Logs</h2>
            <h2>My Favorites</h2>
        </div>

        <div style="overflow:hidden;">
        <div class="preview-container" id="mostrecent">
            <div class="scroll">
                <?php
                    $mostrecent = $dbf->getAllAssocResults("SELECT * FROM logs l JOIN users u ON l.UserID = u.UserID ORDER BY CreationDate DESC LIMIT 100");

                    foreach($mostrecent as $log) {
                        $logtype = $log['LogType'];
                        switch($logtype) {
                            case 1:
                                $logtype = "Bank Tab";
                                break;
                            case 2:
                                $logtype = "Trip Log";
                                break;
                            case 3:
                                $logtype = "Kill Log";
                                break;
                            case 4:
                                $logtype = "Cumulative";
                                break;
                        }
                        ?>
                        <div class="listitem" onclick="redirectToLog(<?php echo $log['LogID']; ?>);">
                            <span class="logname"><?php echo $log['LogTitle']; ?></span>
                            <span class="authorname"><?php echo $log['Username']; ?></span>
                            <span class="logtype"><?php echo $logtype; ?></span>
                        </div>
                    <?php
                    }
                ?>
            </div>
        </div>
        <div class="preview-container" id="mylogs">
            <?php
                if(!$loggedin) {
                    ?>
                    <div class="vcent">

                        <p>You must be logged in to create a log</p>
                        <p><a href="../user/login">Login</a> or <a href="../user/register/">Register</a></p>
                    </div>
            <?php
                } else {
                    $mylogs = $dbf->getAllAssocResults("SELECT * FROM logs WHERE UserID = '" . $_SESSION['userid'] . "'");

                    if(count($mylogs) > 0) {
                        foreach($mylogs as $log) {
                            $logtype = $log['LogType'];
                            switch($logtype) {
                                case 1:
                                    $logtype = "Bank Tab";
                                    break;
                                case 2:
                                    $logtype = "Trip Log";
                                    break;
                                case 3:
                                    $logtype = "Kill Log";
                                    break;
                                case 4:
                                    $logtype = "Cumulative";
                                    break;
                            }
                            ?>
                            <div class="listitem" onclick="redirectToLog(<?php echo $log['LogID']; ?>);">
                                <span class="logname"><?php echo $log['LogTitle']; ?></span>
                                <span class="logtype"><?php echo $logtype; ?></span>
                            </div>
                        <?php
                        }
                    } else {
                        ?>
                        <div class="vcent">
                            <p>You have no logs</p>
                            <p><a href="/ucp/tab/6">Click here</a> to create a log</p>
                        </div>
                    <?php
                    }
                }
            ?>
        </div>
        <div class="preview-container" id="myfaves">
            <?php
                if(!$loggedin) {
                    ?>
                    <div class="vcent">
                        <p>You must be logged in to favorite logs</p>
                        <p><a href="../user/login">Login</a> or <a href="../user/register/">Register</a></p>
                    </div>
                <?php
                } else {
                    $myfavorites = $dbf->getAllAssocResults("SELECT * FROM logsfavorites lf JOIN logs l ON lf.LogID = l.LogID WHERE lf.UserID = '" . $_SESSION['userid'] . "'");

                    if(count($myfavorites) > 0) {
                        foreach($myfavorites as $log) {
                            $logtype = $log['LogType'];
                            switch($logtype) {
                                case 1:
                                    $logtype = "Bank Tab";
                                    break;
                                case 2:
                                    $logtype = "Trip Log";
                                    break;
                                case 3:
                                    $logtype = "Kill Log";
                                    break;
                                case 4:
                                    $logtype = "Cumulative";
                                    break;
                            }
                            ?>
                            <div class="listitem" onclick="redirectToLog(<?php echo $log['LogID']; ?>);">
                                <span class="logname"><?php echo $log['LogTitle']; ?></span>
                                <span class="logtype"><?php echo $logtype; ?></span>
                            </div>
                        <?php
                        }
                    } else {
                        ?>
                        <div class="vcent">
                            <p>You have no favorite logs.</p>
                        </div>
            <?php
                    }
                }
            ?>
        </div>
        </div>
        <script>
            function redirectToLog(id) {
                location.href = "view/" + id;
            }

            function closeSearches() {
                $("#closesearches").css("display", "none");
                $("#searchresults").css("display", "none");
                $("#descriptioncontent").css("display", "block");
                $("#searchtb").val("");
            }

            $(document).ready(function() {
                $(".scroll").mCustomScrollbar({
                    theme: "light",
                    scrollInertia: 0,
                    scrollButtons: {
                        enable: true
                    },
                    advanced: {
                        updateOnContentResize: true
                    }
                });

                $(".vcent").each(function() {
                    var msg = $(this),
                        msgH = msg.height(),
                        sb = msg.parent(),
                        sbH = sb.height();

                    var topMargin = Math.round((sbH / 2) - (msgH / 2));

                    msg.css("margin-top", topMargin + "px");
                });

                $("#searchbtn").click(function() {
                    var query = $("#searchtb").val();

                    $.post("/ucp/scripts/searchLogs.php", {"query": query}, function(data) {
                        $("#descriptioncontent").css("display", "none");
                        $("#searchresults").empty().append(data).css("display", "block");
                        $("#closesearches").css("display", "inline");
                    });
                });

                $('#searchtb').keypress(function(e) {
                    if(e.which == 13) { // Checks for the enter key
                        // Stops IE from triggering the button to be clicked
                        e.preventDefault();

                        $("#searchbtn").trigger("click");
                    }
                });

                $(document).keyup(function(e) {
                    if(e.keyCode == 27) {
                        e.preventDefault();

                        if($("#searchresults").css("display") == "block") {
                            closeSearches();
                        }
                    }
                });
            });
        </script>
        <div id="footer">
            <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date('Y'); ?></p>
        </div>

    </body>
</html>