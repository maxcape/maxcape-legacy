var minutes = 0,
    seconds = 0,
    interval,
    endTime;

function timer() {
    var now = new Date().getTime(),
        timeRemaining = endTime - now;

    var totalSeconds = Math.floor(timeRemaining / 1000);

    minutes = Math.floor(totalSeconds / 60);
    seconds = (totalSeconds - (minutes * 60));

    setLabels();

    if (minutes <= 0 && seconds <= 0 || minutes <= -1) {
        alertEnd();
    }

}

function alertEnd() {
    stopTimer();
    playSound("/sound.mp3");
    setTimeout(function () {
        alert("The timer has ended!")
    }, 500);
}

function startTimer() {
    var running = localStorage.getItem('running');
    running = parseInt(running, 10);

    if (running == 1) {
        $("#running-icon").addClass('running');

        endTime = localStorage.getItem("endTime");

        var now = new Date().getTime(),
            timeRemaining = endTime - now;

        if (timeRemaining < 0) {
            alertEnd();
        } else {
            var totalSeconds = Math.floor(timeRemaining / 1000);

            minutes = Math.floor(totalSeconds / 60);
            seconds = (totalSeconds - (minutes * 60));

            setLabels();
            $("#startbtn").prop("disabled", true);
            $("#timer").addClass("timer-running");
            interval = setInterval(timer, 1000);
        }
    } else {
        minutes = parseInt($("#minutes-set").val(), 10);
        seconds = parseInt($("#seconds-set").val(), 10);

        if ((minutes * 60) + seconds > 0) {
            $("#running-icon").addClass('running');
            setEndTime();
            localStorage.setItem("running", 1);
            $("#startbtn").prop("disabled", true);
            $("#timer").addClass("timer-running");
            interval = setInterval(timer, 1000);
        } else {
            alert("Timer needs to run for at least 1 second.");
        }
    }
}

function stopTimer() {
    localStorage.setItem('running', 0);
    clearInterval(interval);

    $("#running-icon").removeClass('running');
    $("#timer").removeClass("timer-running");

    $("#minutes").text("00");
    $("#seconds").text("00");

    $("#startbtn").prop("disabled", false);
}

function setEndTime() {
    var now = new Date().getTime(),
        timeToAdd = ((minutes * 60) + seconds) * 1000;

    endTime = now + timeToAdd;

    localStorage.setItem("endTime", endTime);
}

function setLabels() {
    var S_Display = 0,
        M_Display = 0;

    if (seconds < 10) {
        S_Display = "0" + seconds;
    } else {
        S_Display = seconds;
    }

    if (minutes < 10) {
        M_Display = "0" + minutes;
    } else {
        M_Display = minutes;
    }

    $("#minutes").text(M_Display);
    $("#seconds").text(S_Display);
}

function playSound(soundfile) {
    document.getElementById("sounddummy").innerHTML =
        "<embed src=\"" + soundfile + "\" hidden=\"true\" autostart=\"true\" loop=\"false\" />";
}

function toggleTimer(anims) {
    var timer = $("#timer");
    if (timer.hasClass("minimized")) {
        if (anims) {
            timer.switchClass("minimized", "maximized", 250);
        } else {
            timer.removeClass("minimized").addClass("maximized");
        }
        timer.find("i").removeClass("icon-double-angle-up").addClass("icon-double-angle-down");

        localStorage.setItem("timerState", "up");
    } else {
        if (anims) {
            timer.switchClass("maximized", "minimized", 250);
        } else {
            timer.removeClass("maximized").addClass("minimized");
        }

        timer.find("i").removeClass("icon-double-angle-down").addClass("icon-double-angle-up");

        localStorage.setItem("timerState", "down");
    }
}