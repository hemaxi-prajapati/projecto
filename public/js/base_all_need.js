document.addEventListener('DOMContentLoaded', function () {
    let alerts = document.querySelectorAll('.alertFlash');

    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.display = 'none';

        }, 4000);
    });
});