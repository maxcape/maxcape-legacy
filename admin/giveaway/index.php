<?
    require_once( '../../dbfunctions.php' );
    require_once( '../../userfunctions.php' );
    $dbf = new dbfunctions;
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();

    if( !$loggedin ) {
        header( "Location: ../../?noredirect&notloggedin" );
        die();
    }

    $myusername = $_SESSION[ 'username' ];

    $db = $dbf->connectToDatabase( $dbf->database );

    if( $db[ 'found' ] ) {
        $userid      = $_SESSION[ 'userid' ];
        $permissions = $dbf->queryToText( "SELECT PrivelegeLevel FROM users WHERE UserID='$userid' LIMIT 1" );
        if( $permissions < 5 ) {
            header( "Location: ../" );
            die();
        }

        $user = $myusername;
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Giveaways</title>
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/alerts.css">
                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/ucp.css">

                <style>
                    #prize-list li input {
                        float:none;
                        display:inline-block;
                    }

                    #prize-list li:first-of-type a {
                        margin-left:1px !important;
                    }

                    #prize-list li a {
                        text-decoration:none;
                        margin-left:5px;
                    }
                </style>

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>
            </head>

            <body>
                <?php require_once( "../../masthead.php" ); ?>

                <div id="content">
                    <div class="innercontent">
                        <form id="create-giveaway">
                            <fieldset>
                                <legend>Create a Giveaway</legend>

                                <label for="giveaway-title">Title:</label>
                                <input type="text" id="giveaway-title"/>

                                <label for="giveaway-description">Description:</label>
                                <textarea style="width:50%;" id="giveaway-description"></textarea>

                                <label for="giveaway-start-date">Start Date:</label>
                                <input type="date" id="giveaway-start-date"/>

                                <label for="giveaway-end-date">End Date:</label>
                                <input type="date" id="giveaway-end-date"/>

                                <div id="prizes">
                                    <h2>Prizes:</h2>
                                    <ol id="prize-list">
                                        <li><input type="text" id="prize-1"/> <a href="javascript:void(0);" onclick="remPrize(1);"><i class="icon-remove"></i></a></li>
                                    </ol>

                                    <a href="javascript:void(0);" onclick="addPrize();">Add Prize</a>
                                </div>

                                <button type="button" onclick="createGiveaway();">Create</button>
                            </fieldset>
                        </form>
                    </div>
                </div>

                <script>
                    function addPrize() {
                        var prizelist = $("#prize-list");
                        var noOfPrizes = prizelist.children().length;
                        noOfPrizes += 1;

                        var input = $("<input>").attr("type", "text").attr("id", "prize-" + noOfPrizes);
                        var remove = $("<a>").attr("href", "javascript:void(0);").click(function() {
                            remPrize(noOfPrizes);
                        }).append($("<i>").addClass("icon-remove"));

                        prizelist.append($("<li>").append(input).append(remove));
                    }

                    function remPrize(number) {
                        var prize = $("#prize-" + number);
                        var parent = prize.parent("li");

                        parent.remove();
                    }

                    function createGiveaway() {
                        var title = $("#giveaway-title").val();
                        var startDate = $("#giveaway-start-date").val();
                        var endDate = $("#giveaway-end-date").val();
                        var description = $("#giveaway-description").val();

                        var prizes = {};

                        var prizelist = $("#prize-list");

                        prizelist.children("li").each(function(index) {
                            var number = index + 1;
                            prizes[number] = $(this).find("input").val();
                        });

                        var postdata = {
                            "title": title,
                            "startDate": startDate,
                            "endDate": endDate,
                            "prizeList": prizes,
                            "desc": description
                        };

                        $.post("createGiveaway.php", postdata, function(data) {
                            alert(data);
                        })
                    }
                </script>
            </body>
        </html>
    <?
    }