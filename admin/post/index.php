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

        $user = $myusername;
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Posts</title>
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/alerts.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
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

                    <?php
                        if(isset($_GET['saved'])) {
                            ?>
                            <div class="alert-message success">
                                <a class="close icon-remove-sign" href="#"></a>

                                <p><strong>Success: </strong>The post has been saved</p>
                            </div>
                            <?php
                        }
                    ?>

                    <div class="innercontent" style="text-align:center;">
                        <h1 style="padding:0; margin:0;">Posts</h1>

                        <h3><a href="new.php">Create a new Post</a></h3>
                        <?php
                        $headlines = $dbf->getAllAssocResults( "SELECT PostID, Headline, Visible FROM posts" );
                        ?>
                        <ul class="columns">
                            <?php
                            for ( $i = 0; $i < count( $headlines ); $i++ ) {
                                if ( $headlines[ $i ][ 'Visible' ] == 0 ) {
                                    $tag = "(H)";
                                }
                                else {
                                    $tag = "(V)";
                                }

                                ?>
                                <li><a href="edit.php?id=<?php echo $headlines[ $i ][ 'PostID' ]; ?>"><?php echo "<b style='color:red;'>$tag</b> " . $headlines[ $i ][ 'Headline' ]; ?></a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </body>
        </html>
    <?
    }
    else {
        echo "Error: Unable to connect to DB.";
    }