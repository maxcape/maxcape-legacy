<?php
    session_start();
    require_once("../dbfunctions.php");
    require_once("../userfunctions.php");
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION['username'];

    if (!$loggedin) {
        ?>
        <script>
            document.location = "/nr?notloggedin";
        </script>
    <?php
    }

    $userid = $_SESSION['userid'];

    $db = $dbf->connectToDatabase($dbf->database);

    $pages = array("Account", "Checkpoints", "Regular Requirements", "Trimmed Requirements", "Options", "My Capes", "My Logs");

    if ($db['found']) {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 0; //0: Account, 1: Checkpoints, 2: Regular, 3: Trimmed
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title><?php echo $pages[$tab]; ?> - User Control Panel - Max/Comp Cape Calc</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/ucp.css">


                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.masonry.min.js"></script>
                <?php
                    if ($tab == 0) {
                        require_once("tabs/account/scripts.php");
                    } else if ($tab == 1) {
                        require_once "tabs/checkpoints/scripts.php";
                    } else if ($tab == 2) {
                        require_once "tabs/regular/scripts.php";
                    } else if ($tab == 3) {
                        require_once "tabs/trimmed/scripts.php";
                    } else if ($tab == 4) {
                        require_once "tabs/options/scripts.php";
                    } else if ($tab == 5) {
                        require_once "tabs/capes/scripts.php";
                    } else if ($tab == 6) {
                        require_once "tabs/logs/scripts.php";
                    }
                ?>
            </head>

            <body>
                <?php
                    require_once("../masthead.php");
                ?>

                <div class="ad" id="topad">
                    <?php $dbf->ad(9305151607); ?>
                </div>


                <div id="main">
                    <div id="sidebar" class="bluebg">
                        <ul>
                            <li <?php if ($tab == 0) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/0">Account</a></li>
                            <li <?php if ($tab == 4) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/4">Options</a></li>
                            <li <?php if ($tab == 1) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/1">Checkpoints</a></li>
                            <li <?php if ($tab == 5) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/5">My Capes</a></li>
                            <li <?php if ($tab == 2) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/2">Regular Requirements</a></li>
                            <li <?php if ($tab == 3) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/3">Trimmed Requirements</a></li>
                            <li <?php if ($tab == 6) {
                                echo "class='active'";
                            } ?>><a href="<?php echo $dbf->basefilepath; ?>ucp/tab/6">My Logs</a></li>
                        </ul>
                    </div>

                    <div id="content">
                        <?php
                            if ($tab == 0) {
                                require_once "tabs/account/page.php";
                            } else if ($tab == 1) {
                                require_once "tabs/checkpoints/page.php";
                            } else if ($tab == 2) {
                                require_once "tabs/regular/page.php";
                            } else if ($tab == 3) {
                                require_once "tabs/trimmed/page.php";
                            } else if ($tab == 4) {
                                require_once "tabs/options/page.php";
                            } else if ($tab == 5) {
                                require_once "tabs/capes/page.php";
                            } else if ($tab == 6) {
                                require_once "tabs/logs/page.php";
                            }
                        ?>
                    </div>
                </div>
            </body>
        </html>
        <?php



        $dbf->disconnectFromDatabase($db['handle']);
    } else {
        $dbf->disconnectFromDatabase($db['handle']);
        echo "Cannot find or connect to database";
    }