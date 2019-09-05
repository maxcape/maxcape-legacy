<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;
    require_once "../../userfunctions.php";
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    if(!$loggedin) {
        header("Location: /forums/");
    }

    $dbf->connectToDatabase($dbf->database) or die();
    $userid=$_SESSION['userid'];

    $userinfo = $dbf->queryToAssoc("SELECT * FROM users WHERE UserID = '$userid'");
    $username = $userinfo['Username'];
    $userlevel = $userinfo['PrivelegeLevel'];
    $rsn = $userinfo['RSN'];

    $forumid = $_GET['subforum'];
    $forumid = mysql_real_escape_string($forumid);

    $forum = $dbf->queryToAssoc("SELECT * FROM forums WHERE ForumID='$forumid'");

    if (count($forum) === 0) {
        header("Location: /forums/");
    }

    $forumname = $forum['Title'];
    $forumlocked = $forum['Locked'] == "0" ? false : true;

    $breadcrumbs = "<a class='breadcrumb' href='/forums/'>Forums Home</a> <a class='breadcrumb' href='/forums/f/$forumid'>$forumname</a>  <a class='breadcrumb' href='#'>Post</a>";

    $thread = "";
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Post Thread</title>

        <link rel="stylesheet" href="../header.css">
        <link rel="stylesheet" href="../forums.css">
        <link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">

        <script src="../jquery.js"></script>

        <script>
            $(document).ready(function() {
                $("#threadtitle").focus();
                $("#threadtitle").on("input", function() {
                    var maxChar = 50;
                    var value = $(this).val();

                    var remainingChars = maxChar - value.length;

                    if(remainingChars < 0) {
                        value = value.slice(0, maxChar);
                        remainingChars = 0;
                    }

                    var color;

                    if(remainingChars > 20) {
                        color="green";
                    } else if(remainingChars > 10) {
                        color="#DDCC00";
                    } else if(remainingChars > 2) {
                        color="#FFAA00";
                    } else {
                        color="red";
                    }

                    $(this).val(value);
                    $("#titleremaining").text(remainingChars).css("color", color);

                });

                $("#threadsubtitle").on("input", function() {
                    var maxChar = 75;
                    var value = $(this).val();

                    var remainingChars = maxChar - value.length;

                    if(remainingChars < 0) {
                        value = value.slice(0, maxChar);
                        remainingChars = 0;
                    }

                    var color;

                    if(remainingChars > 35) {
                        color="green";
                    } else if(remainingChars > 22) {
                        color="#DDCC00";
                    } else if(remainingChars > 5) {
                        color="#FFAA00";
                    } else {
                        color="red";
                    }

                    $(this).val(value);
                    $("#subtitleremaining").text(remainingChars).css("color", color);

                });
            });
        </script>
    </head>
    <body>
        <?php require_once "../header.php"; ?>

        <section id="maincontent">
            <section id="wrapper">
                <h1 class="categoryheader">Posting Thread in <?php echo $forumname; ?></h1>
                <?php
                    if(!$forumlocked || $userlevel >= 4) {
                ?>
                <form id="createthread" method="post" action="submitThread.php">
                    <div style="width:75%; float:left;">
                        <div class="tbcontainer">
                            <input type="text" id="threadtitle" placeholder="Thread Title"/>
                            <span class="remaining" id="titleremaining">50</span>
                        </div>
                        <div class="tbcontainer">
                            <input type="text" id="threadsubtitle" placeholder="Thread Subtitle (summary)"/>
                            <span class="remaining" id="subtitleremaining">75</span>
                        </div>

                        <div id="epiceditor"></div>
                    </div>

                    <div id="rightbar">
                        <div id="syntax">
                        <h2>Markdown Cheatsheet</h2>

                        <div class="mdHelpSection">
                            <h3>Line Break</h3>
                            <div class="mdHelp">
                                <div class="wys">
                                    <p>Add two spaces or returns<span class="highlight">&nbsp;&nbsp;</span></p>
                                    <p>&nbsp;</p>
                                    <p>After the line.</p>
                                </div>
                                <div class="wyg">
                                    <p>Add two spaces or returns</p>
                                    <p>After the line.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mdHelpSection">
                            <h3>Links</h3>

                            <div class="mdHelp">
                                <pre class="wys"><code>[link](example.com/)</code></pre>
                                <div class="wyg">
                                    <p><a href="#">link</a></p>
                                </div>
                            </div>
                        </div>

                        <div class="mdHelpSection">
                            <h3>Emphasis</h3>
                            <div class="mdHelp">
                            <pre class="wys"><code>**bold** *italic*
__bold__ _italic_</code></pre>
                                <div class="wyg">
                                    <p>
                                        <strong>bold</strong>
                                        <em>italic</em>
                                        <br>
                                        <strong>bold</strong>
                                        <em>italic</em>
                                    </p>
                                </div>
                            </div>
                        </div>

                            <div class="mdHelpSection">
                                <h3>Color</h3>
                                <div class="mdHelp">
                                    <pre class="wys"><code>&lt;font color='red'&gt;
    text
&lt;/font&gt;</code>

&mdash;Supports HEX color codes</pre>
                                    <div class="wyg">
                                        <font color="red">text</font>
                                    </div>
                                </div>
                            </div>

                        <div class="mdHelpSection">
                            <h3>Lists</h3>
                            <div class="mdHelp">
                            <pre class="wys">1. Foo
2. Bar
    * Baz</pre>
                            <div class="wyg">
                                <ol>
                                    <li>Foo</li>
                                    <li>Bar

                                        <ul>
                                            <li>Baz</li>
                                        </ul>
                                    </li>
                                </ol>
                            </div>
                            </div>
                        </div>

                        <div class="mdHelpSection">
                            <h3>Images</h3>
                            <div class="mdHelp">
                            <pre class="wys"><code>![alt](slayer.png)</code></pre>
                            <div class="wyg">
                                <p><img src="/images/slayer.png" alt="alt" title="Title"></p>
                            </div>
                            </div>
                        </div>
                        <div class="mdHelpSection">
                            <h3>Headers</h3>
                            <div class="mdHelp">
                            <pre class="wys"><code>#Header 1
##Header 2
###Header 3</code></pre>
                                <div class="wyg">
                                    <h1>Header 1</h1>
                                    <h2>Header 2</h2>
                                    <h3>Header 3</h3>
                                </div>
                            </div>
                        </div>

                        <div class="mdHelpSection">
                            <h3>Horizontal Rule</h3>
                            <div class="mdHelp">
                            <pre class="wys"><code>---
* * *
- - - -</code></pre>
                            <div class="wyg">
                                <hr>
                                <hr>
                                <hr>
                            </div>
                            </div>
                        </div>

                        <div class="mdHelpSection">
                            <h3>Code</h3>
                            <div class="mdHelp">
                            <pre class="wys"><code>```
function name() {
    //Do Something
}
```</code></pre>
                            <div class="wyg">
                                <pre><code>function name() {
    //Do Something
}</code></pre>
                            </div>
                            </div>
                        </div>

                        <div class="mdHelpSection">
                            <h3>Blockquotes</h3>
                            <div class="mdHelp">
                            <pre class="wys"><code>&gt; Blockquotes.</code></pre>
                            <div class="wyg">
                                <blockquote>
                                    <p>Blockquotes.</p>
                                </blockquote>
                            </div>
                            </div>
                        </div>
                        </div>
                        <div id="btncontainer">
                            <button type="submit">Submit Thread</button>
                        </div>
                    </div>
                </form>
                <?php
                    } else {
                        ?>
                <form>
                    <p>You do not have permision to post a thread in this forum.</p>
                    <p>Click <a href="/forums/">here</a> to return to forums home.</p>
                </form>
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

        <script src="../EpicEditor-v0.2.2/js/epiceditor.min.js"></script>
        <script>
            var opts = {
                container: 'epiceditor',
                textarea: null,
                basePath: '../EpicEditor-v0.2.2',
                clientSideStorage: true,
                localStorageName: 'ThreadPost <?php echo $forumname; ?>',
                useNativeFullscreen: false,
                parser: marked,
                file: {
                    name: 'EpicEditor-v0.2.2',
                    defaultContent: '',
                    autoSave: 100
                },
                theme: {
                    base: '/themes/base/epiceditor.css',
                    preview: '/themes/preview/preview.css',
                    editor: '/themes/editor/epic-light.css?1'
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
                autogrow: false
            };
            var editor = new EpicEditor(opts).load(function() {

            });


            function getPostMD() {
                return editor.exportFile();
            }

            function getPostHTML() {
                return editor.exportFile(null, 'html', false);
            }


            function submitThread() {
                var md = getPostMD(),
                    html = getPostHTML(),
                    title = $("#threadtitle").val(),
                    subtitle = $("#threadsubtitle").val();

                if(md != "" && html != "" && title != "") {
                    var submit = true;
                    if(subtitle == "") {
                        var conf = confirm("You are submitting a post without a subtitle.\n\nSubtitles are optional, but allow you to do a one sentence summary of your post.\n\nAre you sure you want to submit a post without a subtitle?");
                        if(!conf) {
                            submit = false;
                        }
                    }

                    if(submit) {
                        $.post("/forums/post/submitThread.php", {"md": md, "html": html, "title": title, "subtitle": subtitle, "forum": <?php echo $forumid; ?>}, function(data) {
                            localStorage.setItem("ThreadPost <?php echo $forumname; ?>", "");
                            location.href = "/forums/t/" + data;
                        });
                    }
                } else {
                    alert("A required field is not filled in.");
                }
            }

            $("#createthread").submit(function(e) {
                e.preventDefault();
                submitThread();
            });
        </script>
    </body>
</html>