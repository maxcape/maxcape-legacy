<?php
    date_default_timezone_set("America/Los_Angeles");
    error_reporting(0);

    class gvars {
        public $database = "ironclan";
        public $disqus = "";
        public $basefilepath = "/";
        public $LOGIN_LENGTH = 14; //Number of days to keep a user logged in for.

        function getUrl() {
            $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
            $url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
            $url .= $_SERVER["REQUEST_URI"];
            return $url;
        }
    }