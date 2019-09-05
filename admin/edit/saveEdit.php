<?php
    require_once( "../../dbfunctions.php" );
    $dbf = new dbfunctions;
    $db  = $dbf->connectToDatabase( $dbf->database );
    if ( $db[ 'found' ] ) {
        $reqID = $_POST[ 'reqID' ];

        for ( $i = 0; $i < count( $_POST ); $i++ ) { //Loop through all posted data.
            if ( key( $_POST ) != "reqID" ) {
                $arrKey = key( $_POST ); //Get the key (element name) of the array.
                $data   = explode( "-", $arrKey ); //Incoming data keys should be in the format "type-obj-id" (Ex: "sub-text-37")
                $type   = $data[ 0 ]; //Types: req, sub
                $obj    = $data[ 1 ]; //Objects: delete, text, number
                $id     = $data[ 2 ]; //req/subreq ID
                $value  = mysql_real_escape_string($_POST[ $arrKey ]); //Posted value

                if ( $type == "req" ) { //This posted data is about the entire requirement.
                    if ( $obj == "delete" ) { //Posted data is to delete the entire requirement and all subrequirements for it!
                        $subreqs = $dbf->getAllAssocResults( "SELECT * FROM subrequirements WHERE RequirementID='$id'" ); //Get all subrequirements for this requirement.

                        foreach ( $subreqs as $subreq ) { //Loop through all subrequirements
                            $subID = $subreq[ 'SubrequirementID' ];

                            $dbf->query( "DELETE FROM userrequirements WHERE SubrequirementID='$subID'" ); //Delete userdata for this subrequirement.
                            $dbf->query( "DELETE FROM subrequirements WHERE Subrequirementid='$subID' LIMIT 1" ); //Delete the subrequirement.
                        }
                        $dbf->query( "DELETE FROM requirements WHERE RequirementID='$id' LIMIT 1" ); //Delete the requirement.

                        break; // Everything related to this requirement has been deleted. There's nothing left to do!
                    }
                    elseif ( $obj == "text" ) { //Posted data is for requirement name.
                        $dbf->query( "UPDATE requirements SET Text='$value' WHERE RequirementID='$id'" ); //Update the name of the requirement.
                    }
                    elseif ( $obj == "type" ) { //Posted data is for requirement type.
                        $dbf->query( "UPDATE requirements SET CapeType='$value' WHERE Requirementid='$id'" ); //Update the type of the requirement (1 = regular, 2 = trimmed).
                    }
                }
                elseif ( $type == "sub" ) { //This posted data is about a subrequirement.
                    if ( $obj == "delete" ) { //Posted data is to delete the subrequirement.
                        $dbf->query( "DELETE FROM userrequirements WHERE SubrequirementID='$id'" ); //Delete all userdata for this subrequirement.
                        $dbf->query( "DELETE FROM subrequirements WHERE Subrequirementid='$id' LIMIT 1" ); //Delete the subrequirement.
                    }
                    elseif ( $obj == "number" ) { //Posted data is for subrequirement number.
                        $dbf->query( "UPDATE subrequirements SET Number='$value' WHERE SubrequirementID='$id'" ); //Update the subrequirement number (1 = checkbox, 2+ = number);
                    }
                    elseif ( $obj == "text" ) { //Posted data is for subrequirement text.
                        $dbf->query( "UPDATE subrequirements SET Text='$value' WHERE SubrequirementID='$id'" ); //Update the display text for the subrequirement.
                    }
                }
                elseif ( $type == "new" ) {
                    $nums = $_POST[ $arrKey ];
                    next( $_POST );
                    $texts = $_POST[ key( $_POST ) ];

                    for ( $i = 0; $i < count( $nums ); $i++ ) {
                        $valid = true;

                        if ( $nums[ $i ] == "" || !is_numeric( $nums[ $i ] ) || $texts[ $i ] == "" ) {
                            $valid = false;
                        }

                        if ( $valid ) {
                            $num  = mysql_real_escape_string( $nums[ $i ] );
                            $text = mysql_real_escape_string( $texts[ $i ] );

                            $dbf->query( "INSERT INTO subrequirements (RequirementID, Text, Number) VALUES ( '$reqID', '$text', '$num' )" );
                        }
                    }

                    break;
                }
            }
            next( $_POST ); //Iterate to the next posted element.
        }
    }