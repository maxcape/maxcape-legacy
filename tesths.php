<?php
    $ch = curl_init( "http://hiscore.runescape.com/index_lite.ws?player=The+Orange" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    $result = curl_exec( $ch );
    curl_close( $ch );

    print_r($result);