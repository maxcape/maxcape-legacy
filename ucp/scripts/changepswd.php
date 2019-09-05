<?php
    session_start();
    require_once("../../dbfunctions.php");
    $dbf = new dbfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $current = mysql_real_escape_string( $_POST[ 'currentpswd' ] );
        $new     = mysql_real_escape_string( $_POST[ 'newpswd' ] );
        $confirm = mysql_real_escape_string( $_POST[ 'confirmnewpswd' ] );

        $userid = $_SESSION[ 'userid' ];

        $info = $dbf->queryToAssoc("SELECT * FROM users WHERE UserID='$userid' LIMIT 1");

        if(count($info) > 0) {
            if(crypt($current, $info['Password']) == $info['Password']) {
                if($new === $confirm) {
                    $newpswd = crypt($new);
                    $dbf->query("UPDATE users SET Password='$newpswd' WHERE UserID='$userid'");

                    echo 0;
                } else {
                    echo 3;
                }
            } else {
                echo 2;
            }
        } else {
            echo 1;
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }