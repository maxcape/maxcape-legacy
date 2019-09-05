<?php
    session_start();
    require_once( "../dbfunctions.php" );
    require_once( "../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf  = new userfunctions;

    $username = $_POST[ 'username' ];
    $password = $_POST[ 'password' ];

    $login = $uf->login($username, $password);

    if($login == 0) {
        header("Location: /ucp/tab/0");
        die();
    } else {
        header("Location: /user/login?&error=$login&username=$username");
        die();
    }