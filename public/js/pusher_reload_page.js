// document.addEventListener('DOMContentLoaded', function () {
// });
Pusher.logToConsole = true;
var pusher = new Pusher('60aef47270465eb54b03', { cluster: 'ap2' });
var channel = pusher.subscribe('my-channel');
channel.bind('reload-page', function (data) {
    if (document.getElementById("tdWithUserId").getAttribute('data-userId') == data.UserId) {
        setTimeout(function () {
            location.reload();
        }, 100);
    }
});