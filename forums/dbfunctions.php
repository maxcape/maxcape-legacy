<?php
    require_once("gvars.php");
    class dbfunctions extends gvars {
        //Database functions
        function connectToDatabase( $database, $user_name = 'evan', $dbPswd = 'orange$', $server = "localhost" ) {
            $db_handle = mysql_connect( $server, $user_name, $dbPswd );
            $db_found  = mysql_select_db( $database, $db_handle );

            $db_info = array( 'handle' => $db_handle, 'found' => $db_found );

            return $db_info;
        }

        function disconnectFromDatabase( $handle ) {
            mysql_close( $handle );
        }

        function queryToAssoc( $query ) {
            $result = $this->query( $query );

            if ( mysql_num_rows( $result ) > 0 ) {
                $result = mysql_fetch_assoc( $result );
                return $result;
            }
        }

        function queryToText( $query ) {
            $result = $this->queryToArray( $query );
            $result = $result[ 0 ];

            return $result;
        }

        function queryToArray( $query ) {
            $result = $this->query( $query );

            if ( mysql_num_rows( $result ) > 0 ) {
                $result = mysql_fetch_array( $result );
                return $result;
            }
        }

        function query( $query ) {
            $result = mysql_query( $query );

            return $result;
        }

        function getAllAssocResults( $query ) {
            $array  = array();
            $search = $this->query( $query );

            while ( $results = mysql_fetch_assoc( $search ) ) {
                $array[ ] = $results;
            }

            return $array;
        }
    }
