<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;
    require_once "../../userfunctions.php";
    $uf = new userfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database.");

    $loggedin = $uf->isLoggedIn();

    $userid = $_SESSION['userid'];
    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

    if (!$loggedin || intval($userlevel) < 3) {
        header("Location: /forums/");
    }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Maxcape Forums Administration</title>

        <link rel="stylesheet" href="../forums.css"/>
        <link rel="stylesheet" href="../header.css"/>
        <link rel="stylesheet" href="admin.css"/>
        <link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <script src="../jquery.js"></script>
    </head>
    <body>
        <?php require_once "../header.php"; ?>

        <section id="maincontent">
            <section id="wrapper">
                <div class="admincontrolcontainer">
                    <?php
                        $categories = $dbf->getAllAssocResults("SELECT * FROM forumcategories");
                    ?>
                    <table border="1" id="forumtree">
                        <tr>
                            <th>Category</th>
                            <th>Forum</th>
                        </tr>
                        <?php
                            foreach ($categories as $cat) {
                                $catid = $cat['CategoryID'];
                                $subforums = $dbf->getAllAssocResults("SELECT * FROM forums WHERE CategoryID='$catid'");
                                ?>
                                <tr>
                                    <th rowspan="<?php echo count($subforums) + 1; ?>" onclick="editCategory('<?php echo $cat['Title']; ?>', '<?php echo $cat['CategoryID']; ?>');"><?php echo $cat['Title']; ?></th>
                                    <td onclick="editSubforum('<?php echo $subforums[0]['ForumID']; ?>', '<?php echo $cat['CategoryID']; ?>', '<?php echo $subforums[0]['Title']; ?>', '<?php echo $subforums[0]["Description"]; ?>');"><?php echo $subforums[0]['Title']; ?></td>
                                </tr>
                                <?php
                                for ($i = 1; $i < count($subforums); $i++) {
                                    ?>
                                    <tr>
                                        <td onclick="editSubforum('<?php echo $subforums[0]['ForumID']; ?>', '<?php echo $cat['CategoryID']; ?>', '<?php echo $subforums[$i]['Title']; ?>', '<?php echo $subforums[$i]["Description"]; ?>');"><?php echo $subforums[$i]['Title']; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                <tr>
                                    <td><a href="javascript:void(0);" onclick="popup('#addsubforumcontainer');">+ Add Subforum</a></td>
                                </tr>
                            <?php
                            }
                        ?>
                        <tr>
                            <th colspan="2"><a href="javascript:void(0);" onclick="popup('#addcategorycontainer');">+ Add Category</a></th>
                        </tr>
                    </table>
                </div>
            </section>
        </section>

        <section id="footer">
            <section id="footerinner">
                <p>Maxcape.com &copy; The Orange 2012 - 2014</p>
            </section>
        </section>

        <section id="blinder">
            <section id="editsubforumcontainer">
                <div class="inner">
                    <input type="hidden" value="" id="editsubforumid">
                    <label for="editsubforumcategory">Category:</label>
                    <select id="editsubforumcategory">
                        <option value="0">Choose a Category</option>
                        <?php
                        foreach($categories as $cat) {
                        ?>
                        <option value="<?php echo $cat['CategoryID']; ?>"><?php echo $cat['Title']; ?></option>
                        <?php
                        }
                        ?>
                    </select>

                    <label for="editsubforumtitle">Title:</label>
                    <input type="text" id="editsubforumtitle"/>

                    <label for="editsubforumdesc">Description: </label>
                    <textarea id="editsubforumdesc"></textarea>

                    <button onclick="savesubforumedit();">Save Changes</button>
                </div>
            </section>
            <section id="editcategorycontainer">
                <div class="inner">
                    <input type="hidden" value="" id="editcategoryid">
                    <label for="editcategorytitle">Title: </label>
                    <input type="text" id="editcategorytitle"/>

                    <button onclick="savecategoryedit();">Save Changes</button>
                </div>
            </section>
            <section id="addsubforumcontainer">
                <div class="inner">
                    <label for="addsubforumcategory">Category:</label>
                    <select id="addsubforumcategory">
                        <option value="0">Choose a Category</option>
                        <?php
                            foreach($categories as $cat) {
                                ?>
                                <option value="<?php echo $cat['CategoryID']; ?>"><?php echo $cat['Title']; ?></option>
                            <?php
                            }
                        ?>
                    </select>

                    <label for="addsubforumtitle">Title:</label>
                    <input type="text" id="addsubforumtitle"/>

                    <label for="addsubforumdesc">Description: </label>
                    <textarea id="addsubforumdesc"></textarea>

                    <button onclick="addsubforum();">Add Subforum</button>
                </div>
            </section>
            <section id="addcategorycontainer">
                <div class="inner">
                    <label for="addcategorytitle">Title: </label>
                    <input type="text" id="addcategorytitle"/>

                    <button onclick="addcategory();">Save Changes</button>
                </div>
            </section>
        </section>

        <script>
            function popup(container) {
                var blinder = $("#blinder"),
                    form = $(container);

                form.css("display", "block");
                blinder.fadeIn();

                center(container);
            }

            function center(el) {
                var target = $(el),
                    h = target.height() + 20,
                    w = target.width() + 20,
                    leftPos = (($(window).width() / 2) - (w / 2)),
                    topPos = (($(window).height() / 2) - (h / 2));

                target.css("left", leftPos + "px").css("top", topPos + "px");
            }

            function editCategory(title, id) {
                popup("#editcategorycontainer");
                $("#editcategoryid").val(id);
                $("#editcategorytitle").val(title);
            }

            function editSubforum(forumid, catid, title, desc) {
                popup("#editsubforumcontainer");

                $("#editsubforumid").val(forumid);
                $("#editsubforumcategory").val(catid);
                $("#editsubforumtitle").val(title);
                $("#editsubforumdesc").val(desc);
            }

            $("#blinder").click(function() {
                $(this).fadeOut();
                $(this).children().css("display","none");
            });

            $("#blinder").children().click(function(e) {
                e.stopPropagation();
            });

            function addcategory() {
                var title = $("#addcategorytitle").val();

                $.post("createCategory.php", {"title": title}, function(data) {
                    location.reload();
                })
            }

            function addsubforum() {
                var title = $("#addsubforumtitle").val(),
                    category = $("#addsubforumcategory").val(),
                    desc = $("#addsubforumdesc").val();

                $.post("createSubforum.php", {"title":title, "catid": category, "desc": desc}, function(data) {
                    location.reload();
                });
            }

            function savecategoryedit() {
                var id = $("#editcategoryid").val(),
                    title = $("#editcategorytitle").val();

                $.post("editCategory.php", {"id": id, "title": title}, function(data) {
                    location.reload();
                });
            }

            function savesubforumedit() {
                var id = $("#editsubforumid").val(),
                    title = $("#editsubforumtitle").val(),
                    category = $("#editsubforumcategory").val(),
                    desc = $("#editsubforumdesc").val();

                $.post("editSubforum.php", {"id": id, "title": title, "desc": desc, "category": category}, function(data) {
                    location.reload()
                });
            }
        </script>
    </body>
</html>