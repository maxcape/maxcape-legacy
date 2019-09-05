<?php
    if (!isset($_GET['skill'])) {
        $skillView = "Overall";
    } else {
        $skillView = $_GET['skill'];
    }

    function time_elapsed_string( $ptime ) {
        $etime = time() - $ptime;

        if ( $etime < 1 ) {
            return '0 seconds';
        }

        $a = array(
            12 * 30 * 24 * 60 * 60 => 'year', 30 * 24 * 60 * 60 => 'month', 24 * 60 * 60 => 'day', 60 * 60 => 'hour', 60 => 'minute', 1 => 'second'
        );

        foreach ( $a as $secs => $str ) {
            $d = $etime / $secs;
            if ( $d >= 1 ) {
                $r = round( $d );
                return $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }
    }

    $skillIDs = array(
        "Overall"       => 0, "Attack" => 1, "Defence" => 2, "Strength" => 3, "Constitution" => 4, "Ranged" => 5, "Prayer" => 6, "Magic" => 7, "Cooking" => 8,
        "Woodcutting"   => 9, "Fletching" => 10, "Fishing" => 11, "Firemaking" => 12, "Crafting" => 13, "Smithing" => 14, "Mining" => 15, "Herblore" => 16,
        "Agility"       => 17, "Thieving" => 18, "Slayer" => 19, "Farming" => 20, "Runecrafting" => 21, "Hunter" => 22, "Construction" => 23, "Summoning" => 24,
        "Dungeoneering" => 25, "Divination" => 26
    );

    function sortByViewedSkill($a, $b) {
        global $skillView;
        global $skillIDs;

        $ExpOne = $a->Skills[$skillIDs[$skillView]]->Exp;
        $ExpTwo = $b->Skills[$skillIDs[$skillView]]->Exp;
        $lvlOne = $a->Skills[$skillIDs[$skillView]]->Level;
        $lvlTwo = $b->Skills[$skillIDs[$skillView]]->Level;

        if($lvlOne == $lvlTwo) {
            if($ExpOne > $ExpTwo) {
                return -1;
            } else if($ExpOne < $ExpTwo) {
                return 1;
            } else {
                return 0;
            }
        } else if($lvlOne > $lvlTwo) {
            return -1;
        } else {
            return 1;
        }
    }

    require_once "../dbfunctions.php";
    require_once "../rsfunctions.php";
    $dbf = new dbfunctions();
    $rsf = new rsfunctions();

    $statList = $dbf->getAllAssocResults("SELECT *
                                            FROM stathistory sh
                                            JOIN statupdates su
                                                ON sh.StatUpdateID = su.StatUpdateID
                                            JOIN clanmembers cm
                                                ON sh.ClanMemberID = cm.ClanMemberID
                                            WHERE su.TimeFetched = (
                                                SELECT MAX(TimeFetched)
                                                FROM statupdates
                                                WHERE Finished = 1
                                            )");
    echo mysql_error();

    $members = array();

    foreach ($statList as $memberStats) {
        $obj = new stdClass();
        $obj->RSN = $memberStats['RSN'];
        $obj->Rank = $dbf->queryToText("SELECT Title FROM clanranks WHERE ClanRankID=" . $memberStats['ClanRankID']);
        if(!strstr( $memberStats['HSLiteData'], "<html>" )) {
            $stats = preg_split("/\s+/", $memberStats['HSLiteData']);
            $obj->Skills = array();

            foreach ($stats as $i => $stat) {
                $arr = explode(",", $stat);

                $obj->Skills[$i] = new stdClass();
                $obj->Skills[$i]->Rank = $arr[0];
                $obj->Skills[$i]->Level = $arr[1];
                $obj->Skills[$i]->Exp = $arr[2];
            }
        } else {
            for($i = 0; $i < 27; $i++) {
                $obj->Skills[$i] = new stdClass();
                $obj->Skills[$i]->Rank  = -1;
                $obj->Skills[$i]->Level = -1;
                $obj->Skills[$i]->Exp   = -1;
            }
        }

        $members[] = $obj;
    }

    usort($members, "sortByViewedSkill");

    $lastUpdate = $dbf->queryToText("SELECT TimeFetched FROM statupdates WHERE Finished = 1 ORDER BY TimeFetched DESC LIMIT 1");
    $curUpdate = $dbf->queryToText("SELECT COUNT(*) FROM statupdates WHERE Finished = 0");
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Iron Clan Highscores</title>
        <link rel="stylesheet" href="../styles.css"/>
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
                            <a <?php echo $skill == $skillView ? 'class="selected"' : ''; ?> href="?skill=<?php echo $skill ?>"><img src="../images/skills/<?php echo strtolower($skill) ?>.png" title="<?php echo $skill; ?>"/></a>
                        <?php
                        }
                    ?>
                </div>

                <div class="content-module">
                    <h2 class="hstitle">Highscores for <?php echo $skillView; ?> </h2>
                    <p class="updated">Last Updated: <?php echo time_elapsed_string(strtotime($lastUpdate)); ?> <?php if($curUpdate != "0") { echo "<span class='updating'>Currently updating</span>"; } ?></p>
                    <hr>
                    <table border="1">
                        <tr>
                            <th>Clan Rank</th>
                            <th>Clan Member</th>
                            <th>Level</th>
                            <th>Experience</th>
                            <th>Official Rank</th>
                        </tr>
                        <?php
                            foreach ($members as $i => $member) {
                                if ($member->Skills[$skillIDs[$skillView]]->Exp != "-1") {
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><img src="../images/ranks/<?php echo $member->Rank; ?>_clan_rank.png"><a href='../tracking/?member=<?php echo $member->RSN; ?>'><?php echo $member->RSN; ?></a></td>
                                        <td><?php echo number_format($member->Skills[$skillIDs[$skillView]]->Level); ?></td>
                                        <td><?php echo number_format($member->Skills[$skillIDs[$skillView]]->Exp); ?></td>
                                        <td><?php echo number_format($member->Skills[$skillIDs[$skillView]]->Rank); ?></td>
                                    </tr>
                                <?php
                                }
                            }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>