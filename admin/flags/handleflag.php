<?php
    //session_start();
    require_once( "../../userfunctions.php" );
    require_once( "../../dbfunctions.php" );

    $dbf = new dbfunctions;
    $uf  = new userfunctions;

    $loggedin   = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];
    $userid     = $_SESSION[ 'userid' ];

    if ( !$loggedin ) {
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database ) or die( "Unable to connect to database" );

    $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
    if ( $permissions < 4 ) {
        die();
    }

    $capeid = mysql_real_escape_string($_POST['id']);
    $do = mysql_real_escape_string($_POST['do']);

    if($do == 1) { //Keep
        $dbf->query("DELETE FROM capeflags WHERE CapeID='$capeid'");
        echo mysql_error();
    } elseif($do == 0) { //Remove
        //Remove CapeColors
        $dbf->query("DELETE FROM capecolors WHERE CapeID='$capeid'");
        echo mysql_error();

        //Remove Colors
        $dbf->query("DELETE FROM colors WHERE ColorID IN ( SELECT ColorID FROM capecolors WHERE CapeID = '$capeid')");
        echo mysql_error();

        //Remove Capevotes
        $dbf->query("DELETE FROM capevotes WHERE CapeID='$capeid'");
        echo mysql_error();

        //Remove capeflags
        $dbf->query("DELETE FROM capeflags WHERE CapeID='$capeid'");
        echo mysql_error();

        //Remove favorites
        $dbf->query("DELETE FROM capefavorites WHERE CapeID='$capeid'");
        echo mysql_error();

        //Remove cape
        $dbf->query("DELETE FROM capes WHERE CapeID='$capeid'");
        echo mysql_error();
    } else {
        echo "error";
    }