function favorite(id) {
    alert("You need to be logged in to set favorites!");
}

function unfavorite(id) {
    alert("You need to be logged in to set favorites!");
}

function upvote(id) {
    alert("You need to be logged in to vote on capes!");
}

function downvote(id) {
    alert("You need to be logged in to vote on capes!");
}

function flag(id) {
    alert("You need to be logged in to flag a cape!");
}

$(document).ready(function() {
    var msg = $("#loginmessage"),
        msgH = msg.height(),
        sb = msg.parents(".sidebar"),
        sbH = sb.height();

    var topMargin = Math.round((sbH / 2) - (msgH / 2));

    msg.css("margin-top", topMargin + "px");
});