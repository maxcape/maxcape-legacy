<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;
    require_once "../../userfunctions.php";
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $userid = $_SESSION['userid'];

    $PER_PAGE = 30;

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

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

    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

    $forumid = $_GET['id'];
    $forumid = mysql_real_escape_string($forumid);

    $forum = $dbf->queryToAssoc("SELECT * FROM forums WHERE ForumID='$forumid'");

    if (count($forum) === 0) {
        header("Location: /forums/");
    }

    $forumname = $forum['Title'];


    $breadcrumbs = "<a class='breadcrumb' href='/forums/'>Forums Home</a> <a class='breadcrumb' href='/forums/f/$forumid'>$forumname</a>";


    $totalthreads = intval($dbf->queryToText("SELECT COUNT(*) FROM forumthreads WHERE ForumID='$forumid'"));

    $numberOfPages = ceil($totalthreads / $PER_PAGE);

    if ($page > $numberOfPages) {
        $page = $numberOfPages;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $forumname; ?> - Maxcape Forums</title>
    <link rel="stylesheet" href="/forums/forums.css"/>
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="/forums/header.css"/>
    <script src="/forums/jquery.js"></script>

    <link rel="stylesheet" href="/forums/f/threadlist.css"/>
</head>

<body>
<?php require_once("../header.php"); ?>

<section id="maincontent">
<section id="wrapper">
<h1 class="categoryheader"><?php echo $forumname; ?></h1>
<?php
    if ($loggedin) {
        if ($forum['Locked'] != "1" || $userlevel >= 4) {
            ?>
            <a class="createthread" href="/forums/post/?subforum=<?php echo $forumid; ?>">Create a Thread</a>
        <?php
        } else {
            ?>
            <span class="createthread">This forum is locked</span>
        <?php
        }
    } else {
        ?>
        <span class="createthread">Please <a href="/user/login">Login</a> or <a href="/user/register">Register</a> to create a thread</span>
    <?php
    }
?>

<ul class="pagination">
    <?php
        $maxPages = 5;

        if ($numberOfPages < $maxPages) {
            for ($i = 0; $i < $numberOfPages; $i++) {
                if ($i == $page - 1) {
                    ?>
                    <li class="currentpage">
                        <a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                } else {
                    ?>
                    <li><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                }
                ?>
            <?php
            }
        } else {
            $startingpage = $page - 3;
            $endingpage = $page + 2;
            if ($startingpage < 0) {
                $startingpage = 0;
            }

            if ($endingpage > $numberOfPages) {
                $endingpage = $numberOfPages;
            }

            if ($startingpage != 0) {
                ?>
                <li><a href="javascript:void(0);" onclick="jumpPage();">...</a></li>
            <?php
            }

            for ($i = $startingpage; $i < $endingpage; $i++) {
                if ($i == $page - 1) {
                    ?>
                    <li class="currentpage">
                        <a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                } else {
                    ?>
                    <li><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                }
                ?>
            <?php
            }

            if ($endingpage != $numberOfPages) {
                ?>
                <li><a href="javascript:void(0);" onclick="jumpPage();">...</a></li>
            <?php
            }
        }
    ?>
</ul>

<table class="threadtable">
    <thead>
        <tr>
            <th class="threadtable-title">Title/Description</th>
            <th class="threadtable-topicauthor">Started By</th>
            <th class="threadtable-topiccount">Views</th>
            <th class="threadtable-postcount">Posts</th>
            <th class="threadtable-lastpost">Latest Post</th>
        </tr>
    </thead>
    <?php
        $threadlist = $dbf->getAllAssocResults("SELECT t.ThreadID, t.UserID, t.Title, t.Subtitle, t.Viewcount,
        t.Locked, t.Sticky, t.Hidden, p.PostID AS LatestPostID, p.PostDate AS LatestPostDate,
        p.UserID AS LatestPostUserID FROM forumthreads t JOIN forumposts p ON p.ThreadID = t.ThreadID AND p.PostDate
        = (SELECT PostDate FROM forumposts p1 WHERE p1.ThreadID = t.ThreadID ORDER BY PostDate DESC LIMIT 1) WHERE
        ForumID = '$forumid' GROUP BY t.ThreadID ORDER BY t.Sticky DESC, LatestPostDate DESC");

        if (count($threadlist) == 0) {
            ?>
            <tr class="thread nothreads">
                <td colspan="5"><div class="nothread"></div>No Threads</td>
            </tr>
        <?php
        }

        foreach ($threadlist as $index => $t) {
            if ($index >= ($page - 1) * $PER_PAGE && $index < (($page - 1) * $PER_PAGE) + $PER_PAGE) {

                $threadid = $t['ThreadID'];
                if ($t['Hidden'] != "1") {
                    $postcount = $dbf->queryToText("SELECT COUNT(*) FROM forumposts WHERE ThreadID ='$threadid'");
                    $viewcount = $dbf->queryToText("SELECT COUNT(*) FROM forumthreadviews WHERE ThreadID='$threadid'");

                    $authorid = $t['UserID'];
                    $author = $dbf->queryToText("SELECT Username FROM users WHERE UserID='$authorid'");

                    $latestpostauthorid = $t['LatestPostUserID'];
                    $latestpostauthor = $dbf->queryToText("SELECT UserName FROM users WHERE UserID='$latestpostauthorid'");

                    $latestpostdate = strtotime($t['LatestPostDate']);

                    $lastviewdate = $dbf->queryToText("SELECT LastViewDate FROM forumthreadviews WHERE
                    UserID='$userid' AND ThreadID='$threadid'");
                    if($lastviewdate == "") {
                        $lastviewdate = "1970-01-01 00:00:00";
                    }
                    $unread = $dbf->getAllAssocResults("SELECT * FROM forumposts WHERE ThreadID='$threadid' AND PostDate >= '$lastviewdate'");
                    ?>

                    <tr class="threadtable-thread <?php echo count($unread) > 0 ? "hasunread" : "hasread"; ?>">
                        <td class="threadtable-title">
                            <div class="<?php echo count($unread) > 0 ? "unread" : "read"; ?>"></div>
                            <div class="pin">
                                <?php echo $t['Sticky'] == "1" ? "<i class='fa fa-thumb-tack fa-rotate-270' title='Sticky'></i> " :
                                    ""; ?>
                            </div>
                            <a href="/forums/t/<?php echo $t['ThreadID']; ?>" <?php echo $t['Locked'] == '1' ? "class='locked'" : ""; ?>>
                                <h3><?php echo $t['Title']; ?></h3>

                                <p><?php echo $t['Subtitle'] ?></p>
                            </a>
                        </td>
                        <td class="threadtable-topicauthor"><?php echo $author; ?></td>
                        <td class="threadtable-topiccount"><?php echo $viewcount; ?></td>
                        <td class="threadtable-postcount"><?php echo $postcount; ?></td>

                        <td class="threadtable-lastpost">
                            <?php
                                $lastpostThreadID = $t['ThreadID'];
                                $lastpostThreadPostCount = $dbf->queryToText("SELECT COUNT(*) FROM forumposts WHERE ThreadID='$lastpostThreadID'");
                                $pages = ceil($lastpostThreadPostCount / 10);
                            ?>
                            <a href="/forums/t/<?php echo $t['ThreadID']; ?>?page=<?php echo $pages; ?>#post-<?php echo $lastpostThreadPostCount; ?>">
                                <span><?php echo $latestpostauthor; ?></span>
                                <span><?php echo time_elapsed_string($latestpostdate); ?></span>
                            </a>
                        </td>
                    </tr>

                <?php
                } else {
                    ?>
                    <tr class="thread nothreads">
                        <td colspan="5">
                            <div class="nothread"></div>
                            This Thread has been hidden
                            <?php
                                if ($userlevel > 3) {
                                    ?>
                                    &bull; <a href="/forums/t/<?php echo $threadid; ?>">View</a>
                                <?php
                                }
                            ?>
                        </td>
                    </tr>
                <?php
                }

                if($t['Sticky'] == "1" && $threadlist[$index + 1]['Sticky'] == "0") {
                    ?>
                </table>
                <table class="threadtable nonstickyposts">
    <?php

                }
            }
        }
    ?>
</table>

<ul class="pagination">
    <?php
        $maxPages = 5;

        if ($numberOfPages < $maxPages) {
            for ($i = 0; $i < $numberOfPages; $i++) {
                if ($i == $page - 1) {
                    ?>
                    <li class="currentpage">
                        <a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                } else {
                    ?>
                    <li><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                }
                ?>
            <?php
            }
        } else {
            $startingpage = $page - 3;
            $endingpage = $page + 2;
            if ($startingpage < 0) {
                $startingpage = 0;
            }

            if ($endingpage > $numberOfPages) {
                $endingpage = $numberOfPages;
            }

            if ($startingpage != 0) {
                ?>
                <li><a href="javascript:void(0);" onclick="jumpPage();">...</a></li>
            <?php
            }

            for ($i = $startingpage; $i < $endingpage; $i++) {
                if ($i == $page - 1) {
                    ?>
                    <li class="currentpage">
                        <a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                } else {
                    ?>
                    <li><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
                <?php
                }
                ?>
            <?php
            }

            if ($endingpage != $numberOfPages) {
                ?>
                <li><a href="javascript:void(0);" onclick="jumpPage();">...</a></li>
            <?php
            }
        }
    ?>
</ul>

<script>
    function jumpPage() {
        var maxpage = <?php echo $numberOfPages; ?>;

        var pageToGoTo = prompt("Please enter a page number between 1 and " + maxpage + " to jump to.");

        if (!isNaN(pageToGoTo) && parseInt(pageToGoTo, 10) <= maxpage && parseInt(pageToGoTo, 10) > 0) {
            location.href = "?page=" + pageToGoTo;
        }
    }
</script>

<?php
    if ($loggedin) {
        if ($forum['Locked'] != "1" || $userlevel > 3) {
            ?>
            <a class="createthread" href="/forums/post/?subforum=<?php echo $forumid; ?>">Create a Thread</a>
        <?php
        } else {
            ?>
            <span class="createthread">This forum is locked</span>
        <?php
        }
    } else {
        ?>
        <span class="createthread">Please <a href="/user/login">Login</a> or <a href="/user/register">Register</a> to create a thread</span>
    <?php
    }
?>
</section>
</section>

<section id="footer">
    <section id="footerinner">
        <p>Maxcape.com &copy; The Orange 2012 - 2014</p>
    </section>
</section>
</body>
</html>