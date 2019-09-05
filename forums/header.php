<?php
    if (isset($_SESSION['userid']) && isset($_SESSION['username']) && isset($_SESSION['rsn'])) {
        $userid = $_SESSION['userid'];
        $username = $_SESSION['username'];
        $rsn = $_SESSION['rsn'];

        $userlevel = $dbf->queryToText("SELECT PrivelegeLevel FROM users WHERE UserID='$userid'");
        $userlevel = intval($userlevel);
    }
?>

<script>
    function lavalamp() {
        var active = $("nav").find(".active");
        var lavalamp = $("#lavalamp");

        var left = active.offset().left;
        var width = active.width();

        lavalamp.css("left", left + "px");
        lavalamp.css("width", width + "px");
    }

    $(document).ready(function () {
        $(window).load(function () {
            lavalamp();

            //dirty hack to add the class ONLY AFTER the lavalamp has been positioned
            window.setTimeout(function () {
                $("#lavalamp").addClass("slider")
            }, 1);
        });

        $('.myMenu > li').bind('mouseover', openSubMenu);
        $('.myMenu > li').bind('mouseout', closeSubMenu);


        function openSubMenu() {
            var ddl = $(this).find("ul");

            ddl.css('visibility', 'visible');

            var right = $(window).width() - ($(this).offset().left + $(this).outerWidth())
            ddl.css("right", right + "px");
        }

        function closeSubMenu() {
            $(this).find('ul').css('visibility', 'hidden');
        }

        $(window).scroll(function () {
            if ($(window).scrollTop() > 60) {
                $("nav").addClass("fixed");
                $("#header").addClass("fixedNav");
            } else {
                $("nav").removeClass("fixed");
                $("#header").removeClass("fixedNav");
            }
        });

        $(window).resize(function () {
            lavalamp();
        });

        $('.socialicon').click(function (e) {
            e.preventDefault();

            var width = 575,
                height = 400,
                left = ($(window).width() - width) / 2,
                top = ($(window).height() - height) / 2,
                opts = 'status=1' +
                    ',width=' + width +
                    ',height=' + height +
                    ',top=' + top +
                    ',left=' + left;

            var url = "";

            if ($(this).hasClass("twitter")) {
                url = "http://twitter.com/share?url={url}&via=The__Orange&hashtags=RuneScape&text={text}";
            } else if ($(this).hasClass("facebook")) {
                url = "http://www.facebook.com/sharer.php?s=100&p[title]={title}&p[summary]={text}&p[url]={url}";
            } else if ($(this).hasClass("tumblr")) {
                url = "http://www.tumblr.com/share/link?url={url2}&name={title}&description={text}";
            } else if ($(this).hasClass("googleplus")) {
                url = "https://plus.google.com/share?u{url}";
            }

            url = url.replace("{url}", location.href);
            url = url.replace("{url2}", location.href.replace("http://", ""));
            url = url.replace("{text}", document.title);
            url = url.replace("{title}", document.title);

            url = encodeURI(url);

            window.open(url, 'Share to Social Media', opts);

            return false;
        });


        $("#calcSearch").click(function () {
            var toggled = $(this).data("expanded");
            $(this).find("a").css("display", toggled ? "block" : "none");
            if (toggled) {
                $("#calcSearchBox").css("width", "0");
            } else {
                $("#calcSearchBox").css("width", "125px");
                $("#calcSearchBox").find("input").focus();
            }

            $(this).data("expanded", !toggled);
        });

        $("#calcSearchBox")
            .click(function (e) {
                e.stopPropagation();
            })
            .find("input")
                .blur(function () {
                    $("#calcSearchBox").css("width", "0");
                    $("#calcSearch").data("expanded", false).find("a").css("display", "block");
                    lavalamp();
                })
                .on('input', function() {
                    var regex = /^([a-zA-Z0-9\-_ ]{1,12})$/g;

                    var val = $(this).val();
                    if(!regex.test(val)) {
                        val = val.substring(0, val.length - 1);
                        $(this).val(val);
                    }
                });

        $("nav li").mouseover(function () {
            var lavalamp = $("#lavalamp");
            var left = $(this).offset().left;
            var width = $(this).width();

            lavalamp.css("left", left + "px");
            lavalamp.css("width", width + "px");
        });

        $("nav").mouseleave(function () {
            var active = $(this).find(".active");
            var lavalamp = $("#lavalamp");

            var left = active.offset().left;
            var width = active.width();

            lavalamp.css("left", left + "px");
            lavalamp.css("width", width + "px");
        });

//        $("#timer-container").click(function () {
//            var toggled = $(this).data("toggled");
//            console.log(toggled);
//
//            if (!toggled) {
//                $("#timer-toggle-container").animate({
//                    width: '35px',
//                    marginLeft: '5px'
//                }, 250);
//            } else {
//                $("#timer-toggle-container").animate({
//                    width: 0,
//                    marginLeft: 0
//                }, 250);
//            }
//
//            $(this).data("toggled", !toggled);
//        });
    });
</script>
<header>
    <div id="header">
        <div id="headerinner">
            <img src="/images/TL_icon.png" class="icon" alt="logo"/>

            <h2>Maxcape</h2>

            <?php
                if (isset($loggedin) && $loggedin) {
                    ?>
                    <ul class="myMenu">
                        <li class="ddl_main">
                            <img class="ddl_user_avatar" src="/forums/getAvatar.php?rsn=<?php echo urlencode($rsn); ?>"/>

                            <div>
                                <span class="ddl_username"><?php echo $username; ?></span>
                                <span class="ddl_rsn"><?php echo $rsn; ?></span> <i class="fa fa-sort-asc"></i>
                            </div>
                            <ul>
                                <li><a href="/profile/<?php echo $username; ?>">My Profile</a>
                                </li>
                                <li><a href="/ucp/tab/0">User Control Panel</a>
                                </li>
                                <li><a href="/user/logout">Logout</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php
                } else {
                    ?>
                    <ul class="myMenu">
                        <li class="ddl_main">
                            <span class="ddl_username"><a href="/user/login">Login</a></span>
                            <span class="ddl_rsn"><a href="/user/register">Register</a></span>
                        </li>
                    </ul>
                <?php
                }
            ?>

<!--            <div id="timer-container" data-toggled="false">-->
<!--                <div id="timer">-->
<!--                    <span class="fa fa-clock-o"></span>-->
<!---->
<!--                    <div id="timer-toggle-container">-->
<!--                        <p id="timer-text">0:00</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
    <nav>
        <div id="navinner">
            <ul>
                <li><a href="/nr">Home</a></li>
                <li id="calcSearch" data-expanded="false">
                    <a href="javascript:void(0);">Calc</a>

                    <div id="calcSearchBox">
                        <form action="/calc/" onsubmit="(function(e) { e.preventDefault(); document.location =
                        '/calc/' + $('#name').val().replace(/ /g, '+'); })(event)">
                            <input name="name" id="name" type="text" placeholder="Input RSN"/>
                        </form>

                    </div>
                </li>
                <li><a href="/designer/">Designer</a></li>
                <li><a href="/sig/">Signatures</a></li>
                <li><a href="/logs/">Logs</a></li>
                <li><a href="/search/">Search Profiles</a></li>
                <li class="active"><a href="/forums/">Forums</a></li>

                <?php
                    if (isset($userlevel) && $userlevel >= 4) {
                        ?>
                        <li><a href="/admin/">Admin</a></li>
                    <?php
                    }
                ?>
            </ul>

            <div id="lavalamp" class=""></div>

            <div id="socialicons">
                <div class="socialicon googleplus">
                    <div class="bannertop"></div>
                    <div class="bannermid"></div>
                    <div class="bannercontent"><i class="fa fa-google-plus"></i></div>
                    <div class="bannerbottom"></div>
                </div>

                <div class="socialicon facebook">
                    <div class="bannertop"></div>
                    <div class="bannermid"></div>
                    <div class="bannercontent"><i class="fa fa-facebook"></i></div>
                    <div class="bannerbottom"></div>
                </div>

                <div class="socialicon tumblr">
                    <div class="bannertop"></div>
                    <div class="bannermid"></div>
                    <div class="bannercontent"><i class="fa fa-tumblr"></i></div>
                    <div class="bannerbottom"></div>
                </div>

                <div class="socialicon twitter">
                    <div class="bannertop"></div>
                    <div class="bannermid"></div>
                    <div class="bannercontent"><i class="fa fa-twitter"></i></div>
                    <div class="bannerbottom"></div>
                </div>
            </div>
        </div>
    </nav>
</header>

<div id="breadcrumbs">
    <?php echo $breadcrumbs; ?>
</div>
