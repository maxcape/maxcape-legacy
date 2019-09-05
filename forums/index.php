<?php
    require_once( '../dbfunctions.php' );
    require( '../userfunctions.php' );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    function time_elapsed_string($ptime) {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return '0 seconds';
        }

        $a = array(
            12 * 30 * 24 * 60 * 60 => 'year', 30 * 24 * 60 * 60 => 'month', 24 * 60 * 60 => 'day', 60 * 60 => 'hour', 60 => 'minute', 1 => 'second'
        );

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);

                return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }
        }
    }

    $dbf->connectToDatabase($dbf->database) or die();

    $breadcrumbs = "<a class='breadcrumb' href='/forums/'>Forums Home</a>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Maxcape Forums</title>
        <link rel="stylesheet" href="forums.css"/>
        <link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <link rel="stylesheet" href="header.css"/>
        <script src="jquery.js"></script>
    </head>

    <body>
        <?php require_once("header.php"); ?>

        <div id="maincontent">
            <div id="wrapper">
                <div id="content">
                    <div class="innercontent" id="forumlist">
                        <?php
                            $categories = $dbf->getAllAssocResults("SELECT * FROM forumcategories");
                            foreach ($categories as $category) {
                                ?>
                                <div class="category">
                                    <h2 class="categoryheader"><?php echo $category['Title']; ?></h2>


                                    <table class="subforumlisttable">
                                        <tr class="subforumlisttableheader">
                                            <th class="subforumtable-icon">&nbsp;</th>
                                            <th class="subforumtable-title">Title/Description</th>
                                            <th class="subforumtable-topiccount">Threads</th>
                                            <th class="subforumtable-postcount">Posts</th>
                                            <th class="subforumtable-lastpost">Latest Post</th>
                                        </tr>
                                        <?php
                                            $catid = $category['CategoryID'];
                                            $forumlist = $dbf->getAllAssocResults("SELECT * FROM forums WHERE CategoryID='$catid'");
                                            foreach ($forumlist as $forum) {
                                                $forumid = $forum['ForumID'];
                                                $lockedForum = $forum['Locked'] == "1" ? true : false;

                                                $threadCount = $dbf->queryToText("SELECT COUNT(*) FROM forumthreads WHERE ForumID='$forumid'");
                                                $postCount = $dbf->queryToText("SELECT COUNT(*) FROM forumthreads t JOIN forumposts p ON t.ThreadID = p.ThreadID WHERE t.ForumID='$forumid'");

                                                $recentpost = $dbf->queryToAssoc("SELECT *, p.UserID AS PostAuthor FROM forumposts p JOIN forumthreads t ON p.ThreadID = t.ThreadID AND t.ForumID='$forumid' WHERE t.Hidden != 1 ORDER BY p.PostDate DESC LIMIT 1");
                                                $recentpostuserid = $recentpost['PostAuthor'];
                                                $recentpostuser = $dbf->queryToText("SELECT Username FROM users WHERE UserID='$recentpostuserid'");

                                                $maxLen = 18;
                                                if(strlen($recentpost['Title']) > $maxLen) {
                                                    $recentpost['Title'] = substr($recentpost['Title'], 0, $maxLen) . "...";
                                                }
                                                ?>
                                        <tr class="subforumtable-subforum" id="sf-<?php echo $forum['ForumID']; ?>">
                                            <td class="subforumtable-icon">

                                                <i class="fa fa-<?php echo $lockedForum ? "lock" : "unlock-alt";
                                                ?>"></i>
                                            </td>
                                            <td class="subforumtable-title">
                                                <a href="/forums/f/<?php echo $forum['ForumID']; ?>">
                                                    <h3><?php echo $forum['Title']; ?></h3>

                                                    <p><?php echo $forum['Description']; ?></p>
                                                </a>
                                            </td>
                                            <td class="subforumtable-topiccount">
                                                <?php echo $threadCount != "" ? $threadCount : 0; ?>
                                            </td>
                                            <td class="subforumtable-postcount">
                                                <?php echo $postCount != "" ? $postCount : 0; ?>
                                            </td>
                                            <td class="subforumtable-lastpost">
                                                <?php
                                                    if(count($recentpost) != 0) {
                                                        $lastpostThreadID = $recentpost['ThreadID'];
                                                        $lastpostThreadPostCount = $dbf->queryToText("SELECT COUNT(*) FROM forumposts WHERE ThreadID='$lastpostThreadID'");
                                                        $pages = ceil($lastpostThreadPostCount / 10);
                                                        ?>
                                                        <span class="latestpostthread"><a href="/forums/t/<?php echo $recentpost['ThreadID']; ?>?page=<?php echo $pages; ?>#post-<?php echo $lastpostThreadPostCount; ?>"><?php echo $recentpost['Title']; ?></a></span>
                                                        <span class="latestpostauthor"><?php echo $recentpostuser; ?></span>
                                                        <span class="latestposttime"><?php echo time_elapsed_string(strtotime($recentpost['PostDate'])); ?></span>
                                                    <?php
                                                    } else {
                                                        ?>
                                                        <span>None</span>
                                                    <?php
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                            <?php
                                            }
                                        ?>
                                    </table>
                                </div>
                            <?php
                            }
                        ?>
                    </div>
                </div>
                <aside>
                    <?php
                        if(isset($userlevel) && $userlevel > 3) {
                    ?>
                    <a id="adminLink" href="/forums/admin/">Admin Access</a>
                    <?php
                        }
                    ?>
                    <h2 class="categoryheader">New Forum Threads</h2>
                    <ul class="sidebarlist">
                        <?php
                            $recentThreads = $dbf->getAllAssocResults("SELECT * FROM forumthreads WHERE Hidden=0 ORDER BY CreationDate DESC LIMIT 10");

                            foreach ($recentThreads as $recent) {
                                $authorid = $recent['UserID'];
                                $author = $dbf->queryToText("SELECT Username FROM users WHERE UserID='$authorid'");
                                $authorrsn = $dbf->queryToText("SELECT RSN FROM users WHERE UserID='$authorid'");

                                $postDate = strtotime($recent['CreationDate']);

                                $recentid = $recent['ThreadID'];
                                $recentForum = $dbf->queryToText("SELECT f.Title FROM forums f JOIN forumthreads t ON t.ForumID = f.ForumID AND t.ThreadID = '$recentid'");
                                $recentForumID = $dbf->queryToText("SELECT ForumID FROM forums WHERE Title='$recentForum'");

                                $maxLen = 18;
                                if(strlen($recent['Title']) > $maxLen) {
                                    $recent['Title'] = substr($recent['Title'], 0, $maxLen) . "...";
                                }
                                ?>
                                <li>
                                    <div class="userportrait">
                                        <img src="getAvatar.php?rsn=<?php echo urlencode($authorrsn); ?>" onerror="this.src='avatar.png';" alt="Avatar"/>
                                    </div>
                                    <div class="postdetails">
                                        <p class="threadtitle">
                                            <span><a href="/forums/t/<?php echo $recentid; ?>"><?php echo $recent['Title']; ?></a></span>
                                            <span>in <a href="/forums/f/<?php echo $recentForumID; ?>"><?php echo $recentForum; ?></a></span>
                                        </p>

                                        <p class="userandtime">
                                            <a href="#" class="user"><?php echo $author; ?></a> <?php echo time_elapsed_string($postDate); ?>
                                        </p>
                                    </div>
                                </li>
                            <?php
                            }
                        ?>
                    </ul>
                </aside>
            </div>


            <div id="whoshere">
                <?php
                    $activeUsers = $dbf->getAllAssocResults("SELECT u.Username, u.PrivelegeLevel, ul.Title
                                                             FROM forumthreadviews ftv
                                                             JOIN users u
                                                                 ON u.UserID = ftv.UserID
                                                             JOIN userlevels ul
                                                                 ON ul.UserLevelID = u.PrivelegeLevel
                                                             WHERE LastViewDate >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                                                             GROUP BY u.Username
                                                             ORDER BY u.PrivelegeLevel DESC");
                ?>
                <h2 class="categoryheader">Who's here</h2>
                <div id="whoshere-container">
                    <span class="categorysubheader floatleft"><?php echo count($activeUsers); ?> users active in the
                        last 30 minutes:</span>
                    <span class="categorysubheader floatright">Legend: <span class="level-5">Maxcape
                            Developer</span> | <span class="level-4">Site Admin</span> | <span
                            class="level-3">Forum Moderator</span> | <span class="level-2">VIP</span> | <span
                            class="level-1">User</span>
                    </span>

                    <div class="activeUsers">
                    <?php

                        foreach($activeUsers as $i=> $activeUser) {
                            ?>
                        <span class="activeUser level-<?php echo $activeUser['PrivelegeLevel']; ?>"><?php echo
                            $activeUser['Username']; if($i < count($activeUsers) - 1) echo ", ";
                            ?></span>
                    <?php
                        }
                    ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="footer">
            <div id="footerinner">
                <p>Maxcape.com &copy; The Orange 2012 - 2014</p>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $(".subforum").each(function () {
                    $(this).click(function () {
                        location.href = "/forums/f/" + $(this).attr("data-id");
                    });
                });
            });
        </script>
    </body>
</html>