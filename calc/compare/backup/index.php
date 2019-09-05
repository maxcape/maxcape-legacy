<?php
    session_start();
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;
    require_once( "../../userfunctions.php" );
    $uf = new userfunctions;
    require_once("../../rsfunctions.php");
    $rsf = new rsfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database.");

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];

    $user1 = mysql_real_escape_string($_GET['user1']);
    $user2 = mysql_real_escape_string($_GET['user2']);

    $maxcapexp  = 13034431 * 26;

    $ranks1 = array();
    $levels1 = array();
    $experience1 = array();
    $ranks2 = array();
    $levels2 = array();
    $experience2 = array();

    $skills = $dbf->getAllAssocResults("SELECT * FROM skills");

    $data     = $rsf->updatePlayer( $user1 );
    $response1 = $data[ 0 ];
    $data     = $data[ 1 ];


    $data = preg_split( "/\s+/", $data );

    $user1tomax = 0;


    for ( $i = 0; $i < count( $skills ); $i++ ) {
        $skillNumber = $skills[ $i ][ 'Number' ];
        $thisSkill   = explode( ",", $data[ $skillNumber ] );

        if ( $thisSkill[ 0 ] == -1 ) {
            $thisSkill[ 0 ] = 0;
        }
        if ( $thisSkill[ 1 ] == -1 ) {
            $thisSkill[ 1 ] = 1;
        }
        if ( $thisSkill[ 2 ] == -1 ) {
            $thisSkill[ 2 ] = 0;
        }

        if($i > 0) {
            $ranks1[ $i ]      = $thisSkill[ 0 ];
            $levels1[ $i ]     = $thisSkill[ 1 ];
            $experience1[ $i ] = $thisSkill[ 2 ];

            if($thisSkill[1] < 99) {
                $user1tomax += 13034431 - $thisSkill[2];
            }
        }
        else {
            $user1totalxp = $thisSkill[2];
        }
    }

    $data     = $rsf->updatePlayer( $user2 );
    $response2 = $data[ 0 ];
    $data     = $data[ 1 ];

    $data = preg_split( "/\s+/", $data );

    $user2tomax = 0;
    $user2totalxp = $data[0][2];

    for ( $i = 0; $i < count( $skills ); $i++ ) {
        $skillNumber = $skills[ $i ][ 'Number' ];
        $thisSkill   = explode( ",", $data[ $skillNumber ] );

        if ( $thisSkill[ 0 ] == -1 ) {
            $thisSkill[ 0 ] = 0;
        }
        if ( $thisSkill[ 1 ] == -1 ) {
            $thisSkill[ 1 ] = 1;
        }
        if ( $thisSkill[ 2 ] == -1 ) {
            $thisSkill[ 2 ] = 0;
        }

        if($i > 0) {
            $ranks2[ $i ]      = $thisSkill[ 0 ];
            $levels2[ $i ]     = $thisSkill[ 1 ];
            $experience2[ $i ] = $thisSkill[ 2 ];

            if($thisSkill[1] < 99) {
                $user2tomax += 13034431 - $thisSkill[2];
            }
        }
        else {
            $user2totalxp = $thisSkill[2];
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Comparison - <?php echo $user1; ?> and <?php echo $user2; ?> - Max/Comp Cape Calc</title>
    <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css"/>
    <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>calc/compare/compare.css"/>

    <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
    <script src="<?php echo $dbf->basefilepath; ?>js/justgage/js/raphael.2.1.0.min.js"></script>
    <script src="<?php echo $dbf->basefilepath; ?>js/justgage/js/justgage.1.0.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#compare").change(function() {
                var value = $(this).val();

                $(".skill-percentage").each(function() {
                    var newwidth = $(this).attr("data-width" + value);
                    $(this).animate({width: newwidth + "%"}, 250);
                    $(this).parent().find(".skill-per").text(newwidth + "%");
                });
            });

            <?php
            if($response1 != "not in use") {
            ?>

            var g1 = new JustGage({
                id: "gauge1",
                value: <?php echo number_format((($maxcapexp - $user1tomax)/$maxcapexp)*100, 2); ?>,
                min: 0,
                max: 100,
                title: " ",
                label: "%",
                valueFontColor: "#AAA",
                labelFontColor: "#AAA",
                titleFontColor: "#AAA",
                levelColors: [
                    "#FF0000",
                    "#F9C802",
                    "#A9D70B"
                ]
            });
            <?php
            }
            ?>

            <?php
            if($response2 != "not in use") {
            ?>

            var g2 = new JustGage({
                id: "gauge2",
                value: <?php echo number_format((($maxcapexp - $user2tomax)/$maxcapexp)*100, 2); ?>,
                min: 0,
                max: 100,
                title: " ",
                label: "%",
                valueFontColor: "#AAA",
                labelFontColor: "#AAA",
                titleFontColor: "#AAA",
                levelColors: [
                    "#FF0000",
                    "#F9C802",
                    "#A9D70B"
                ]
            });
            <?php
            }
            ?>

            $('#user1input, #user2input').keypress(function(event){
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    compare();
                }
            }).focus(function() {
                $(this).select();
            }).mouseup(function() {
                return false;
            });
        });

        function compare() {
            var user1 = $("#user1input").val(),
                user2 = $("#user2input").val();

            user1 = user1.replace(/ /g, "+");
            user2 = user2.replace(/ /g, "+");

            location.href = "/calc/compare/" + user1 + "/" + user2;
        }
    </script>
</head>

<body>
    <?php require_once("../../masthead.php"); ?>

    <div style="width:100%; text-align:center;">
        <button id="comparebtn1" type="button" onclick="compare()">Compare</button>
        <input type="text" id="user1input" value="<?php echo $user1; ?>"/>
        <div class="dropdown dropdown-dark" style="width:250px; margin:0;">
            <select id="compare" class="dropdown-select">
                <option value="1">Compare Percentage Complete</option>
                <option value="2">Compare Percentage of Experience</option>
            </select>
        </div>
        <input type="text" id="user2input" value="<?php echo $user2; ?>"/>
        <button id="comparebtn2" type="button" onclick="compare()">Compare</button>
    </div>

    <div id="main" style="overflow:hidden;">
        <div class="player one">
            <?php
            if($response1 != "not in use") {
            ?>
            <h1 class="name"><?php echo $user1; ?></h1>
            <div id="gauge1"></div>
            <h2><?php echo number_format($user1totalxp); ?></h2>

            <div class="skilllist">
                <div class="skill">
                    <span class="skill-name">Skill</span>
                    <span class="skill-rank">Rank</span>
                    <span class="skill-lvl">Level</span>
                    <span class="skill-exp">Experience</span>
                    <span class="skill-per">%</span>
                </div>
                <?php
                    $greater = array();
                    for ( $i = 1; $i < count( $skills ); $i++ ) {
                        $width1 = ($experience1[$i] / 13034431) * 100;
                        if($width1 > 100) {
                            $width1 = 100;
                        }

                        $width1 = number_format($width1, 2);

                        $totalxpforboth = $experience1[$i] + $experience2[$i];
                        $percentagemine = ($experience1[$i] / $totalxpforboth)*100;

                        if($percentagemine > 100) {
                            $percentagemine = 100;
                        }

                        $width2 = number_format($percentagemine, 2);
                        if($width2 > 50) {
                            $greater[$i] = 1;
                        }
                        ?>
                        <div class="skill <?php echo $width2 > 50 ? "winner" : "" ?>">
                            <div class="skill-percentage" style="width:<?php echo $width1; ?>%;" data-width1="<?php echo $width1; ?>" data-width2="<?php echo $width2; ?>"></div>
                            <span class="skill-name"><?php echo ucwords($skills[$i]['Name']); ?></span>
                            <span class="skill-rank"><?php echo number_format($ranks1[$i]); ?></span>
                            <span class="skill-lvl"><?php echo number_format($levels1[$i]); ?></span>
                            <span class="skill-exp"><?php echo number_format($experience1[$i]); ?></span>
                            <span class="skill-per"><?php echo $width1; ?>%</span>
                        </div>
                <?php
                    }
                ?>
            </div>
            <?php
            } else {
                ?>
                <h1 class="name">No Stats</h1>
            <?php
            }
            ?>
        </div>

        <div class="middle-column">
            <?php
                foreach($skills as $i => $skill) {
                    if($skill['Name'] != "Overall") {
            ?>
            <div class="img-container <?php echo $greater[$i] == 1 ? "leftgreater" : "rightgreater"; ?>">
                <div class="img-bg-left"></div>
                <img src="<?php echo $dbf->basefilepath; ?>images/<?php echo strtolower($skill['Name']); ?>.png"/>
                <div class="img-bg-right"></div>
            </div>
            <?php
                    }
                }
            ?>
        </div>

        <div class="player two">
            <?php
                if($response2 != "not in use") {
            ?>
            <h1 class="name"><?php echo $user2; ?></h1>
            <div id="gauge2"></div>
            <h2><?php echo number_format($user2totalxp); ?></h2>

            <div class="skilllist">
                <div class="skill">
                    <span class="skill-name">Skill</span>
                    <span class="skill-rank">Rank</span>
                    <span class="skill-lvl">Level</span>
                    <span class="skill-exp">Experience</span>
                    <span class="skill-per">%</span>
                </div>
                <?php
                    for ( $i = 1; $i < count( $skills ); $i++ ) {
                        $width1 = ($experience2[$i] / 13034431) * 100;
                        if($width1 > 100) {
                            $width1 = 100;
                        }

                        $width1 = number_format($width1, 2);

                        $totalxpforboth = $experience1[$i] + $experience2[$i];
                        $percentagemine = ($experience2[$i] / $totalxpforboth)*100;

                        if($percentagemine > 100) {
                            $percentagemine = 100;
                        }

                        $width2 = number_format($percentagemine, 2);
                        ?>
                        <div class="skill <?php echo $width2 > 50 ? "winner" : "" ?>">
                            <div class="skill-percentage" style="width:<?php echo $width1; ?>%;" data-width1="<?php echo $width1; ?>" data-width2="<?php echo $width2; ?>"></div>
                            <span class="skill-name"><?php echo ucwords($skills[$i]['Name']); ?></span>
                            <span class="skill-rank"><?php echo number_format($ranks2[$i]); ?></span>
                            <span class="skill-lvl"><?php echo number_format($levels2[$i]); ?></span>
                            <span class="skill-exp"><?php echo number_format($experience2[$i]); ?></span>
                            <span class="skill-per"><?php echo $width1; ?>%</span>
                        </div>
                    <?php
                    }
                ?>
            </div>
        </div>
        <?php
            } else {
            ?>
            <h1 class="name">No Stats</h1>
        <?php
        }
        ?>
    </div>

    <div id="footer">
        <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
    </div>
</body>
</html>