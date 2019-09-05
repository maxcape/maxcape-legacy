<?php
    require_once "../dbfunctions.php";
    $dbf = new dbfunctions;


    if (!isset($_GET['member']) || $_GET['member'] == "") {
        $memberView = "All";


        $updates = $dbf->getAllAssocResults("SELECT MAX(StatUpdateID) AS StatUpdateID, TimeFetched, Finished FROM statupdates WHERE Finished = 1 GROUP BY MAKEDATE(YEAR(TimeFetched), DAYOFYEAR(TimeFetched))");
        //$updates = array_reverse($updates);
    } else {
        $memberView = mysql_real_escape_string($_GET['member']);


        $updates = $dbf->getAllAssocResults("SELECT * FROM statupdates WHERE Finished = 1 AND StatUpdateID > 1");
    }

    if ($memberView == "All") {
        $clanmembers = $dbf->getAllAssocResults("SELECT * FROM clanmembers WHERE NameChanged = 0");
    } else {
        $clanmembers = $dbf->getAllAssocResults("SELECT * FROM clanmembers WHERE LOWER(RSN)= LOWER('$memberView')");
    }

    $skillIDs = array(
        "Overall"       => 0, "Attack" => 1, "Defence" => 2, "Strength" => 3, "Constitution" => 4, "Ranged" => 5, "Prayer" => 6, "Magic" => 7, "Cooking" => 8,
        "Woodcutting"   => 9, "Fletching" => 10, "Fishing" => 11, "Firemaking" => 12, "Crafting" => 13, "Smithing" => 14, "Mining" => 15, "Herblore" => 16,
        "Agility"       => 17, "Thieving" => 18, "Slayer" => 19, "Farming" => 20, "Runecrafting" => 21, "Hunter" => 22, "Construction" => 23, "Summoning" => 24,
        "Dungeoneering" => 25, "Divination" => 26
    );

    if (!isset($_GET['skill'])) {
        $skillView = "Overall";
    } else {
        $skillView = mysql_real_escape_string($_GET['skill']);
    }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stat Tracking</title>

    <link rel="stylesheet" href="../styles.css"/>
    <style>
        #chart {
        <?php
            if($memberView == "All") {
            ?> height: 750px;
        <?php
            } else {
            ?> height: 500px;
        <?php
            }
        ?>
        }
    </style>

    <script src="../jquery.js"></script>
</head>
<body>
<div id="header">
    <div class="pagewidth">
        <h1>Iron Dragon - Iron Man Clan</h1>
    </div>
</div>

<div id="nav">
    <div class="pagewidth">
        <ul>
            <li><a href="/IronClan/">Home</a></li>
            <li><a href="../hs/">Clan Highscores</a></li>
            <li><a href="../tracking/">Stat Tracking</a></li>
            <li><a href="http://www.reddit.com/r/ironclan" target="_blank">Subreddit</a></li>
            <li><a href="../rules/">Rules</a></li>
        </ul>
    </div>
</div>

<div class="pagewidth">
    <div id="content">
        <div id="skillselect">
            <?php
                foreach ($skillIDs AS $skill => $id) {
                    ?>
                    <a <?php echo $skill == $skillView ? 'class="selected"' : ''; ?> href="?skill=<?php echo $skill;
                        echo $memberView != "All" ? "&member=" . urlencode($memberView) : ""; ?>"><img src="../images/skills/<?php echo strtolower($skill) ?>.png" title="<?php echo $skill; ?>"/></a>
                <?php
                }
            ?>
        </div>
        <div class="content-module">
            <?php
                if ($memberView == "All") {
                    ?>
                    <h2 class="hstitle">Stat Tracking</h2>
                <?php
                } else {
                    $rank = $dbf->queryToText("SELECT Title FROM clanranks WHERE ClanRankID='" . $clanmembers[0]['ClanRankID'] . "'");
                    ?>
                    <h2 class="hstitle"><?php echo "<img src='../images/ranks/" . $rank . "_clan_rank.png' /> $memberView"; ?></h2>
                <?php
                }
            ?>
            <form method="get" action="">
                <input type="text" name="member" placeholder="View Member" value="<?php echo $memberView == "All" ? "" : $memberView; ?>"/>
                <button>View</button>
            </form>
            <hr>
            <div id="chart" data-collapsed="false"></div>
            <div id="scrollMSG">
                <p>Scroll up to view chart again.</p>
            </div>
        </div>

        <?php
            if ($memberView != "All") {
                ?>
                <div class="content-module">
                    <div id="alog-header">
                        <h2>Adventurer's Log</h2>
                        <hr>
                    </div>
                    <div id="stats-header">
                        <h2>Current Stats</h2>
                        <hr>
                    </div>
                    <div id="alog"></div>
                    <div id="stats">
                        <table border="1">
                            <tr>
                                <th>Skill</th>
                                <th>Level</th>
                                <th>Experience</th>
                                <th>Exp Gained Today</th>
                                <th>Exp Gained This Week</th>
                                <th>Exp Gained This Month</th>
                            </tr>
                            <?php
                                $memID = $clanmembers[0]['ClanMemberID'];
                                $latestStats = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE ClanMemberID='$memID' ORDER BY StatUpdateID DESC LIMIT 1");
                                $latestStats = preg_split("/\s+/", $latestStats);

                                $dailyUpdates = $dbf->getAllAssocResults("SELECT MAX(StatUpdateID) AS StatUpdateID, TimeFetched, Finished FROM statupdates WHERE Finished = 1 GROUP BY MAKEDATE(YEAR(TimeFetched), DAYOFYEAR(TimeFetched))");

                                $yesterday = $dailyUpdates[count($dailyUpdates) - 2];
                                $yesterdayStats = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE ClanMemberID='$memID' AND StatUpdateID='" . $yesterday['StatUpdateID'] . "'");
                                $yesterdayStats = preg_split("/\s+/", $yesterdayStats);

                                $weekly = $dailyUpdates[count($dailyUpdates) - 8];
                                $weeklyStats = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE ClanMemberID='$memID' AND StatUpdateID =" . $weekly['StatUpdateID']);
                                $weeklyStats = preg_split("/\s+/", $weeklyStats);

                                $monthly = count($dailyUpdates) > 31 ? $dailyUpdates[count($dailyUpdates) - 31] : 0;
                                $monthlyStats = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE ClanMemberID='$memID' AND StatUpdateID =" . $monthly['StatUpdateID']);
                                $monthlyStats = preg_split("/\s+/", $monthlyStats);

                                foreach ($latestStats as $i => $stat) {
                                    if ($i > 0 && $i < 27) {
                                        $stat = explode(",", $stat);
                                        $yesterday = explode(",", $yesterdayStats[$i]);
                                        $weekly = explode(",", $weeklyStats[$i]);
                                        $monthly = explode(",", $monthlyStats[$i]);

                                        $skill = array_search($i, $skillIDs);
                                        ?>
                                        <tr>
                                            <td><?php echo "<img src='../images/skills/" . strtolower($skill) . ".png' />$skill" ?></td>
                                            <td><?php echo number_format($stat[1]); ?></td>
                                            <td><?php echo number_format($stat[2]); ?></td>
                                            <td class="gains">+<?php echo number_format($stat[2] - $yesterday[2]); ?></td>
                                            <td class="gains">+<?php echo number_format($stat[2] - $weekly[2]); ?></td>
                                            <td class="gains">+<?php echo number_format($stat[2] - $monthly[2]); ?></td>
                                        </tr>
                                    <?php
                                    }
                                }
                            ?>
                        </table>
                    </div>
                </div>
            <?php
            }
        ?>
    </div>
</div>

<script src="../highcharts/js/highcharts.js"></script>
<script src="../highcharts/js/themes/grid-light.js"></script>
<script>
    $(function () {
        $('#chart').highcharts({
            chart: {
                type: 'spline',
                zoomType: "x"
            },
            title: {
                text: 'IronClan Stat Gains'
            },
            subtitle: {
                <?php
                    echo $memberView == "All" ? "text: 'All members'" : "text: '$memberView'";
                ?>
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Date'
                }
            },
            yAxis: {
                title: {
                    text: 'Experience'
                },
                min: 0
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
                valuePrefix: "{point.x:%b %e, %H:%M}: ",
                valueSuffix: " xp"
            },
            series: [
                <?php
                foreach($clanmembers as $i => $member) {
                    $rsn = $member['RSN'];
                    $id = $member['ClanMemberID'];

                    if($i > 0) {
                        echo ",\n";
                    }
                    ?>
                {
                    name: '<?php echo $rsn; ?>',
                    data: [
                        <?php
                        $prevXP = 0;
                        $firstUpdate = true;
                            foreach($updates as  $updateIndex => $update) {
                                $updateID = $update['StatUpdateID'];
                                $year = date("Y", strtotime($update['TimeFetched']));
                                $month = date("m", strtotime($update['TimeFetched']));
                                $day = date("d",strtotime($update['TimeFetched']));
                                $hour = date("G",strtotime($update['TimeFetched']));

                                $history = $dbf->queryToText("SELECT HSLiteData FROM stathistory WHERE StatUpdateID='$updateID' AND ClanMemberID='$id' AND LEFT(HSLiteData, 1) != '<'");

                                if(!strstr($history, "<html>")) {
                                    $stats = preg_split("/\s+/", $history);
                                    $viewed = $stats[$skillIDs[$skillView]];

                                    $viewed = explode(",", $viewed);

                                    $xp = $viewed[2];

                                    if($xp == "-1") {
                                        $xp = 0;
                                    }
                                } else {
                                    $xp = 0;
                                }


                                if($xp != "" && $xp != 0) {
                                    if($xp > $prevXP || $memberView == "All") {
                                        if($updateIndex > 0 && !$firstUpdate) {
                                            echo ",";
                                        }

                                        $firstUpdate = false;
                                ?>
                        [Date.UTC(<?php echo "$year, " . (intval($month) - 1) . ", $day, $hour"; ?>), <?php echo $xp == "" ? 0 : $xp; ?>]
                        <?php
                                    }
                                }
                            $prevXP = $xp;
                            } //end update loop
                ?>
                    ]
                }
                <?php
        }
?>
            ]
        });
    });
</script>

<script src="jquery.zrssfeed.min.js"></script>
<script>
    $("#alog").rssfeed("http://services.runescape.com/m=adventurers-log/rssfeed?searchName=<?php echo $memberView; ?>", {
        limit: 10,
        header: false,
        date: false,
        content: true,
        media: false
    });

    $(".gains").each(function () {
        var gain = $(this);
        var value = gain.text();
        var gained = parseInt(value);

        if(gained > 0) {
            gain.css("color", "green");
        } else if (value == "+-1") {
            gain.text("+0");
        }
    });

    $(document).bind('DOMMouseScroll mousewheel', function(e) {
        var FFdelta = e.originalEvent.detail;
        var WKdelta = e.originalEvent.wheelDelta;

        if(FFdelta < 0 || WKdelta > 0) {
            //scrolling up

            //alert("up");

            if($("#chart").attr("data-collapsed") == "true") {
                $("#chart").css("height", "500px").attr("data-collapsed", "false");
                $("#scrollMSG").css("display", "none");
            }
        } else {
            //scrolling down

            //alert("down");
            if($(document).scrollTop() > 50) {
                $("#chart").css("height", "0").attr("data-collapsed", "true");
                $("#scrollMSG").css("display", "block");
            }
        }
    });
</script>
</body>
</html>