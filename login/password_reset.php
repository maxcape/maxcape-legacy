<?php
    session_start();
    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $uf = new userfunctions;
    $dbf = new dbfunctions;

    $loggedin = $uf->isLoggedIn();

    if ( $loggedin ) {
        header( "Location: /" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    $error = "";

    if ( $db[ 'found' ] ) {
        $token = mysql_real_escape_string( $_GET[ 'token' ] );
        $userid = $dbf->queryToText( "SELECT UserID FROM recoveries WHERE Token='$token' AND Time > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND Used=0" );
        $username = $dbf->queryToText( "SELECT Username FROM users WHERE UserID='$userid'" );

        if ( isset( $_POST[ 'password' ] ) ) {
            $password        = mysql_real_escape_string( $_POST[ 'password' ] );
            $passwordconfirm = mysql_real_escape_string( $_POST[ 'confirmpassword' ] );
            $tkn            = mysql_real_escape_string( $_POST[ 'token' ] );


            if ( strlen( $password ) > 5 ) {
                if ( $password === $passwordconfirm ) {
                    $pswd = crypt( $password );

                    $dbf->query( "UPDATE users SET password='$pswd' WHERE UserID='$userid'" );
                    $dbf->query( "UPDATE recoveries SET Used=1 WHERE Token='$tkn'" );

                    $login = $uf->login( $username, $password );

                    if ( $login == 0 ) {
                        header( "Location: /?passwordchanged" );
                        die();
                    }
                }
                else {
                    $error = "Passwords do not match.";
                }
            }
            else {
                $error = "Password must be greater then 5 characters";
            }
        }

        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Forgotten Password</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/user.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    $(document).ready(function () {
                        $(".close").click(function () {
                            $(this).parent().fadeOut();
                        });
                    });
                </script>
            </head>

            <body>
                <?php require_once("../masthead.php"); ?>

                <div id="content">
                    <?php
                    if ( isset( $_GET[ 'token' ] ) ) {
                    $token = mysql_real_escape_string( $_GET[ 'token' ] );

                    $valid = $dbf->queryToAssoc( "SELECT * FROM recoveries WHERE Token='$token' AND Time > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND Used=0" );

                    if ( count( $valid ) > 0 ) {
                    ?>
                    <div style="padding:10px;">
                        <div id="login" class="blackbg">
                            <div class="alert-message info">
                                <a class="close icon-remove-sign" href="#"></a>

                                <p><strong>Username: </strong><?php echo $username; ?></p>
                            </div>

                            <?php
                            if ( $error != "" ) {
                                ?>
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Error: </strong><?php echo $error; ?></p>
                                </div>
                            <?php
                            }
                            ?>

                            <form id="loginform" action="#" method="post">
                                <fieldset class="clearfix">
                                    <input type="hidden" name="token" value="<?php echo $token; ?>">

                                    <p><span class="icon-lock"><label>Password</label></span><input name="password" type="password" placeholder="Password" required></p>
                                    <p><span class="icon-lock"><label>Confirm</label></span><input name="confirmpassword" type="password" placeholder="Confirm Password" required></p>
                                    <p><input type="submit" value="Reset Password"></p>
                                </fieldset>
                            </form>
                        </div>
                        <?php
                        }
                        else {
                            ?>
                            <div class="innercontent">
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Error: </strong>Your token is not valid.</p>
                                </div>

                                <p><a href="<?php echo $dbf->basefilepath; ?>nr">Click here</a> to return to home.</p>
                            </div>
                        <?php
                        }
                        } else {
                            ?>
                            <div class="innercontent">
                                <div class="alert-message error">
                                    <a class="close icon-remove-sign" href="#"></a>

                                    <p><strong>Error: </strong>Your token is not valid.</p>
                                </div>

                                <p><a href="<?php echo $dbf->basefilepath; ?>nr">Click here</a> to return to home.</p>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
            </body>
        </html>
        <?php

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "Cannot connect to database";
    }