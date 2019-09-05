<?php
    require_once( "../dbfunctions.php" );
    $dbf = new dbfunctions;
    $page_title = "Forum Signatures";

    require_once( "../userfunctions.php" );
    $uf = new userfunctions;

    $loggedin = $uf->isLoggedIn();
    $myusername = $_SESSION[ 'username' ];

    $db = $dbf->connectToDatabase( $dbf->database );

    if ( $db[ 'found' ] ) {
        ?>

        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8"/>
                <title>Forum Signatures</title>

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/base/wrapper.css">

                <link rel="stylesheet" href="<?php echo $dbf->basefilepath; ?>css/special/sig.css">

                <script src="<?php echo $dbf->basefilepath; ?>js/jquery.js"></script>

                <script>
                    $(document).ready(function () {
                        $("#blah").submit(function (e) {
                            e.preventDefault();
                            $("#html").prop("value", "");
                            $("#bbcode").prop("value", "");
                            createSig();
                        });

                        $("#goal").change(function () {
                            var value = $(this).val();

                            if (value == "comp") {
                                $("#compoptions").css("display", "block");
                            } else {
                                $("#compoptions").css("display", "none");
                            }
                        });
                    });

                    function createSig() {
                        var rsn = $("#rsn").prop("value"),
                            goal = $("#goal").val();

                        if (goal == "comp") {
                            goal = "comp-" + $("input[name=comptype]:checked", "#blah").val();
                        }

                        if (rsn != "") {
                            rsn = encodeURIComponent(rsn);

                            $("#imagecontainer").empty().append($("<img>").prop("src", rsn + "/" + goal + ".png"));

                            var calcurl = 'http://maxcape.com/calc/' + rsn,
                                imgurl = 'http://maxcape.com/sig/' + rsn + "/" + goal + '.png';

                            $("#direct").prop("value", imgurl);
                            $("#html").prop("value", "<a href='" + calcurl + "'><img src='" + imgurl + "' /></a>");
                            $("#bbcode").prop("value", "[url=" + calcurl + "][img]" + imgurl + "[/img][/url]");
                        } else {
                            $("#imagecontainer").empty();
                        }
                    }
                </script>
            </head>

            <body>
                <?php
                    require_once( "../masthead.php" );
                ?>
                <div class="innercontent">
                    <div class="alert-message info">
                        <a class="close icon-remove-sign" href="#"></a>
                         <p><strong>Color Customization!</strong> If you have a user account, click <a href="<?php echo $dbf->basefilepath; ?>ucp/tab/4">here</a> to customize the colors of your signature.</p>
                    </div>
                    <form id="blah">
                        <label for="rsn">RSN:</label>
                        <input type="text" id="rsn" name="rsn" placeholder="RuneScape Name" required="required" pattern="([a-zA-Z0-9-_ ]{1,12})">
                        <br>
                        <label for="goal">Goal:</label>
                        <div class="dropdown dropdown-dark">
                            <select id="goal" class="dropdown-select">
                                <option value="10">10s Cape</option>
                                <option value="20">20s Cape</option>
                                <option value="30">30s Cape</option>
                                <option value="40">40s Cape</option>
                                <option value="50">50s Cape</option>
                                <option value="60">60s Cape</option>
                                <option value="70">70s Cape</option>
                                <option value="80">80s Cape</option>
                                <option value="90">90s Cape</option>
                                <option selected="selected" value="max">Max Cape</option>
                                <option value="comp">Completionist Cape</option>
                            </select>
                        </div>

                        <div id="compoptions">
                            <h4>Completionist Cape Image</h4>
                            <label>
                                <input checked="checked" type="radio" name="comptype" value="regular">
                                Regular
                            </label>
                            <label>
                                <input type="radio" name="comptype" value="trimmed">
                                Trimmed
                            </label>
                        </div>
                        <button type="submit">Create!</button>
                    </form>
                    <div id="output">
                        <div id="imagecontainer">
                            <p style="text-align:center; color:#AAA;">Create a forum signature above.</p>
                        </div>
                        <div id="directcontainer" style="margin-bottom:5px;">
                            <label for="direct">Direct:</label>
                            <input disabled="disabled" type="text" id="direct">
                        </div>
                        <label for="html">HTML:</label>
                        <label for="bbcode">BBCode:</label>
                        <textarea disabled="disabled" id="html"></textarea> <textarea disabled="disabled" id="bbcode"></textarea>
                    </div>
                </div>

                <div id="footer">
                    <p>Max/Completionist Cape Calc &copy; The Orange 2012 - <?php echo date( 'Y' ); ?></p>
                </div>
            </body>
        </html>

        <?php
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    } else {
        $dbf->disconnectFromDatabase( $db[ 'handle' ] );
    }
