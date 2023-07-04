var sec = 0;
var min = 0;
var hrs = 0;
var start_Timer = null;

let stateCheck = setInterval(() => {
    if (document.readyState === 'complete') {
        clearInterval(stateCheck);
        if (sessionStorage.getItem("TimerStatus"));
        {
            if (sessionStorage.getItem("TimerStatus") == "start") {
                sec = sessionStorage.getItem("TimerTimeSecond")
                min = sessionStorage.getItem("TimerTimeMin")
                hrs = sessionStorage.getItem("TimerTimeHour")
                checkInCheckOutClick();
            }
        }
        getTimeFromDatabase();
    }
}, 100);

function stopGlobleTimer() {
    clearInterval(start_Timer);
    setCheckoutTimeToDatabase();
}

function startGlobleTimer() {
    clearInterval(start_Timer);
    start_Timer = setInterval(() => {
        sec++;
        if (sec == 60) {
            min++;
            sec = 0;
        }
        if (min == 60) {
            hrs++;
            min = 0;
        }
        updateDisplay();
    }, 1000);
    return start_Timer;
}

function updateDisplay() {
    phrs = (hrs.toString().length < 2) ? '0' + hrs : hrs;
    pmin = (min.toString().length < 2) ? '0' + min : min;
    psec = (sec.toString().length < 2) ? '0' + sec : sec;
    document.querySelector('.hrs').innerText = phrs;
    document.querySelector('.min').innerText = pmin;
    document.querySelector('.sec').innerText = psec;

}
function getTimeFromDatabase() {

    $.ajax({
        url: "/getGlobleAttendanceTime",
        method: "POST",
        data: {
            // userId: userId,
        },
        success: function (response) {
            sec = sec < response.second ? response.second : sec;
            min = min < response.minut ? response.minut : min;
            hrs = hrs < response.hour ? response.hour : hrs;
            // min = response.minut;
            // hrs = response.hour;
            updateDisplay()
        },
        error: function (xhr, status, error) {
            console.error("Error sending data:", error);
        },
    });
}
function setcheckInTimeToDatabase() {

    $.ajax({
        url: "/setCheckInGlobleAttendanceTime",
        method: "GET",
        success: function (response) {
            // getTimeFromDatabase()
        },
        error: function (xhr, status, error) {
            console.error("Error sending data:", error);
        },
    });
}
function setCheckoutTimeToDatabase() {

    $.ajax({
        url: "/setCheckOutGlobleAttendanceTime",
        method: "POST",
        data: {
            hours: hrs,
            minut: min,
            second: sec,
        },
        success: function (response) {
            clearInterval(start_Timer);
        },
        error: function (xhr, status, error) {
            console.error("Error sending data:", error);
        },
    });
}
function checkInCheckOutClick() {
    $("#playPauseBtn").toggleClass('fa-play');
    $("#playPauseBtn").toggleClass('fa-pause');
    let status = ($("#checkInCheckOutBtn").val());
    if (status == "stop") {
        $("#checkInCheckOutBtn").val("start");
        if (sessionStorage.getItem("TimerStatus") != "start") {

            setcheckInTimeToDatabase();
        }
        startGlobleTimer()
        sessionStorage.setItem("TimerStatus", $("#checkInCheckOutBtn").val());

    }
    else {
        $("#checkInCheckOutBtn").val("stop");
        stopGlobleTimer()
        sessionStorage.setItem("TimerStatus", $("#checkInCheckOutBtn").val());
    }

}

window.onbeforeunload = function () {
    // console.log("ss");
    sessionStorage.setItem("TimerTimeSecond", sec);
    sessionStorage.setItem("TimerTimeMin", min);
    sessionStorage.setItem("TimerTimeHour", hrs);
    // setCheckoutTimeToDatabase();
    sessionStorage.setItem("TimerStatus", $("#checkInCheckOutBtn").val());
    // stopGlobleTimer();
};

