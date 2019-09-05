<?
    require_once( '../../dbfunctions.php' );
    require_once( '../../userfunctions.php' );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    if ( !$loggedin ) {
        header( "Location: ../../?noredirect&notloggedin" );
        die();
    }

    $myusername = $_SESSION['username'];

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $userid      = $_SESSION['userid'];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if ( $permissions < 5 ) {
            header( "Location: ../" );
            die();
        }
        $postID = $_GET[ 'id' ];

        if ( isset( $_POST[ 'submit' ] ) && $_POST[ 'submit' ] != NULL ) {
            $headline = mysql_real_escape_string( $_POST[ 'headline' ] );
            $content  = mysql_real_escape_string( $_POST[ 'postcontent' ] );

            if ( isset( $_POST[ 'visible' ] ) ) {
                $visible = 1;
            }
            else {
                $visible = 0;
            }

            if ( isset( $_POST[ 'sticky' ] ) ) {
                $sticky = 1;
            }
            else {
                $sticky = 0;
            }

            $dbf->query( "UPDATE posts SET Headline='$headline', Content='$content', Visible='$visible', Sticky='$sticky' WHERE PostID='$postID'" );
            header("Location: /admin/post/?saved=true");
            die();
        }

        $postdata = $dbf->getAllAssocResults( "SELECT * FROM posts WHERE PostID='$postID'" );
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Posts</title>
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/admin.css">
            </head>

            <body>
                <?php require_once("../../masthead.php"); ?>

                <div id="content">
                    <div class="innercontent" style="margin-bottom:10px; text-align:center;">
                        <a href="../add">New Requirement</a>
                        &bull; <a href="../edit">Edit Requirements</a>
                        &bull; <a href="../skillcalc">Skill Calc Entries</a>
                        &bull; <a href="../flags">Manage Designer Flags</a>
                        <?php
                        if ( $permissions > 1 ) {
                            ?>
                            &bull; <a>Manage Front Page Posts</a>
                        <?php
                        }
                        ?>
                    </div>

                    <div class="innercontent" style="text-align:center;">
                        <form method="post">
                            <fieldset id="post">
                                <legend><h3>Edit Post</h3></legend>

                                <label for="headline">Headline:</label>
                                <input type="text" name="headline" id="headline" value="<?php echo $postdata[ 0 ][ 'Headline' ]; ?>">

                                <label for="postcontent">Content (use HTML):</label>
                                <textarea name="postcontent" id="postcontent"><?php echo $postdata[ 0 ][ 'Content' ] ?></textarea>
                                <?php
                                if ( $postdata[ 0 ][ 'Visible' ] == 1 ) {
                                    ?>
                                    <label style="display:inline;" for="visible"><input type="checkbox" checked="checked" id="visible" name="visible">Visible</label>
                                <?php
                                }
                                else {
                                    ?>
                                    <label style="display:inline;" for="visible"><input type="checkbox" id="visible" name="visible">Visible</label>
                                <?php
                                }

                                if ( $postdata[ 0 ][ 'Sticky' ] == 1 ) {
                                    ?>
                                    <label style="display:inline; margin-left:25px;" for="sticky"><input type="checkbox" checked="checked" id="sticky" name="sticky">Sticky</label>
                                <?php
                                }
                                else {
                                    ?>
                                    <label style="display:inline; margin-left:25px;" for="sticky"><input type="checkbox" id="sticky" name="sticky">Sticky</label>
                                <?php
                                }
                                ?>
                            </fieldset>

                            <input type="submit" name="submit" value="Save Post">
                        </form>
                    </div>
                </div>
            </body>
        </html>
    <?
    }
    else {
        echo "Error: Unable to connect to DB.";
    }