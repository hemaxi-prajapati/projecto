document.addEventListener("DOMContentLoaded", function () {
  var currentlyExpandedRow = null;

  var expandableRows = document.querySelectorAll(".expandable-row");
  expandableRows.forEach(function (row) {
    row.addEventListener("click", function () {
      var date = row.dataset.rowId;

      if (currentlyExpandedRow !== null) {
        var expandedContent =
          currentlyExpandedRow.nextElementSibling.querySelector(
            ".expanded-content"
          );
        currentlyExpandedRow.classList.remove("show");
        expandedContent.style.display = "none";
      }

      var expandedRow = row.nextElementSibling;
      var expandedContent = expandedRow.querySelector(".expanded-content");
      expandedRow.classList.toggle("show");
      expandedContent.style.display =
        expandedContent.style.display === "none" ? "block" : "none";

      currentlyExpandedRow = row;

      if(row.dataset.value == "closed"){
        getLogsForDate(date, row.dataset.userId);
        row.dataset.value = "opened";
        }
        else{
          row.dataset.value = "closed";
        }
    });
  });
});

function getLogsForDate(date, user) {
  $.ajax({
    url: "/getLogsForDate",
    method: "GET",
    data: {
      date: date,
      user: user
    },
    success: function (data) {
      var logsForDate = data.logsForDate;

      var table = document.createElement("table");
      table.classList.add("table", "table-striped", "table-bordered");

      var tableRow = document.createElement("tr");
      var tableHeader1 = document.createElement("th");
      var tableHeader2 = document.createElement("th");
      var tableHeader3 = document.createElement("th");
      var srHeader = document.createElement("th");

      srHeader.textContent = "Sr";
      tableHeader1.textContent = "Check-in";
      tableHeader2.textContent = "Check-out";
      tableHeader3.textContent = "Total Time";

      tableRow.appendChild(srHeader);
      tableRow.appendChild(tableHeader1);
      tableRow.appendChild(tableHeader2);
      tableRow.appendChild(tableHeader3);
      table.appendChild(tableRow);
      counter = 1;
      logsForDate.forEach(function (log) {
        // console.log(co++);
        var logRow = document.createElement("tr");
        var srNum = document.createElement("td");
        var checkInCell = document.createElement("td");
        var checkOutCell = document.createElement("td");
        var totalTimeCell = document.createElement("td");

        srNum.textContent = counter++;
        checkInCell.textContent = log.checkIn;
        checkOutCell.textContent = log.checkOut;
        totalTimeCell.textContent = log.TotalTime;

        logRow.appendChild(srNum);
        logRow.appendChild(checkInCell);
        logRow.appendChild(checkOutCell);
        logRow.appendChild(totalTimeCell);
        table.appendChild(logRow);
      });

      var expandedContent = document.querySelector(
        ".expanded-row.show .expanded-content"
      );
      expandedContent.innerHTML = "";
      expandedContent.appendChild(table);
    },
    error: function (xhr, status, err) {
      console.log(err);
    },
  });
}
