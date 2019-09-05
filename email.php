<?php
    $to      = 'evan@theorange.me';
    $subject = 'Password Reset';
    $headers = 'From: Max/Comp Cape Calc Password Recovery <noreply@theorange.me>' . "\r\n" .
        'X-Mailer: PHP/' . phpversion() . "\r\n" .
        'Content-Type: text/html; charset=ISO-8859-1';

    $message = '<h1>Hello!</h1>

                <p>You have recently requested a password reset for your Max/Comp cape calc profile. If you did not request this, ignore it. The following link will expire 1 hour after it was sent.</p>

                <p><a href="http://theorange.me/calcs/capes/login/password_reset.php">Click here</a> to reset your password.</p>
                <p>If the above link doesn\'t work, copy and paste the following url into your address bar:</p>
                <p style="text-align:center;">http://theorange.me/calcs/capes/login/password_reset.php</p>

                <p>Please do not reply to this email. This email account is never seen by human eyes. If you have a question, feel free to email The Orange at <a href="mailto:evan@theorange.me">evan@theorange.me</a></p>';

    mail($to, $subject, $message, $headers);