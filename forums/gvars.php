<?php
    date_default_timezone_set("America/Los_Angeles");

    class gvars {
        public $database = "mcc";
        public $disqus = "mccdisqus";
        public $basefilepath = "/";
        public $userid = 1;

        function getUrl() {
            $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
            $url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
            $url .= $_SERVER["REQUEST_URI"];
            return $url;
        }
    }