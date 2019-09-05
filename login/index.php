<?php
    session_start();
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "../userfunctions.php" );
    $uf = new userfunctions;

    $isloggedin = $uf->isLoggedIn();

    if ( $isLoggedIn ) {
        header( "Location: ../ucp/" );
        die();
    }

    $pages = array( "login", "register", "logout" );
    $action = "";

    if ( isset( $_GET[ 'action' ] ) ) {
        if ( in_array( $_GET[ 'action' ], $pages ) ) {
            $action = $_GET[ 'action' ];
        }
        else {
            header( "Location: /user/login" );
            die();
        }
    }
    else {
        header( "Location: /user/login" );
        die();
    }

    $page_title = ucwords( $action ) . "- Max/Comp Cape Calc";

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        if ( $action == "logout" ) {
            $uf->logout();
            header( "Location: /nr?loggedout" );
            die();
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title><?php echo ucwords( $action ); ?> - Max/Comp Cape Calc</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/user.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    $(document).ready(function() {
                        $(".close").click(function() {
                            $(this).parent().fadeOut();
                        });
                    });
                </script>
            </head>

            <body>
                <?php
                require_once("../masthead.php");
                ?>

                <div id="content">
                    <div style="padding:10px;">
                        <?php
                        if ( $action == "login" ) {
                            ?>
                            <div id="login" class="blackbg">
                                <?php
                                if ( isset( $_GET[ 'error' ] ) ) {
                                    switch ( $_GET[ 'error' ] ) {
                                        case 1:
                                            $message = "Username or Password is incorrect.";
                                            break;
                                        case 2:
                                            $message = "Username or Password is incorrect.";
                                            break;
                                        case 3:
                                            $message = "Unable to connect to database.";
                                            break;
                                    }
                                    ?>
                                    <div class="alert-message error">
                                        <a class="close icon-remove-sign" href="#"></a>
                                        <p><strong>Error: </strong><?php echo $message; ?></p>
                                    </div>
                                <?php
                                }
                                ?>
                                <form id="loginform" action="login.php" method="post">
                                    <fieldset class="clearfix">
                                        <p><span class="icon-user"><label>Username</label></span><input name="username" value="<?php if ( isset( $_GET[ 'username' ] ) ) {
                                                echo  $_GET[ 'username' ];
                                            } ?>" type="text" placeholder="Username (Login Name)" required></p>
                                        <p><span class="icon-lock"><label>Password</label></span><input name="password" type="password" placeholder="Password" required></p>
                                        <p><input type="submit" value="Sign In"></p>
                                    </fieldset>
                                </form>

                                <p style="color:#888;"><a href="recover">Forgot password?</a><span class="icon-arrow-right"></span></p>

                                <p style="color:#888;">Don't have an account? <a href="register">Register</a><span class="icon-arrow-right"></span></p>
                            </div>
                        <?php
                        }
                        else {

                            ?>
                            <div id="login" class="blackbg">
                                <?php
                                if ( isset( $_GET[ 'error' ] ) ) {
                                    switch ( $_GET[ 'error' ] ) {
                                        case 1:
                                            $message = "Password must be at least 5 characters.";
                                            break;
                                        case 2:
                                            $message = "Passwords do not match.";
                                            break;
                                        case 3:
                                            $message = "This username is already taken.";
                                            break;
                                        case 4:
                                            $message = "This email is already in use.";
                                            break;
                                        case 5:
                                            $message = "Unknown error :(";
                                            break;
                                    }

                                    if(isset($_GET['error2'])) {
                                        switch($_GET['error2']) {
                                            case 1:
                                                $message = "Password is incorrect.";
                                                break;
                                            case 2:
                                                $message = "Username is incorrect.";
                                                break;
                                            case 3:
                                                $message = "Unable to connect to database.";
                                                break;
                                        }
                                    }
                                    ?>
                                    <div class="alert-message error">
                                        <a class="close icon-remove-sign" href="#"></a>
                                        <p><strong>Error: </strong><?php echo $message; ?></p>
                                    </div>
                                <?php
                                }
                                ?>
                                <form id="registerform" action="register.php" method="post">
                                    <fieldset class="clearfix">
                                        <p <?php echo $_GET['error'] == 3 ? 'style="outline: thin solid red;"' : ""; ?>><span class="icon-user"><label>Username</label></span><input name="username" type="text" placeholder="Username (Login Name)" required value="<?php if(isset($_GET['username'])) { echo $_GET['username']; } ?>"></p>
                                        <p <?php echo $_GET['error'] == 4 ? 'style="outline: thin solid red;"' : ""; ?>><span class="icon-envelope-alt"><label>Email</label></span><input name="email" type="text" placeholder="Email" required value="<?php if(isset($_GET['email'])) { echo $_GET['email']; } ?>"></p>
                                        <p><span class="icon-group"><label>RSN</label></span><input name="rsn" type="text" placeholder="RuneScape Name" required value="<?php if(isset($_GET['rsn'])) { echo $_GET['rsn']; } ?>"></p>
                                        <p <?php echo $_GET['error'] == 2 || $_GET['error'] == 1 ? 'style="outline: thin solid red;"' : ""; ?> ><span class="icon-lock"><label>Password</label></span><input name="password" type="password" placeholder="Password" required></p>
                                        <p <?php echo $_GET['error'] == 2 ? 'style="outline: thin solid red;"' : ""; ?> ><span class="icon-lock"><label>Confirm</label></span><input name="passwordconfirm" type="password" placeholder="Confirm Password" required></p>
                                        <p><input type="submit" value="Register"></p>
                                    </fieldset>
                                </form>

                                <p style="color:#888;">Already have an account? <a href="login">Login</a><span class="icon-arrow-right"></span></p>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
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