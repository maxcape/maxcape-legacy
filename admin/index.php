<?php
    session_start();
    require_once( '../dbfunctions.php' );
    require_once( '../userfunctions.php' );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION['username'];

    if ( !$loggedin ) {
        header( "Location: ../?noredirect&notloggedin" );
        die();
    }

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        $userid      = $_SESSION[ 'userid' ];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if ( $permissions < 4 ) {
            header( "Location: ../?noredirect&permissionerror" );
            die();
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Admin</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <style>
                    h1, h3 {
                        margin:0;
                        padding:0;
                    }

                    .innercontent ul {
                        list-style-type: none;
                        margin:10px auto;
                        width:300px;
                    }

                    .innercontent ul li {
                        font-size: 22px;
                        position: relative;
                        height: 40px;
                        border-bottom:thin solid #DDD;
                        border-top:thin solid #999;
                    }

                    .innercontent ul li:first-of-type {
                        border-top:none;
                    }

                    .innercontent ul li:last-of-type {
                        border-bottom:none;
                    }

                    .innercontent ul li span {
                        position: absolute;
                        font-size: 32px;
                    }

                    .innercontent a {
                        position: absolute;
                        margin: 5px 0 0 15%;
                        text-decoration: none;
                    }

                    .innercontent a:hover {
                        text-decoration: underline;
                    }

                    table {
                        margin:0 auto;
                    }

                    table tr:first-of-type {
                        background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(255, 255, 255, .15)), to(rgba(0, 0, 0, .25))), -webkit-gradient(linear, left top, right bottom, color-stop(0, rgba(255, 255, 255, 0)), color-stop(0.5, rgba(255, 255, 255, .1)), color-stop(0.501, rgba(255, 255, 255, 0)), color-stop(1, rgba(255, 255, 255, 0)));
                        background: -moz-linear-gradient(top, rgba(255, 255, 255, .15), rgba(0, 0, 0, .25)), -moz-linear-gradient(left top, rgba(255, 255, 255, 0), rgba(255, 255, 255, .1) 50%, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0));
                        background: linear-gradient(top, rgba(255, 255, 255, .15), rgba(0, 0, 0, .25)), linear-gradient(left top, rgba(255, 255, 255, 0), rgba(255, 255, 255, .1) 50%, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0));
                    }

                    table tr:first-of-type th {
                        text-align:center;
                    }

                    table tr:first-of-type th:first-of-type {
                         border-top-left-radius:5px;
                     }

                    table tr:first-of-type th:last-of-type {
                        border-top-right-radius:5px;
                    }

                    table tr:last-of-type th:first-of-type {
                        border-bottom-left-radius:5px;
                    }

                    table tr:last-of-type td:last-of-type {
                        border-bottom-right-radius:5px;
                    }

                    table tr:last-of-type th, table tr:last-of-type td {
                        border-bottom:thin solid black;
                    }

                    table tr td:last-of-type, table tr:first-of-type th:last-of-type {
                        border-right:thin solid black;
                    }


                    table th, table td {
                        border-top:thin solid black;
                        border-left:thin solid black;
                        padding:5px;
                        width:200px;
                        text-align:left;
                    }
                </style>
            </head>

            <body>
                <?php require_once("../masthead.php"); ?>

                <div id="content">
                    <div class="innercontent" style="text-align:center;">
                        <h1>Admin Access</h1>

                        <h3>Welcome, <? echo $_SESSION[ 'username' ]; ?></h3>

                        <ul>
                            <li><span class="icon-plus"></span><a href="add">Add Requirement</a></li>
                            <li><span class="icon-edit"></span><a href="edit">Edit Requirements</a></li>
                            <li><span class="icon-pencil"></span><a href="skillcalc">Skill Calc Entries</a></li>
                            <li><span class="icon-flag"></span><a href="flags">Manage Designer Flags</a></li>
                            <?php
                            if ( $permissions == "5" ) {
                                ?>
                                <li><span class="icon-edit"></span><a href="post">Manage Posts</a></li>
                                <li><span class="icon-plus"></span><a href="giveaway">Manage Giveaways</a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </body>
        </html>
    <?
    }
    else {
        echo "Error: Unable to connect to DB.";
    }