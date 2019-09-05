<?php
    function getStats($name) {
        $ch = curl_init( "http://hiscore.runescape.com/index_lite.ws?player=$name" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

        $result = curl_exec( $ch );
        curl_close( $ch );

        return $result;
    }

    /*function getStats($name) {
        $results = file_get_contents("http://hiscore.runescape.com/index_lite.ws?player=$name");
        return $results;
    }*/

    echo "The following was returned from the hslite: ";
    echo "<div style='height:500px; width:500px; margin: 20px auto; border:thin solid black;'>";
    print_r(getStats("The Orange"));
    echo "</div>";