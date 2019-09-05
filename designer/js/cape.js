//Placeholder color array
var dyecolor = [0, 0, 0, 255];

window.onload = function () {
    //Init all canvases
    init("mbasecanvas", "mbase");
    init("cbasecanvas", "cbase");
    for (var i = 1; i <= 4; i++) {
        init("mccanvas" + i, "mc" + i);
        init("cccanvas" + i, "cc" + i);
    }
    init("mtrimcanvas", "mtrim");
    init("ctrimcanvas", "ctrim");

    //Dye them default colors
    doDye(1);
    doDye(2);
    doDye(3);
    doDye(4);
};

function init(c, i) {
    //initialize canvas (c) with image (i)
    var canvas = document.getElementById(c);
    var context = canvas.getContext("2d");
    var image = document.getElementById(i);

    context.drawImage(image, 0, 0);
}


function RGBtoHSL(red, green, blue) {
    //Converts RGB colors to HSL colors

    var r = red / 255.0;
    var g = green / 255.0;
    var b = blue / 255.0;
    var H = 0;
    var S = 0;

    var min = Math.min(r, g, b);
    var max = Math.max(r, g, b);
    var delta = (max - min);

    var L = (max + min) / 2.0;

    if (delta == 0) {
        H = 0;
        S = 0;
    } else {
        S = L > 0.5 ? delta / (2 - max - min) : delta / (max + min);

        var dR = (((max - r) / 6) + (delta / 2)) / delta;
        var dG = (((max - g) / 6) + (delta / 2)) / delta;
        var dB = (((max - b) / 6) + (delta / 2)) / delta;

        if (r == max)
            H = dB - dG;
        else if (g == max)
            H = (1 / 3) + dR - dB;
        else
            H = (2 / 3) + dG - dR;

        if (H < 0)
            H += 1;
        if (H > 1)
            H -= 1;
    }
    var HSL = {hue: 0, sat: 0, bri: 0};
    HSL.hue = (H * 360);
    HSL.sat = (S * 100);
    HSL.bri = Math.round((L * 100));

    return HSL;
}

function convertRealValToJagex(val,rmax,jmax) {
    return Math.round(val/rmax*jmax);
}

function getRSHSL(color) {
    //Converts real HSL to RS HSL.

    var r = color[0],
        g = color[1],
        b = color[2];

    var hsl = RGBtoHSL(r, g, b);

    var rs = {hue: 0, sat: 0, bri: 0};

    rs.hue = hsl.hue > 0 ? convertRealValToJagex(hsl.hue, 360, 63) : 0;
    rs.sat = hsl.sat > 0 ? convertRealValToJagex(hsl.sat, 100, 7) : 0;
    rs.bri = hsl.bri > 0 ? convertRealValToJagex(hsl.bri, 100, 126) : 0;

    $("#rshue").val(rs.hue);
    $("#rssat").val(rs.sat);
    $("#rslit").val(rs.bri);
}

function doDye(number) {
    //Dye the layer (number).

    //Initialize the max/comp canvases
    var mcvs = document.getElementById("mccanvas" + number);
    var mctx = mcvs.getContext("2d");
    var ccvs = document.getElementById("cccanvas" + number);
    var cctx = ccvs.getContext("2d");

    //Get color and seperate out the values
    var colorhex = $("#color" + number).css("background-color");
    colorhex = colorhex.match(/[\d]+/g);

    dyecolor[0] = colorhex[0];
    dyecolor[1] = colorhex[1];
    dyecolor[2] = colorhex[2];

    //Get the RS HSL values.
    getRSHSL(dyecolor);

    //Set canvases to only color colored areas
    mctx.globalCompositeOperation = "source-atop";
    cctx.globalCompositeOperation = "source-atop";

    //Color the layer
    mctx.fillStyle = "rgba(" + dyecolor[0] + ", " + dyecolor[1] + ", " + dyecolor[2] + ", 1)";
    mctx.fillRect(0, 0, 244, 463);

    cctx.fillStyle = "rgba(" + dyecolor[0] + ", " + dyecolor[1] + ", " + dyecolor[2] + ", 1)";
    cctx.fillRect(0, 0, 244, 463);
}