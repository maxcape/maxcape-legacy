<?php
    session_start();
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "../userfunctions.php" );
    $uf = new userfunctions;

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $username        = mysql_real_escape_string( $_POST[ 'username' ] );
        $email           = mysql_real_escape_string( $_POST[ 'email' ] );
        $rsn             = mysql_real_escape_string( $_POST[ 'rsn' ] );
        $password        = mysql_real_escape_string( $_POST[ 'password' ] );
        $passwordconfirm = mysql_real_escape_string( $_POST[ 'passwordconfirm' ] );

        $info = "&username=$username&email=$email&rsn=$rsn";

        if ( strlen( $password ) >= 5 ) {
            if ( $password === $passwordconfirm ) {
                $checkname = $dbf->queryToAssoc( "SELECT * FROM users WHERE Username='$username'" );
                if ( count( $checkname ) == 0 ) {
                    $checkemail = $dbf->queryToAssoc( "SELECT * FROM users WHERE Email='$email'" );
                    if ( count( $checkemail ) == 0 ) {
                        $pswd = crypt( $password );

                        $dbf->query( "INSERT INTO users (Username, Password, Email, RSN, JoinDate) VALUES ('$username', '$pswd', '$email', '$rsn', NOW())" );

                        $validate = $uf->login( $username, $password );

                        if ( $validate == 0 ) {
                            header( "Location: ../ucp/" );
                            die();
                        }
                        else {
                            header( "Location: ../login/?action=register&error=5&error2=$validate" . $info );
                            $dbf->disconnectFromDatabase( $db[ 'handle' ] );
                            die();
                        }
                    }
                    else {
                        header( "Location: ../login/?action=register&error=4" . $info );
                        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
                        die();
                    }
                }
                else {
                    header( "Location: ../login/?action=register&error=3" . $info );
                    $dbf->disconnectFromDatabase( $db[ 'handle' ] );
                    die();
                }
            }
            else {
                header( "Location: ../login/?action=register&error=2" . $info );
                $dbf->disconnectFromDatabase( $db[ 'handle' ] );
                die();
            }
        }
        else {
            header( "Location: ../login/?action=register&error=1" . $info );
            $dbf->disconnectFromDatabase( $db[ 'handle' ] );
            die();
        }

        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
