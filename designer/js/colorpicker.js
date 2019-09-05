//Initialization
var h = 0,
    s = 0,
    l = 0,
    rgb = [];

var mousedown = false;
var mousedownsq = false;

var circleXMem = 0;
var circleYMem = 0;

var huecanvas = document.getElementById("huepicker");
var pickercanvas = document.getElementById("colorpicker");

var justselected = false;

var pickerctx = pickercanvas.getContext("2d");
var huectx = huecanvas.getContext("2d");
var hueheight = huecanvas.height;
var huewidth = huecanvas.width;
var pickerheight = pickercanvas.height;
var pickerwidth = pickercanvas.width;

huecanvas.addEventListener("mousedown", mouseDown, false);
huecanvas.addEventListener("mousemove", mouseMove, false);
pickercanvas.addEventListener("mousedown", mouseDownSq, false);
pickercanvas.addEventListener("mousemove", mouseMoveSq, false);

drawPicker();
drawHue();
drawLine(1);


function hsl2rgb(h, s, l) {
    //Converts HSL colors to RGB colors

    var m1, m2, hue;
    var r, g, b;
    s /= 100;
    l /= 100;
    if (s == 0)
        r = g = b = (l * 255);
    else {
        if (l <= 0.5)
            m2 = l * (s + 1);
        else
            m2 = l + s - l * s;
        m1 = l * 2 - m2;
        hue = h / 360;
        r = HueToRgb(m1, m2, hue + 1 / 3);
        g = HueToRgb(m1, m2, hue);
        b = HueToRgb(m1, m2, hue - 1 / 3);
    }
    return {r: r, g: g, b: b};
}

/**
 * @return {number}
 */
function HueToRgb(m1, m2, hue) {
    //converts the Hue to the correct RGB value
    var v;
    if (hue < 0)
        hue += 1;
    else if (hue > 1)
        hue -= 1;

    if (6 * hue < 1)
        v = m1 + (m2 - m1) * hue * 6;
    else if (2 * hue < 1)
        v = m2;
    else if (3 * hue < 2)
        v = m1 + (m2 - m1) * (2 / 3 - hue) * 6;
    else
        v = m1;

    return 255 * v;
}

function drawPicker() {
    //Draw the colorpicker
    var ignore = false;

    if (justselected) {
        //Color was just selected, needs special treatment
        var selected = $("#" + id);
        h = selected.attr("data-h");

        ignore = true;
        justselected = false;

        drawLine(hueheight - ((h / 360) * hueheight), false);
    }

    //Get imagedata for the picker
    var imgd = pickerctx.getImageData(0, 0, pickerwidth, pickerheight);
    var pix = imgd.data;

    //Loop through all pixels
    for (var i = 0, n = pix.length; i < n; i += 4) {
        var col = (i / 4) % pickerwidth;
        if (col == 0) {
            var line = (i / 4) / pickerheight;
            var percentageDown = line / pickerheight;
            s = 100 - (percentageDown * 100);
        }

        l = ((col) / pickerwidth) * 100;

        rgb = hsl2rgb(h, s, l);

        pix[i]     = rgb.r; 	// red
        pix[i + 1] = rgb.g; 	// green
        pix[i + 2] = rgb.b; 	// blue
        pix[i + 3] = 255;       // alpha

        //Error catch if ID isn't set
        if (id != "") {
            if (ignore) {
                //just selected, meaning we need to know where to put the circle
                if (Math.ceil(h) == selected.attr("data-h") && Math.ceil(s) == selected.attr("data-s") && Math.ceil(l) == selected.attr("data-l")) {
                    circleXMem = col;
                    circleYMem = line;
                    ignore = false;
                }
            }

            if (line == circleYMem && col == circleXMem && !ignore) {
                //This is where our color is that we want
                var color = $("#" + id);
                var minicolor = $("#mini" + id);

                //Set color attributes and trigger the color changed (dye cape)
                color.attr("data-h", Math.round(h));
                color.attr("data-s", Math.round(s));
                color.attr("data-l", Math.round(l));
                color.css("background-color", "hsl(" + Math.round(h) + ", " + Math.round(s) + "%, " + Math.round(l) + "%)");
                color.trigger("changedColor");

                //Set minicolor attributes
                minicolor.attr("data-h", Math.round(h));
                minicolor.attr("data-s", Math.round(s));
                minicolor.attr("data-l", Math.round(l));
                minicolor.css("background-color", "hsl(" + Math.round(h) + ", " + Math.round(s) + "%, " + Math.round(l) + "%)");

                $("#" + id + "Hval").val(Math.round(h));
                $("#" + id + "Sval").val(Math.round(s));
                $("#" + id + "Lval").val(Math.round(l));
            }
        }
    }
    //Draw the new pixels
    pickerctx.putImageData(imgd, 0, 0);

    //Draw the circle around the selected color
    pickerctx.strokeStyle = "white";
    pickerctx.lineWidth = 2;

    pickerctx.beginPath();
    pickerctx.arc(circleXMem, circleYMem, 5, 0, 2 * Math.PI, false);
    pickerctx.stroke();
}

function drawHue() {
    //Draw the hue picker
    var imgd = huectx.getImageData(0, 0, huewidth, hueheight);
    var pix = imgd.data;

    var hue = 360,
        sat = 100,
        lum = 50;

    //Parse all the pixels to color it necessarily
    for (var i = 0; i < pix.length; i += 4) {
        var col = (i / 4) % huewidth;

        if (col == 0) {
            var line = ((i / 4)) / huewidth;
            var percentageDown = line / hueheight;
            hue = 360 - (percentageDown * 360);
        }

        rgb = hsl2rgb(hue, sat, lum);

        if (col > 2 && col < 22) {
            pix[i  ] = rgb.r; 	    // red
            pix[i + 1] = rgb.g; 	// green
            pix[i + 2] = rgb.b; 	// blue
            pix[i + 3] = 255;       // alpha
        }
    }

    //Draw the new pixels.
    huectx.putImageData(imgd, 0, 0);
}

function drawLine(y, draw) {
    //draw is an optional parameter. If undefined, set to true
    if (typeof(draw) == "undefined") {
        draw = true;
    }

    //Clear out and redraw the hue
    huectx.clearRect(0, 0, huewidth, hueheight);
    drawHue();

    //Draw the line where the mouse is.
    huectx.lineWidth = 2;
    huectx.strokeStyle = "white";

    huectx.beginPath();
    huectx.moveTo(0, y - 2);
    huectx.lineTo(0, y + 2);
    huectx.moveTo(0, y);
    huectx.lineTo(huewidth, y);
    huectx.moveTo(huewidth, y - 2);
    huectx.lineTo(huewidth, y + 2);
    huectx.stroke();

    var percentageDown = y / pickerheight;
    h = Math.round(360 - (percentageDown * 360));

    //Prevents infinite looping in certain instances.
    if (draw) {
        drawPicker();
    }
}

function mouseDown(e) {
    //Event handler for mouse down on the H picker
    e.preventDefault(); //Prevent right click menu

    if (e.which == 1) {
        //Only activate on left mouse button

        mousedown = true;
        mouseMove(e);
    }

}

function mouseMove(e) {
    //Event handler for mouse movement on the H picker
    if (mousedown) {
        //If the mouse is down, we want the Y coordinate of the mouse ON THE PICKER (NOT THE PAGE).
        //Offset the values by the height of the masthead and the ad.
        var headerheight = parseInt($("#masthead").height(), 10) + parseInt($("body").css("margin-top"), 10) + parseInt($(".latestNews").css("margin-bottom"), 10) + parseInt($(".latestNews").height(), 10);

        var yOffset = headerheight + 3;

        //var x;
        var y;
        if (e.pageX || e.pageY) {
            //x = e.pageX;
            y = e.pageY;
        }
        else {
            //x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        y -= huecanvas.offsetTop + yOffset;

        //Draw the line on the hue picker at the Y position
        drawLine(y);
    }
}

function mouseDownSq(e) {
    //Event handler for mouse down on the S/L picker
    e.preventDefault(); //Prevent right click menu

    if (e.which == 1) {
        //Only activate on left mouse button

        mousedownsq = true;
        mouseMoveSq(e);
    }

}

function mouseMoveSq(e) {
    //Event handler for mouse movement on the S/L picker
    if (mousedownsq) {
        //If the mouse is down, get the X and Y coordinate of the mouse ON THE PICKER (NOT THE PAGE).
        //Offset the values by the height of the masthead and the ad.s
        var headerheight = (parseInt($("#masthead").height(), 10) + parseInt($("body").css("margin-top"), 10) + parseInt($(".latestNews").css("margin-bottom"), 10) + parseInt($(".latestNews").height(), 10));

        var yOffset = headerheight + 3;

        var x;
        var y;
        if (e.pageX || e.pageY) {
            x = e.pageX;
            y = e.pageY;
        }
        else {
            x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        if($(window).width() > 1024)
            x -= pickercanvas.offsetLeft + Math.round(($(window).width() - 1024) / 2);
        else
            x -= pickercanvas.offsetLeft;
        y -= pickercanvas.offsetTop + yOffset;

        //Set circle position (mouse coordinates) on the S/L picker to get the color there
        circleXMem = x;
        circleYMem = y;

        console.log(x + ", " + y);

        pickerctx.clearRect(0, 0, 360, 360);
        //Draw the S/L picker
        drawPicker();
    }
}

