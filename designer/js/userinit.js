/* Max/Comp Cape Designer
 * userinit.js
 * Author: Evan Riley
 * File Dependencies: jQuery.js, smallipop.js, cape.js, colorpicker.js, mCustomScrollbar.js
 *
 * Only used when user is LOGGED IN
 *
 * Last Edited: 7/1/13
 */

//Following code is only important if the user is logged in
$(window).resize(function () {
    if ($(".blinder").css("visibility") != "hidden") {
        //Recenter the popup on window resize
        center();
    }
});

function center() {
    //Center the container on the screen.
    var target = $("#container"),
        h = target.height() + 20,
        w = target.width() + 20,
        leftPos = (($(window).width() / 2) - (w / 2)),
        topPos = (($(window).height() / 2) - (h / 2));

    target.css("left", leftPos + "px").css("top", topPos + "px");
}

function popup() {
    //Show the blinder/container.
    $(".blinder").css("visibility", "visible");
    center();
}

function hideblinder() {
    //Hide the blinder/container.
    $(".blinder").css("visibility", "hidden");
    $("#capename").val("");
}

function isValidID(str) {
    var exp = new RegExp('^[\\d]+$');

    return exp.test(str);
}

function favorite(id) {
    if (isValidID(id)) {
        $.post("scripts/favorite.php", { "capeid": id, "action": "add"}, function (data) {
        });

        var preview;

        for (var i = 1; i <= 3; i++) {
            switch (i) {
                case 1:
                    preview = $("#cape-preview-" + id);
                    break;
                case 2:
                    preview = $("#cape-preview-alltime-" + id);
                    break;
                case 3:
                    preview = $("#cape-preview-month-" + id);
                    break;
            }

            if (preview.length > 0) {
                preview.find(".favoritebutton").addClass("favorited").html("<span class='icon-star'></span> Unfavorite");
                preview.find(".favoritebutton").attr("onclick", "unfavorite(" + id + ")");

                var classes = preview.attr("class").split(/\s+/);

                for (var ix = 0; ix < classes.length; ix++) {
                    var className = $.trim(classes[ix]);

                    if (className.match(/^smallipop/)) {
                        preview.removeClass(className);
                    }
                }
            }
        }

        for (var i = 1; i <= 3; i++) {
            switch (i) {
                case 1:
                    preview = $("#cape-preview-" + id);
                    break;
                case 2:
                    preview = $("#cape-preview-alltime-" + id);
                    break;
                case 3:
                    preview = $("#cape-preview-month-" + id);
                    break;
            }

            if (preview.length > 0) {
                break;
            }
        }

        //Clone preview and make adjustments to have it match favorites format
        var clone = preview.clone();
        clone.attr("id", clone.attr("id") + "-favorite");
        clone.find(".vote").remove();
        clone.css("height", "0").css("opacity", "0");

        clone.click(function () {
            applyCapeStyle($(this));
        });


        $("#favorites").find(".mCSB_container").prepend(clone);

        clone.animate({
            opacity: 1,
            height: "30px"
        }, 100);

        initSmallipop($("#" + clone.attr("id")));


        for (var i = 1; i <= 3; i++) {
            switch (i) {
                case 1:
                    preview = $("#cape-preview-" + id);
                    break;
                case 2:
                    preview = $("#cape-preview-alltime-" + id);
                    break;
                case 3:
                    preview = $("#cape-preview-month-" + id);
                    break;
            }

            if (preview.length > 0) {
                initSmallipop(preview);
            }
        }
    }
}

function unfavorite(id) {
    if (isValidID(id)) {
        $.post("scripts/favorite.php", { "capeid": id, "action": "remove"}, function (data) {
        });
        var preview;

        for (var i = 1; i <= 3; i++) {
            switch (i) {
                case 1:
                    preview = $("#cape-preview-" + id);
                    break;
                case 2:
                    preview = $("#cape-preview-alltime-" + id);
                    break;
                case 3:
                    preview = $("#cape-preview-month-" + id);
                    break;
            }

            if (preview.length > 0) {
                preview.find(".favoritebutton").removeClass("favorited").html("<span class='icon-star'></span> Favorite");
                preview.find(".favoritebutton").attr("onclick", "favorite(" + id + ")");

                var classes = preview.attr("class").split(/\s+/);

                for (var ix = 0; ix < classes.length; ix++) {
                    var className = $.trim(classes[ix]);

                    if (className.match(/^smallipop/)) {
                        preview.removeClass(className);
                    }
                }
            }
        }

        var clone = $("#cape-preview-" + id + "-favorite");
        clone.animate({
            opacity: 0,
            height: 0
        }, {
            "complete": function () {
                $(this).remove();
            },
            "duration": 100
        });

        for (var i = 1; i <= 3; i++) {
            switch (i) {
                case 1:
                    preview = $("#cape-preview-" + id);
                    break;
                case 2:
                    preview = $("#cape-preview-alltime-" + id);
                    break;
                case 3:
                    preview = $("#cape-preview-month-" + id);
                    break;
            }

            if (preview.length > 0) {
                initSmallipop(preview);
            }
        }
    }
}

function upvote(id, clickedElement) {
    var vote = $("#" + clickedElement.attr("id"));
    if (!vote.hasClass("votedchoice")) {
        var preview = vote.parents(".preview");
        var oldVal = parseInt(preview.find(".result-number").text());
        var hasvoted = preview.find(".vote").attr("data-voted");

        if (preview.find(".result-sign").text() == "-") {
            oldVal *= -1;
        }

        var newVal = hasvoted == "true" ? oldVal + 2 : oldVal + 1;

        var newclass = "";
        //Below is class/tooltip code that needs to be applied to all 3 types

        for (var i = 1; i <= 4; i++) {
            switch (i) {
                case 1:
                    preview = $("#upvote-" + id).parents(".preview");
                    break;
                case 2:
                    preview = $("#upvote-alltime-" + id).parents(".preview");
                    break;
                case 3:
                    preview = $("#upvote-month-" + id).parents(".preview");
                    break;
                case 4:
                    preview = $("#cape-preview-" + id + "-favorite");
                    break;
            }

            var update = preview.find(".smallipop-hint");
            if (hasvoted == "true") {
                update.find(".upvotecount").text(parseInt(update.find(".upvotecount").text()) + 1);
                update.find(".downvotecount").text(parseInt(update.find(".downvotecount").text()) - 1);
            } else {
                update.find(".upvotecount").text(parseInt(update.find(".upvotecount").text()) + 1);
            }

            if (newVal == 0) {
                preview.find(".result-sign").empty();
                newclass = "reszero";
            } else if (newVal > 0) {
                preview.find(".result-sign").text("+");
                newclass = "respos";
            } else {
                preview.find(".result-sign").text("-");
                newclass = "resneg";
            }

            preview.find(".result-number").text(Math.abs(newVal));

            preview.find(".upvote").addClass("votedchoice");
            preview.find(".downvote").removeClass("votedchoice");

            preview.find(".result").removeClass("reszero").removeClass("respos").removeClass("resneg").addClass(newclass);
            preview.find(".vote").attr("data-voted", "true");


            preview.smallipop("destroy");
            preview.removeClass("smallipop-initialized");
            initSmallipop(preview);
        }

        var fav = $("#cape-preview-" + id + "-favorite");

        if (newVal == 0) {
            fav.find(".result-sign").empty();
            newclass = "reszero";
        } else if (newVal > 0) {
            fav.find(".result-sign").text("+");
            newclass = "respos";
        } else {
            fav.find(".result-sign").text("-");
            newclass = "resneg";
        }

        fav.find(".result-number").text(Math.abs(newVal));
        fav.find(".result").removeClass("reszero").removeClass("respos").removeClass("resneg").addClass(newclass);


        $.post("scripts/vote.php", {"id": id, "direction": "upvote"}, function (data) {
            if (data != "0") {
                alert(data + "\n\nYour vote has not been counted.");
            }
        });
    } else {
        alert("You have already voted this direction");
    }
}

function downvote(id, clickedElement) {
    var vote = $("#" + clickedElement.attr("id"));
    if (!vote.hasClass("votedchoice")) {
        var preview = vote.parents(".preview");
        var oldVal = parseInt(preview.find(".result-number").text());
        var hasvoted = preview.find(".vote").attr("data-voted");

        if (preview.find(".result-sign").text() == "-") {
            oldVal *= -1;
        }

        var newVal = hasvoted == "true" ? oldVal - 2 : oldVal - 1;

        var newclass = "";

        //Below is class/tooltip code that needs to be applied to all 3 types

        for (var i = 1; i <= 4; i++) {
            switch (i) {
                case 1:
                    preview = $("#upvote-" + id).parents(".preview");
                    break;
                case 2:
                    preview = $("#upvote-alltime-" + id).parents(".preview");
                    break;
                case 3:
                    preview = $("#upvote-month-" + id).parents(".preview");
                    break;
                case 4:
                    preview = $("#cape-preview-" + id + "-favorite");
                    break;
            }

            var update = preview.find(".smallipop-hint");
            if (hasvoted == "true") {
                update.find(".upvotecount").text(parseInt(update.find(".upvotecount").text()) - 1);
                update.find(".downvotecount").text(parseInt(update.find(".downvotecount").text()) + 1);
            } else {
                update.find(".upvotecount").text(parseInt(update.find(".downvotecount").text()) + 1);
            }

            if (newVal == 0) {
                preview.find(".result-sign").empty();
                newclass = "reszero";
            } else if (newVal > 0) {
                preview.find(".result-sign").text("+");
                newclass = "respos";
            } else {
                preview.find(".result-sign").text("-");
                newclass = "resneg";
            }

            preview.find(".result-number").text(Math.abs(newVal));


            preview.find(".upvote").removeClass("votedchoice");
            preview.find(".downvote").addClass("votedchoice");

            preview.find(".result").removeClass("reszero").removeClass("respos").removeClass("resneg").addClass(newclass);
            preview.find(".vote").attr("data-voted", "true");


            preview.smallipop("destroy");
            preview.removeClass("smallipop-initialized");
            initSmallipop(preview);
        }


        $.post("scripts/vote.php", {"id": id, "direction": "downvote"}, function (data) {
            if (data != "0") {
                alert(data + "\n\nYour vote has not been counted.");
            }
        });
    } else {
        alert("You have already voted this direction");
    }
}

function flag(id) {
    if (isValidID(id)) {
        $.post("scripts/flag.php", {"capeid": id}, function (data) {
            alert("Thank you, this cape has been flagged for review.");
        });
    }
}

//Code only shown if user is logged in.
$(document).ready(function () {
    $("#save").click(function () {
        //Open the save dialogue.
        popup();
        $("#capename").focus();
    });

    $("#saveform").submit(function (e) {
        //Prevent form submit
        e.preventDefault();

        //Don't submit an empty name.
        if ($("#capename").val().length > 0) {
            //Post form data to submit.php
            $.post("scripts/submit.php", $(this).serialize(), function (data) {
                //Close the popup
                hideblinder();

                $("#most-recent").find(".mCSB_container").prepend(data);
                var preview = $("#most-recent").find(".preview:first-of-type");

                preview.css("opacity", "0").css("height", "0");

                preview.animate({
                    opacity: 1,
                    height: "30px"
                }, 100);

                initSmallipop(preview);
            });
        } else {
            alert("Please specify a name for your cape!");
        }
    });
});