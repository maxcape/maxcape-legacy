<?
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;

    require_once( "../userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION['username'];

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        ?>
        <!DOCTYPE html>

        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <title>Search - Max/Comp Cape Calc</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/search.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    $(document).ready(function () {
                        $("#usersearch").submit(function (e) {
                            e.preventDefault();

                            if ($("#searchname").val() != "") {
                                var textbox = $("#searchname").clone(true);
                                var button = $("#submitbtn").clone(true);
                                $("#usersearch").empty().append($("<img class='loader' src='../images/load.gif'>")).animate({
                                    width: "126px"
                                }, 150);

                                $.post("search.php", {
                                    search: textbox.val()
                                }, function (data) {
                                    $("#usersearch").animate({
                                        margin: '0px auto',
                                        width: '450px'
                                    }, 150, function () {
                                        $("#usersearch").empty().append(textbox).prepend(button);
                                        textbox.focus();
                                    });

                                    $("#searchresults").empty().append(data);
                                });
                            }
                        });
                    });

                    function goToProfile(user) {
                        location.href = "../profile/" + user;
                    }
                </script>
            </head>
            <body>
                <?php
                require_once("../masthead.php");
                ?>

                <div id="content">
                    <div id="maincontent">
                        <div class="innercontent searchbox">
                            <form id="usersearch" class="form-wrapper cf">
                                <input type="text" id="searchname" name="searchname" placeholder="Search for a profile" required="required" pattern="^[a-zA-Z0-9_ ]*$">
                                <button type="submit" id="submitbtn">Search</button>
                            </form>

                            <div id="searchresults">

                            </div>
                        </div>
                    </div>

                    <div class="sidebar">
                        <?
                        $totalUsers = $dbf->queryToText( "SELECT COUNT(*) FROM users" );
                        $totalViews = $dbf->queryToText( "SELECT SUM(ProfileViews) FROM users" );
                        $checkpoints = $dbf->queryToText( "SELECT COUNT(*) FROM checkpoints" );

                        $last10 = $dbf->getAllAssocResults( "SELECT Username, RSN, LastProfileView FROM users ORDER BY LastProfileView DESC LIMIT 10" );
                        ?>
                        <div class="stats">
                            <h2>Statistics</h2>

                            <p>Total Users</p>

                            <h3><? echo number_format( $totalUsers ); ?></h3>

                            <p>Total Profile Views</p>

                            <h3><? echo number_format( $totalViews ); ?></h3>

                            <p>Total Users With Checkpoints</p>

                            <h3><? echo number_format( $checkpoints ); ?></h3>
                        </div>

                        <h2>Recently Viewed</h2>

                        <ul>
                            <?

                            for ( $i = 0; $i < 10; $i++ ) {
                                ?>
                                <li>
                                    <a href="../profile/<? echo str_replace( " ", "+", $last10[ $i ][ 'Username' ] ); ?>"><? echo $last10[ $i ][ 'Username' ]; ?></a> <span
                                        class="datetext"><? echo date( 'M d Y, h:i:sa', strtotime( $last10[ $i ][ 'LastProfileView' ] ) ); ?></span>
                                </li>
                            <?
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </body>
        </html>
        <?
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
    else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
        echo "<h1>Cannot find or connect to database</h1>";
    }
