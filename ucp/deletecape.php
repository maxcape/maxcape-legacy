<?php
    session_start();
    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf  = new userfunctions;

    $isloggedin = $uf->isLoggedIn();

    if ( !$isloggedin ) {
        header( "Location: /nr" );
    }

    $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

    $capeid = mysql_real_escape_string( $_POST[ 'id' ] );
    $userid = $_SESSION[ 'userid' ];

    $capeuserid = $dbf->queryToText( "SELECT UserID FROM capes WHERE CapeID='$capeid'" );

    if ( $userid === $capeuserid ) {

        $dbf->query( "DELETE FROM capecolors WHERE CapeID='$capeid'" );
        echo mysql_error();

        //Remove Colors
        $dbf->query( "DELETE FROM colors WHERE ColorID IN ( SELECT ColorID FROM capecolors WHERE CapeID = '$capeid')" );
        echo mysql_error();

        //Remove Capevotes
        $dbf->query( "DELETE FROM capevotes WHERE CapeID='$capeid'" );
        echo mysql_error();

        //Remove capeflags
        $dbf->query( "DELETE FROM capeflags WHERE CapeID='$capeid'" );
        echo mysql_error();

        //Remove favorites
        $dbf->query( "DELETE FROM capefavorites WHERE CapeID='$capeid'" );
        echo mysql_error();

        //Remove cape
        $dbf->query( "DELETE FROM capes WHERE CapeID='$capeid'" );
        echo mysql_error();
    } else {
        echo "You do not have permission to delete this cape.";
    }