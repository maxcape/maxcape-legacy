<?php
    session_start();
    error_reporting( 1 );
    require_once( "dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        ?>

        <!DOCTYPE html>
        <html>
            <head>
                <title>Page Not Found</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
            </head>

            <body>
                <?php require_once( "analytics.php" ); ?>
                <?php require_once( "masthead.php" ); ?>

                <div id="content">
                    <div class="innercontent">
                        <h1 style="text-align: center;">404 - File Not Found</h1>

                        <p style="text-align: center;">The requested page could not be found. Click <a href="<?php echo $dbf->basefilepath; ?>nr">here</a> to return to the main page.</p>
                    </div>
                </div>

                <script>
                    $(".active").each(function() {
                        $(this).removeClass('active');
                    });
                </script>
            </body>
        </html>

    <?php
    }
?>