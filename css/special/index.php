<?php
    require_once("../../gvars.php");
    $g = new gvars;
    header("Location: " . $g->basefilepath);
    die();