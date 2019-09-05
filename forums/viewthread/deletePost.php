<?php
    session_start();
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $postid = mysql_real_escape_string($_POST['id']);
    $userid = $_SESSION['userid'];

    $postuserid = $dbf->queryToText("SELECT UserID FROM forumposts WHERE PostID='$postid'");
    $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");

    if($postuserid === $userid || intval($userlevel) >= 3) {
        $ismainpost = $dbf->queryToText("SELECT IsMainPost FROM forumposts WHERE PostID='$postid'");

        if($ismainpost == "1") {
            $threadid = $dbf->queryToText("SELECT ThreadID FROM forumposts WHERE PostID='$postid'");
            $postcount = $dbf->getAllAssocResults("SELECT * FROM forumposts WHERE ThreadID='$threadid'");

            if(count($postcount) === 1) {
                $dbf->query("DELETE FROM forumposts WHERE ThreadID='$threadid'");
                $dbf->query("DELETE FROM forumthreads WHERE ThreadID='$threadid'");

                echo "thread deleted";
            } else {
                $dbf->query("UPDATE forumposts SET IsDeleted=1 WHERE PostID='$postid'");

                echo "post deleted";
            }
        } else {
            $dbf->query("UPDATE forumposts SET IsDeleted=1 WHERE PostID='$postid'");

            echo "post deleted";
        }
    } else {
        echo "This is not this users post or user is not a moderator UserID";
    }