<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;
    require_once "../../userfunctions.php";
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    $PER_PAGE = 10;

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $userid = $_SESSION['userid'];

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

    $threadid = $_GET['threadid'];
    $threadid = mysql_real_escape_string($threadid);
    $dbf->query("UPDATE forumthreads SET Viewcount = Viewcount + 1 WHERE ThreadID='$threadid'");
    $thread = $dbf->queryToAssoc("SELECT * FROM forumthreads WHERE ThreadID = '$threadid'");

    $viewed = $dbf->queryToAssoc("SELECT * FROM forumthreadviews WHERE UserID='$userid' AND ThreadID='$threadid'");
    $hasviewed = count($viewed) > 0 ? true : false;

    if (count($thread) === 0) {
        header("Location: /forums/");
    }

    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");
    $forumname = $dbf->queryToText("SELECT f.Title FROM forums f JOIN forumthreads t ON t.ForumID = f.ForumID AND t.ThreadID='$threadid'");
    $forumid = $dbf->queryToText("SELECT f.ForumID FROM forums f JOIN forumthreads t ON t.ForumID = f.ForumID AND t.ThreadID='$threadid'");
    $threadname = $thread['Title'];
    $locked = $thread['Locked'] == "1" ? true : false;
    $sticky = $thread['Sticky'] == "1" ? true : false;
    $hidden = $thread['Hidden'] == "1" ? true : false;

    if ($hidden && $userlevel < 3) {
        header("Location: /forums/");
    }

    $totalposts = $dbf->queryToText("SELECT COUNT(*) FROM forumposts WHERE ThreadID='$threadid'");

    $numberOfPages = ceil($totalposts / $PER_PAGE);

    if ($page > $numberOfPages) {
        $page = $numberOfPages;
    }

    $breadcrumbs = "<a class='breadcrumb' href='/forums/'>Forums Home</a> <a class='breadcrumb' href='/forums/f/$forumid'>$forumname</a> <a class='breadcrumb' href='#'>$threadname</a>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $threadname; ?> - <?php echo $forumname; ?> - Maxcape Forums</title>
    <link rel="stylesheet" href="/forums/forums.css"/>
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="/forums/header.css"/>
    <script src="/forums/jquery.js"></script>

    <script src="../../js/justgage/js/justgage.1.0.1.min.js"></script>
    <script src="../../js/justgage/js/raphael.2.1.0.min.js"></script>
    <script>
        $(document).ready(function () {
            if (window.location.hash != "") {
                handleHash();
            }
            $(window).on("hashchange", handleHash);

            $(".post_link").click(function () {
                handleHash();
            });

            var rsncache = {};
            var gauge;

            $(".gauge").each(function (index) {
                var rsn = $(this).attr("data-rsn");
                var id = $(this).attr("id");

                var percentage = 0;
                $.post("/forums/getPercentage.php", {"rsn": rsn}, function (data) {
                    if (data != "") {
                        percentage = data;
                        rsncache[rsn] = percentage;

                        gauge = new JustGage({
                            id: id,
                            value: percentage,
                            min: 0,
                            max: 100,
                            title: rsn,
                            label: "Percent",
                            valueFontColor: "#1D1D1D",
                            titleFontColor: "#1D1D1D",
                            labelFontColor: "#1D1D1D",
                            refreshAnimationTime: 150,
                            levelColors: [
                                "#FF0000",
                                "#F9C802",
                                "#A9D70B"
                            ]
                        });
                    }
                });
            });
        });

        function handleHash() {
            var hash = window.location.hash;
            var target;

            $(".post").each(function () {
                if ($(this).attr("id") == hash.slice(1, hash.length)) {
                    target = $(this);
                }
            });
            $("body").animate({scrollTop: target.offset().top - 70}, 1);
        }


        function jumpPage() {
            var maxpage = <?php echo $numberOfPages; ?>;

            var pageToGoTo = prompt("Please enter a page number between 1 and " + maxpage + " to jump to.");

            if (!isNaN(pageToGoTo) && parseInt(pageToGoTo, 10) <= maxpage && parseInt(pageToGoTo, 10) > 0) {
                location.href = "?page=" + pageToGoTo;
            }
        }

        <?php
        if($loggedin) {
        ?>
        function quotePost(postid, author, number) {
            var postcontent = $("#mdcontent-" + postid).val();
            var lines = postcontent.split("\n");

            var output = ">[" + author + "](/forums/t/<?php echo $threadid; ?>#post-" + number + ") said:\n>\n";
            for (var i = 0; i < lines.length; i++) {
                output += ">" + lines[i] + "\n";
            }

            output += "\n\n";

            editor.importFile('some-file', output);
            editor.focus();

            document.location = "#reply";
        }
        <?php
        } else {
        ?>
        function quotePost(number, author, number) {
            alert("Please log in to do that.");
        }
        <?php
        }
        ?>

        <?php
        if(intval($userlevel) >= 3) {
        ?>
        function move() {
            $("#blinder").css("display", "block");
        }

        function moveThread() {
            var threadid = $("#moveThreadID").val();
            var movetoid = $("#moveToForum").val();

            $.post("/forums/admin/moveThread.php", {"threadid": threadid, "moveToForum": movetoid}, function (data) {
                if (data.slice(0, 5) != "Error") {
                    location.reload();
                } else {
                    alert(data);
                }
            });
        }

        function cancelMove() {
            $("#blinder").css("display", "none");
        }

        function lock(threadid) {
            $.post("/forums/admin/lockThread.php", {"threadid": threadid}, function (data) {
                if (data.slice(0, 5) != "Error") {
                    $("#lockThread").removeClass().addClass(data);
                } else {
                    alert(data);
                }
            });
        }

        function sticky(threadid) {
            $.post("/forums/admin/stickyThread.php", {"threadid": threadid}, function (data) {
                if (data.slice(0, 5) != "Error") {
                    $("#stickyThread").removeClass().addClass(data);
                } else {
                    alert(data);
                }
            });
        }


        function hideThread(threadid) {
            $.post("/forums/admin/hideThread.php", {"threadid": threadid}, function (data) {
                if (data.slice(0, 5) != "Error") {
                    $("#hideThread").removeClass().addClass(data);
                } else {
                    alert(data);
                }
            });
        }


        function viewContent(postid) {
            var hidden = $("#hiddenContent-" + postid);

            hidden.toggle();
        }

        function restorePost(postid) {
            $.post("/forums/admin/restorePost.php", {"postid": postid}, function (data) {
                if (data.slice(0, 5) != "Error") {
                    location.reload();
                } else {
                    alert(data);
                }
            });
        }
        <?php
        }
        ?>
    </script>

    <link rel="stylesheet" href="/forums/viewthread/posts.css"/>
</head>

<body>
<?php require_once("../header.php"); ?>

<section id="maincontent">
<section id="wrapper">

<?php
    if (intval($userlevel) >= 3) {
        ?>
        <span class="threadcontrols">
            <a id="stickyThread" class="<?php echo $sticky ? "sticky" : "unsticky"; ?>" href="javascript:sticky(<?php echo $threadid; ?>);" title="<?php echo $sticky ? "Unsticky thread" : "Sticky thread"; ?>">
                <i class="fa fa-thumb-tack"></i> </a>
            <a id="lockThread" class="<?php echo $locked ? "lock" : "unlock"; ?>" href="javascript:lock(<?php echo $threadid; ?>);" title="<?php echo $locked ? "Unlock thread" : "Lock thread" ?>">
                <i class="fa <?php echo $locked ? "fa-lock" : "fa-unlock"; ?>"></i> </a>
            <a id="moveThread" href="javascript:move();" title="Move Thread"><i class="fa fa-sign-out"></i></a>
            <a id="hideThread" class="<?php echo $hidden ? "hidden" : "unhidden" ?>" href="javascript:hideThread(<?php
                echo
                $threadid; ?>)"
               title="Hide Thread"><i class="fa fa-eraser"></i></a>
        </span>

    <?php
    }
?>
<h1 class="categoryheader"><?php echo $threadname; ?>
    <?php if ($sticky) {
            echo "<span class='posted'> &bull; This thread is a sticky</span>";
        }
        if ($hidden) {
            echo "<span class='posted'> &bull; This thread is hidden</span>";
        }
    ?>
</h1>
<?php
    if ($loggedin && !$locked) {
        ?>
        <a class="createthread" href="#reply" onclick="editor.focus();">Post a Reply</a>
    <?php
    } else if ($locked) {
        ?>
        <span class="createthread">This thread is locked.</span>
    <?php
    } else {
        ?>
        <span class="createthread">Please <a href="/user/login">Login</a> or <a href="/user/register">Register</a> to reply to this thread</span>
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
                    <li class="currentpage"><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
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
                    <li class="currentpage"><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
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

<span id="lastviewinfo">
    <?php
        if ($hasviewed) {
            echo "You last viewed this thread on " . date("F jS, Y", strtotime($viewed['LastViewDate']));
        } else {
            echo "This is the first time you have viewed this thread";
        }
    ?>
</span>

<div id="postlist">
    <?php
        $postlist = $dbf->getAllAssocResults("SELECT * FROM forumposts WHERE ThreadID='$threadid'");

        foreach ($postlist as $i => $post) {
            if ($i >= ($page - 1) * $PER_PAGE && $i < (($page - 1) * $PER_PAGE) + $PER_PAGE) {

                if ($post['IsDeleted'] == "0") {
                    $authorid = $post['UserID'];
                    $author = $dbf->queryToText("SELECT Username FROM users WHERE UserID='$authorid'");
                    $topbarcolor = $dbf->queryToText("SELECT CommentBGColor FROM users WHERE UserID='$authorid'");
                    $level = $dbf->queryToText("SELECT ul.Title FROM userlevels ul JOIN users u ON u.PrivelegeLevel = ul.UserLevelID WHERE u.UserID = '$authorid'");
                    $levelNum = $dbf->queryToText("SELECT ul.UserLevelID FROM userlevels ul JOIN users u ON u.PrivelegeLevel = ul.UserLevelID WHERE u.UserID = '$authorid'");
                    $authorrsn = $dbf->queryToText(("SELECT RSN FROM users WHERE UserID='$authorid'"));

                    $postedstring = time_elapsed_string(strtotime($post['PostDate']));

                    if (intval($post['EditCount']) > 0) {
                        $edituserid = $post['EditUserID'];
                        $edituser = $dbf->queryToText("SELECT Username FROM users WHERE UserID='$edituserid'");
                        $postedstring .= " &bull; Edited " . $post['EditCount'] . " times &bull; Last edited " . time_elapsed_string(strtotime($post['EditDate'])) . " by $edituser";
                    }
                    ?>
                    <div class="post" id="post-<?php echo $i + 1; ?>">
                        <?php
                            if (hexdec($topbarcolor) > 0xFFFFFF * 0.666) {
                                $colorClass = "darkText";
                            } else if (hexdec($topbarcolor) > 0xFFFFFF * 0.5) {
                                $colorClass = "medText";
                            } else {
                                $colorClass = "lightText";
                            }

                            $postcount = $dbf->queryToText("SELECT COUNT(*) FROM forumposts WHERE UserID='$authorid'");
                            $postcount = intval($postcount);
                        ?>
                        <div class="topbar <?php echo $colorClass; ?>" style="background-color:#<?php echo $topbarcolor; ?>;">
                            <span class="userdetail_username"><a href="/profile/<?php echo urlencode($author);
                                ?>"><?php echo
                                    $author;
                                    ?></a></span>
                        <span class="topbar_postnumber">
                            <a class="post_link" data-post-number="<?php echo $i + 1; ?>" href="#post-<?php echo $i + 1; ?>"><i class="fa fa-link"></i></a>
                            <a href="javascript:void(0);" onclick="quotePost(<?php echo $post['PostID']; ?>, '<?php echo $author; ?>', <?php echo $i + 1; ?>);"><i class="fa fa-quote-left"></i></a>
                        </span>
                        </div>
                        <div class="userdetail">
                            <div class="imagecontainer">
                                <img src="/forums/getAvatar.php?rsn=<?php echo urlencode($authorrsn); ?>"/>
                            </div>
                            <div class="userdetails">
                                <span class="userdetail_usergroup"><?php echo $level; ?></span>
                                <span class="userdetail_rsn">RSN: <a href="/calc/<?php echo urlencode($authorrsn); ?>"><?php echo $authorrsn; ?></a></span>
                                <span class="userdetail_postcount">Posts: <?php echo number_format($postcount); ?></span>
                            </div>
                            <div class="gauge" id="gauge-<?php echo $i + 1; ?>" data-rsn="<?php echo $authorrsn; ?>"></div>
                        </div>
                        <div class="postcontents">
                            <span class="posted">Posted <?php echo $postedstring; ?></span>

                            <div class="postcontent">
                                <?php echo $post['HTMLContent']; ?>
                            </div>


                            <div class="posteditor" id="posteditor-<?php echo $post['PostID']; ?>"></div>

                            <?php
                                if ($userid == $authorid || intval($userlevel) >= 3) {
                                    ?>
                                    <div class="ownercontrols">
                                        <?php if (intval($userlevel > 3) || $userid == $authorid) { ?>
                                            <a href="javascript:void(0)" class="delete" title="Delete Post" onclick="deletePost(<?php echo $post['PostID']; ?>)">Delete</a>
                                            <a href="javascript:void(0)" class="edit" onclick="createPostEditor(<?php echo $post['PostID']; ?>);">Edit</a>
                                            <a href="javascript:void(0)" class="canceledit" onclick="cancelEdit(<?php echo $post['PostID']; ?>);">Cancel</a>
                                            <a href="javascript:void(0)" class="saveedit" onclick="saveEdit(<?php echo $post['PostID']; ?>);">Save</a>
                                        <?php } else if(intval($userlevel) > intval($levelNum)) { ?>
                                            <a href="javascript:void(0)" class="delete" title="Hide Post" onclick="deletePost(<?php echo $post['PostID']; ?>)">Hide</a>
                                        <?php } ?>
                                    </div>
                                <?php
                                }
                            ?>
                        </div>

                        <textarea id="mdcontent-<?php echo $post['PostID']; ?>"><?php echo $post['MDContent']; ?></textarea>
                    </div>
                <?php
                } else {
                    $authorid = $post['UserID'];
                    ?>
                    <div class="post" id="post-<?php echo $i + 1; ?>">
                        <div class="topbar darkText">
                            <span class="userdetail_username">[Deleted]</span>
                        <span class="topbar_postnumber">
                            <a class="post_link" data-post-number="<?php echo $i + 1; ?>" href="#post-<?php echo $i + 1; ?>"><i class="fa fa-link"></i></a>
                            <a href="javascript:void(0);" class="disabledQuote"><i class="fa fa-quote-left"></i></a>
                        </span>
                        </div>
                        <div class="userdetail">
                            <div class="imagecontainer">
                                <img src="../avatar.png"/>
                            </div>
                            <div class="userdetails">
                                <span class="userdetail_usergroup"></span>
                                <span class="userdetail_rsn"></span>
                                <span class="userdetail_postcount"></span>
                            </div>
                        </div>
                        <div class="postcontents">
                            <span class="posted">[Deleted]</span>

                            <div class="postcontent contentdeleted">
                                <p>This post has been deleted and/or hidden.</p>
                                <?php
                                    if (intval($userlevel) >= 3) {
                                        ?>
                                        <div class="hiddenContent" id="hiddenContent-<?php echo $post['PostID']; ?>">
                                            <?php echo $post['HTMLContent']; ?>
                                        </div>
                                    <?php
                                    }
                                ?>
                            </div>

                            <?php
                                if ($userid == $authorid || intval($userlevel) >= 3) {
                                    ?>
                                    <div class="ownercontrols">
                                        <a href="javascript:void(0)" class="delete" title="Restore Post"
                                           onclick="restorePost(<?php echo $post['PostID']; ?>)">Restore</a>
                                        <a href="javascript:void(0)" class="edit" onclick="viewContent(<?php echo
                                        $post['PostID']; ?>); ">Show/Hide Hidden Content</a>
                                    </div>
                                <?php
                                }
                            ?>
                        </div>
                    </div>
                <?php
                }
            }
        }
    ?>
</div>

<ul class="pagination">
    <?php
        $maxPages = 5;

        if ($numberOfPages < $maxPages) {
            for ($i = 0; $i < $numberOfPages; $i++) {
                if ($i == $page - 1) {
                    ?>
                    <li class="currentpage"><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
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
                    <li class="currentpage"><a href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a></li>
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

<?php
    if ($loggedin && !$locked) {
        ?>
        <div id="reply">
            <form id="createthread" method="post" action="submitPost.php">
                <h1>Reply to this Thread</h1>

                <div style="width:100%; float:left;">
                    <div id="epiceditor"></div>
                </div>

                <div id="rightbar">
                    <div id="btncontainer">
                        <a href="#">Markdown Help</a>
                        <button type="submit">Post Reply</button>
                    </div>
                </div>
            </form>
        </div>
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

<?php
    if ($userlevel >= 3) {
        $forumCats = $dbf->getAllAssocResults("SELECT * FROM forumcategories");
        ?>
        <div id="blinder">
            <div id="moveThreadDialog">
                <p>Move this thread to:</p>
                <input type="hidden" id="moveThreadID" value="<?php echo $threadid; ?>"/>

                <select id="moveToForum">
                    <?php
                        foreach ($forumCats as $category) {
                            ?>
                            <optgroup label="<?php echo $category['Title']; ?>">
                                <?php
                                    $forumList = $dbf->getAllAssocResults("SELECT * FROM forums WHERE CategoryID=" . $category['CategoryID']);
                                    foreach ($forumList as $forum) {
                                        ?>
                                        <option value="<?php echo $forum['ForumID']; ?>" <?php echo $forum['ForumID'] == $forumid ? "selected='selected'" : ""; ?>><?php echo $forum['Title']; ?></option>
                                    <?php
                                    }
                                ?>
                            </optgroup>
                        <?php
                        }
                    ?>
                </select>

                <button onclick="cancelMove();">Cancel</button>
                <button onclick="moveThread();">Move Thread</button>
            </div>
        </div>
    <?php
    }
?>

<?php
    if ($loggedin && !$locked) {
        ?>
        <script src="/forums/EpicEditor-v0.2.2/js/epiceditor.min.js"></script>
        <script>
            var opts = {
                container: 'epiceditor',
                textarea: null,
                basePath: '/forums/EpicEditor-v0.2.2',
                clientSideStorage: false,
                localStorageName: 'EpicEditor-v0.2.2',
                useNativeFullscreen: false,
                parser: marked,
                file: {
                    name: 'EpicEditor-v0.2.2',
                    defaultContent: '',
                    autoSave: 100
                },
                theme: {
                    base: '/themes/base/epiceditor.css',
                    preview: '/themes/preview/github.css',
                    editor: '/themes/editor/epic-light.css'
                },
                button: {
                    preview: true,
                    fullscreen: true,
                    bar: "auto"
                },
                focusOnLoad: false,
                shortcut: {
                    modifier: 18,
                    fullscreen: 70,
                    preview: 80
                },
                string: {
                    togglePreview: 'Toggle Preview Mode',
                    toggleEdit: 'Toggle Edit Mode',
                    toggleFullscreen: 'Enter Fullscreen'
                },
                autogrow: true
            };
            var editor = new EpicEditor(opts).load();

            function getPostMD() {
                return editor.exportFile();
            }

            function getPostHTML() {
                return editor.exportFile(null, 'html', false);
            }

            $("#createthread").submit(function (e) {
                e.preventDefault();

                var md = getPostMD(),
                    html = getPostHTML();

                $.post("/forums/viewthread/submitPost.php", {"md": md, "html": html, "threadid": <?php echo $threadid; ?>}, function (data) {
                    console.log(data);
                    location.reload();
                });
            });

            var posteditor;

            function createPostEditor(postid) {
                $("#posteditor-" + postid).parent(".postcontents").find(".posted").css("display", "none");
                $("#posteditor-" + postid).parent(".postcontents").find(".postcontent").css("display", "none");
                $("#posteditor-" + postid).css("display", "block");

                var controls = $("#posteditor-" + postid).parent(".postcontents").find(".ownercontrols");

                controls.find(".canceledit").css("display", "block");
                controls.find(".saveedit").css("display", "block");
                controls.find(".edit").css("display", "none");
                controls.find(".delete").css("display", "none");

                var opts = {
                    container: 'posteditor-' + postid,
                    textarea: "mdcontent-" + postid,
                    basePath: '/forums/EpicEditor-v0.2.2',
                    clientSideStorage: true,
                    localStorageName: 'savedPostEdit-' + postid,
                    useNativeFullscreen: false,
                    parser: marked,
                    file: {
                        name: 'EpicEditor-v0.2.2',
                        defaultContent: '',
                        autoSave: 100
                    },
                    theme: {
                        base: '/themes/base/epiceditor.css',
                        preview: '/themes/preview/github.css',
                        editor: '/themes/editor/epic-light.css'
                    },
                    button: {
                        preview: true,
                        fullscreen: true,
                        bar: "auto"
                    },
                    focusOnLoad: true,
                    shortcut: {
                        modifier: 18,
                        fullscreen: 70,
                        preview: 80
                    },
                    string: {
                        togglePreview: 'Toggle Preview Mode',
                        toggleEdit: 'Toggle Edit Mode',
                        toggleFullscreen: 'Enter Fullscreen'
                    },
                    autogrow: false
                };
                posteditor = new EpicEditor(opts).load();
            }

            function cancelEdit(postid) {
                posteditor.unload();

                $("#posteditor-" + postid).parent(".postcontents").find(".posted").css("display", "block");
                $("#posteditor-" + postid).parent(".postcontents").find(".postcontent").css("display", "block");
                $("#posteditor-" + postid).css("display", "none");

                var controls = $("#posteditor-" + postid).parent(".postcontents").find(".ownercontrols");

                controls.find(".canceledit").css("display", "none");
                controls.find(".saveedit").css("display", "none");
                controls.find(".edit").css("display", "block");
                controls.find(".delete").css("display", "block");
            }

            function saveEdit(postid) {
                var mdcontent = posteditor.exportFile(),
                    htmlcontent = posteditor.exportFile(null, "html", false);

                $.post("/forums/viewthread/editPost.php", {"id": postid, "md": mdcontent, "html": htmlcontent}, function (data) {
                    console.log(data);

                    $("#posteditor-" + postid).parent(".postcontents").find(".postcontent").html(data);

                    cancelEdit(postid);
                });
            }

            function deletePost(postid) {
                var conf = confirm("Are you sure you want to hide this post?");

                if (conf) {
                    $.post("/forums/viewthread/deletePost.php", {"id": postid}, function (data) {
                        if (data == "post deleted") {
                            location.reload();
                        } else if (data == "thread deleted") {
                            location.href = "/forums/f/<?php echo $forumid; ?>";
                        } else {
                            alert(data);
                        }
                    });
                }
            }
        </script>
    <?php
    }

    if ($loggedin) {
        $dbf->query("INSERT IGNORE INTO forumthreadviews (UserID, ThreadID, LastViewDate) VALUES ('$userid', '$threadid',
        NOW()) ON DUPLICATE KEY UPDATE LastViewDate=NOW()");
    }
?>
</body>
</html>