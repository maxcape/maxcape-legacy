<?php
    session_start();
    require_once( "../../dbfunctions.php" );
    require_once( "../../userfunctions.php" );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $dbf->connectToDatabase( $dbf->database ) or die( "Cannot connect to database" );

    $capename = mysql_real_escape_string( $_POST[ 'capename' ] );
    $colors = array(
        1    => array(
            "H" => mysql_real_escape_string( $_POST[ 'color1Hval' ] ), "S" => mysql_real_escape_string( $_POST[ 'color1Sval' ] ), "L" => mysql_real_escape_string( $_POST[ 'color1Lval' ] )
        ), 2 => array(
            "H" => mysql_real_escape_string( $_POST[ 'color2Hval' ] ), "S" => mysql_real_escape_string( $_POST[ 'color2Sval' ] ), "L" => mysql_real_escape_string( $_POST[ 'color2Lval' ] )
        ), 3 => array(
            "H" => mysql_real_escape_string( $_POST[ 'color3Hval' ] ), "S" => mysql_real_escape_string( $_POST[ 'color3Sval' ] ), "L" => mysql_real_escape_string( $_POST[ 'color3Lval' ] )
        ), 4 => array(
            "H" => mysql_real_escape_string( $_POST[ 'color4Hval' ] ), "S" => mysql_real_escape_string( $_POST[ 'color4Sval' ] ), "L" => mysql_real_escape_string( $_POST[ 'color4Lval' ] )
        )
    );

    //Declare ranges for colors (to make them easty to change if I decide to store RS values)
    $hrange = array(
        0, 360
    );
    $srange = array(
        0, 100
    );
    $lrange = array(
        0, 100
    );

    $valid = true;

    //Validate colors
    foreach ( $colors as $color ) {
        //Check if blank.
        if ( in_array( "", $color ) ) {
            $valid = false;
            break;
        }

        //Check if H is in range
        if ( $color[ 'H' ] < $hrange[ 0 ] && $color[ 'H' ] > $hrange[ 1 ] ) {
            $valid = false;
            break;
        }

        //Check if S is in range
        if ( $color[ 'S' ] < $srange[ 0 ] && $color[ 'S' ] > $srange[ 1 ] ) {
            $valid = false;
            break;
        }

        //Check if L is in range
        if ( $color[ 'L' ] < $lrange[ 0 ] && $color[ 'L' ] > $lrange[ 1 ] ) {
            $valid = false;
            break;
        }
    }

    if ( $valid ) {
        //Color is valid
        if ( $capename != "" ) {
            //Name isn't blank
            $userID   = $_SESSION[ 'userid' ];
            $username = $_SESSION[ 'username' ];
            $colorIDs = array();

            //Insert the cape
            $dbf->query( "INSERT INTO capes (UserID, Title, SubmitDate) VALUES ('$userID', '$capename', NOW())" );
            //Get the ID of the cape just inserted
            $capeid = mysql_insert_id();

            foreach ( $colors as $color ) {
                $h = $color[ 'H' ];
                $s = $color[ 'S' ];
                $l = $color[ 'L' ];

                //Insert this color
                $dbf->query( "INSERT INTO colors (H, S, L) VALUES ('$h', '$s', '$l')" );
                //Add the inserted ID to the array.
                $colorIDs[ ] = mysql_insert_id();
            }

            foreach ( $colorIDs as $no => $id ) {
                //Bind each color to the cape in the join table
                $dbf->query( "INSERT INTO capecolors (ColorNumber, CapeID, ColorID) VALUES ('" . ( $no + 1 ) . "', '$capeid', '$id')" );
            }

            $strlen        = strlen( $capename );
            $maxstrlen     = 12;
            $scalingfactor = 5;

            if ( $strlen > $maxstrlen ) {
                $fsize = 100 - ( $scalingfactor * ( $strlen - $maxstrlen ) );
            } else {
                $fsize = 100;
            }
            ?>
            <div class="preview" id="cape-preview-<?php echo $capeid; ?>">
                <div class="applycapestyle">

                    <span class="capetitle" style="font-size:<?php echo $fsize; ?>%"><?php echo str_replace( "\\", "", $capename ); ?></span>

                    <div class="microcolors">
                        <?php
                        foreach ( $colors as $color ) {
                            ?>
                            <div class="microcolor" style="background-color:hsl(<?php echo $color[ 'H' ]; ?>, <?php echo $color[ 'S' ]; ?>%, <?php echo $color[ 'L' ]; ?>%)"
                                 data-h="<?php echo $color[ 'H' ]; ?>" data-s="<?php echo $color[ 'S' ]; ?>" data-l="<?php echo $color[ 'L' ]; ?>"></div>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="vote" data-voted="false">
                    <span class="icon-thumbs-up upvote" onclick="upvote(<?php echo $capeid; ?>, $(this));" id="upvote-<?php echo $capeid; ?>"></span> <span
                        class="icon-thumbs-down downvote" onclick="downvote(<?php echo $capeid; ?>, $(this));" id="downvote-<?php echo $capeid; ?>"></span>
                </div>

                <span class="result reszero"><span class="result-sign">&nbsp;</span><span class="result-number">0</span></span>

                <div class="smallipop-hint">
                    <h2><?php echo str_replace( "\\", "", $capename ); ?></h2>

                    <p>Submitted by: <a target="_blank" href="<?php echo $dbf->basefilepath . "profile/$username"; ?>"> <?php echo $username; ?></a></p>

                    <div class="capevoteresults">
                        <p class="votecount respos floatleft">Upvotes: 0</p>

                        <p class="votecount resneg floatright">Downvotes: 0</p>
                    </div>

                    <div class="controls">
                        <p class="floatleft"><a href="javascript:void(0);" onclick="flag(<?php echo $capeid; ?>);"><span class="icon-flag"></span> Flag</a></p>

                        <p class="floatright"><a class="favoritebutton" href="javascript:void(0);" onclick="favorite(<?php echo $capeid; ?>);"><span class="icon-star"></span> Favorite</a></p>
                    </div>
                </div>
            </div>
        <?php
        } else {
            //Name is blank
            echo 2;
        }
    } else {
        //Not valid
        echo 1;
    }