/* Max/Comp Cape Designer
 * init.js
 * Author: Evan Riley
 * File Dependencies: jQuery.js, smallipop.js, cape.js, colorpicker.js, mCustomScrollbar.js
 *
 * Last Edited: 7/1/13
 */

//Apply the element (.applycapestyle) to the designer
function applyCapeStyle(element) {
    //Remove selected from all
    $(".applycapestyle").removeClass("selectedcapestyle");
    //Apply selected to this one
    element.addClass("selectedcapestyle");

    element.find(".microcolor").each(function (index) {
        //Get colors of each microcolor and set those to the color and minicolor that corresponds to it.
        var target = $("#color" + (index + 1));
        var mini = $("#minicolor" + (index + 1));

        var h = $(this).attr("data-h"),
            s = $(this).attr("data-s"),
            l = $(this).attr("data-l");

        target.attr("data-h", h);
        target.attr("data-s", s);
        target.attr("data-l", l);
        target.css("background-color", $(this).css("background-color"));

        mini.attr("data-h", h);
        mini.attr("data-s", s);
        mini.attr("data-l", l);
        mini.css("background-color", $(this).css("background-color"));

        //Trigger the changedColor event, dying the cape.
        target.trigger("changedColor");

        //Redraw the picker so that it shows the correct color
        justselected = true;
        drawPicker();
    });
}

function outputImage(type) {
    var output = document.createElement("canvas");
    output.setAttribute("height", "425");
    output.setAttribute("width", type == "c" ? "221" : "238");

    var base = document.getElementById(type + "basecanvas"),
        c1 = document.getElementById(type + "ccanvas1"),
        c2 = document.getElementById(type + "ccanvas2"),
        c3 = document.getElementById(type + "ccanvas3"),
        c4 = document.getElementById(type + "ccanvas4"),
        trim = document.getElementById(type + "trimcanvas");

    var ctx = output.getContext("2d");

    ctx.drawImage(base, 0, 0);
    ctx.drawImage(c1, 0, 0);
    ctx.drawImage(c2, 0, 0);
    ctx.drawImage(c3, 0, 0);
    ctx.drawImage(c4, 0, 0);
    ctx.drawImage(trim, 0, 0);

    var img = output.toDataURL("image/png");
    var image = $("<img>").attr("src", img);

    $("#imageslot").empty().append(image);

    $("#imageblinder").css("visibility", "visible");


    output.remove();
}

function hideimg() {
    $("#imageblinder").css("visibility", "hidden");
}

function initSmallipop(element) {
    element.smallipop({
        preferredPosition: "right",
        theme: "black",
        popupDistance: 10,
        popupOffset: 10,
        hideOnPopupclick: false,
        hideOnTriggerClick: false
    });
}

//intialize variables for coloring capes
var id = "";
var justselected = false;
var mousedown = false;
var mousedownsq = false;

$(document).ready(function () {
    $("body").mouseup(function () {
        //Set mousedown variables to false anywhere on the screen.
        //Prevents mousedowning on a picker, then mouseuping off the picker.
        mousedown = false;
        mousedownsq = false;
    });

    //Initialize scrollbars
    $(".usercapes").mCustomScrollbar({
        theme: "light",
        scrollInertia: 0,
        scrollButtons: {
            enable: true
        },
        advanced: {
            updateOnContentResize: true
        }
    });

    //Init smallipop tooltips
    initSmallipop($(".preview"));

    //Apply cape styles to the designer when clicked
    $(".applycapestyle").click(function () {
        applyCapeStyle($(this));
    });

    //Handle colors (big)
    $(".color").each(function (index) {
        //Loop through color divs
        $(this).bind("changedColor", function () {
            //Bind this event to each color so that when it's changed, we know which color to dye
            doDye(index + 1);
        });

        $(this).click(function () {
            var thiscolor = $(this);
            $(".color").each(function () {
                //Loop through all colors again to select the correct one, and make sure non other are selected.
                if ($(this).attr("id") == thiscolor.attr("id")) {
                    //This is the one that was clicked on.
                    if ($(this).hasClass("selected")) {
                        //this one is already selected
                        //Unselect it
                        $(this).removeClass("selected");
                        $("#cp").css("visibility", "hidden");
                        id = "";
                        document.getElementById("colorpicker").getContext("2d").clearRect(0, 0, 150, 150);
                        document.getElementById("huepicker").getContext("2d").clearRect(0, 0, 25, 150);
                    } else {
                        //It wasn't selected
                        //Select it
                        $(this).addClass("selected");
                        $("#cp").css("visibility", "visible");

                        id = thiscolor.attr("id");
                        justselected = true;
                        drawPicker();
                    }
                } else {
                    //Make sure this one isn't shown as selected
                    $(this).removeClass("selected");
                }
            })
        });
    });

    //Handle colors (small) on the left
    $(".minicolor").each(function () {
        //Loop through all minicolors
        $(this).click(function () {
            //Set the selected color to this minicolors color.
            var selected = $(".selected");
            var color = $("#" + selected.attr("id"));
            var mini = $("#mini" + selected.attr("id"));

            selected.attr("data-h", $(this).attr("data-h"));
            selected.attr("data-s", $(this).attr("data-s"));
            selected.attr("data-l", $(this).attr("data-l"));
            selected.css("background-color", $(this).css("background-color"));
            selected.trigger("changedColor");

            mini.attr("data-h", $(this).attr("data-h"));
            mini.attr("data-s", $(this).attr("data-s"));
            mini.attr("data-l", $(this).attr("data-l"));
            mini.css("background-color", $(this).css("background-color"));

            justselected = true;
            drawPicker();
        });
    });
});