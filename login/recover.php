<?php
    session_start();
    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $uf = new userfunctions;
    $dbf = new dbfunctions;

    $loggedin = $uf->isLoggedIn();

    if($loggedin) {
        header( "Location: ../ucp/" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {

        if(isset($_POST['email'])) {
            $email = mysql_real_escape_string($_POST['email']);

            $exists = $dbf->queryToAssoc("SELECT * FROM users WHERE Email='$email'");

            if(count($exists) > 0) {
                $userid = $exists['UserID'];
                $token = md5($email . time());

                $dbf->query("INSERT INTO recoveries (UserID, Token, Time) VALUES ('$userid', '$token', NOW())");

                $reseturl =  "http://www.maxcape.com/user/reset?token=$token";

                $to      = $email;
                $subject = 'Password Reset';
                $headers = 'From: Max/Comp Cape Calc Password Recovery <noreply@maxcape.com>' . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'Content-Type: text/html; charset=ISO-8859-1';

                $message = "<h1>Hello!</h1>
                                    <p>You have recently requested a password reset for your Max/Comp cape calc profile. If you did not request this, ignore it. The following link will expire 1 hour after it was sent.</p>
                                    <p><a href='$reseturl'>Click here</a> to reset your password.</p>
                                    <p>If the above link doesn't work, copy and paste the following url into your address bar:</p>
                                    <p style=\"text-align:center;\">$reseturl</p>
                                    <p>Please do not reply to this email. This email account is never seen by human eyes. If you have a question, feel free to email The Orange at <a href=\"evan.riley@live.com\">evan.riley@live.com</a></p>";

                if(mail( $to, $subject, $message, $headers )) {
                    header("Location: /nr?resetrequested");
                    die();
                } else {
                    echo "Cannot send email";
                }
            }
        }

        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Forgotten Password</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/user.css">
            </head>

            <body>
                <?
                require_once("../masthead.php");
                ?>

                <div id="content">
                    <div style="padding:10px;">
                        <div id="login" class="blackbg">
                            <form id="loginform" action="#" method="post">
                                <fieldset class="clearfix">
                                    <p><span class="icon-envelope-alt"><label>Email</label></span><input name="email" type="text" placeholder="Email" required></p>
                                    <p style="margin-bottom:0;"><input style="margin-bottom:0;" type="submit" value="Request Reset"></p>
                                </fieldset>
                            </form>
                        </div>
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