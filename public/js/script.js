var timerInterval;
var currentTimerId = null; // Variable to store the ID of the currently running timer

function data(id, Tid, time) {
  var timerButton = document.getElementById(id.id);

  if (timerInterval && currentTimerId !== Tid.id) {
    // Timer is running for another task, so stop it
    stopTimer();

    resetButton(); // Reset the button for the previous task
  }
  if (timerInterval && currentTimerId === Tid.id) {
    // Timer is running for the current task, so stop it
    stopTimer();
    resetButton(); // Reset the button for the current task
  } else {
    // Timer is not running, so start it
    startTimer(Tid.id, time);
    timerButton.textContent = "Stop"; // Change the button text
    timerButton.classList.remove("btn-primary"); // Remove primary button style
    timerButton.classList.add("btn-danger"); // Add success button style
    currentTimerId = Tid.id; // Update the current timer ID
  }
}

// Function to start the timer
function startTimer(id, time) {
  var startTime = Date.now(); // Get the current timestamp

  timerInterval = setInterval(function () {
    var elapsedTime = Date.now() - startTime; // Calculate elapsed time

    var formattedTime = formatTime(elapsedTime); // Format the time

    document.getElementById(id).textContent = formattedTime;
    localStorage.setItem("latestTime", formattedTime);
    localStorage.setItem("id", id);
  }, 1000); // Update the display every 1 second
}

// Function to stop the timer
function stopTimer() {
  var totalTimer = localStorage.getItem("latestTime");

  var id = localStorage.getItem("id").substring(5);
  sendTimerUserTaskId(id, totalTimer);
  clearInterval(timerInterval); // Clear the interval
  timerInterval = null; // Reset the interval variable
}

// Function to reset the button text for all tasks
function resetButton() {
  var buttons = document.querySelectorAll("table .taskTimerBtn");
  for (var i = 0; i < buttons.length; i++) {
    buttons[i].textContent = "Start";
    buttons[i].classList.remove("btn-danger"); // Remove success button style
    buttons[i].classList.add("btn-primary"); // Add primary button style
  }
}

// Format the time as HH:MM:SS
function formatTime(time) {
  var seconds = Math.floor(time / 1000) % 60;
  var minutes = Math.floor(time / 1000 / 60) % 60;
  var hours = Math.floor(time / 1000 / 60 / 60);

  // Add leading zeros if necessary
  var formattedSeconds = ("0" + seconds).slice(-2);
  var formattedMinutes = ("0" + minutes).slice(-2);
  var formattedHours = ("0" + hours).slice(-2);

  return formattedHours + ":" + formattedMinutes + ":" + formattedSeconds;
}

function sendTimerUserTaskId(taskId, totalTimer) {
  var url = "/Employee/Dashboard/stopTimer";
  var data = {
    taskId: taskId,
    timerValue: totalTimer,
  };

  $.ajax({
    url: url,
    method: "GET",
    data: {
      taskId: taskId,
      timerValue: totalTimer,
    },
    success: function (response) {
      $("#totalTime" + taskId).text(response.timer);
    },
    error: function (xhr, status, error) {
      // Handle the error response
      console.error("Error sending data:", error);
    },
  });
}
