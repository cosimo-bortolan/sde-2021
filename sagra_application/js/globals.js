const MAX_MOBILE_SIZE = 900;
const INTERVAL_TIME = 15 * 1000;

const AUTH_API = "/auth_api";
const CORE_API = "/core_api";
const ORDER_API = "/order_api";

$.ajaxSetup({
    timeout: 1000
});

ko.observableArray.fn.swap = function (index1, index2) {
    this.valueWillMutate();

    var temp = this()[index1];
    this()[index1] = this()[index2];
    this()[index2] = temp;

    this.valueHasMutated();
};

// Register service worker to control making site work offline
if('serviceWorker' in navigator) {
  navigator.serviceWorker
           .register('/sagra_application/sw.js')
           .then(function() { console.log('Service Worker Registered'); });
}

var formatDate = function (date, format) {
    var options = {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: "2-digit", second: "2-digit"};
    date = date.toLocaleDateString("it-IT", options).split(", ");
    var day = date[0].split("/");
    var hour = date[1].split(":");
    date = format;
    date = date.replace("yyyy", day[2]);
    date = date.replace("mm", day[1]);
    date = date.replace("dd", day[0]);
    date = date.replace("hh", hour[0]);
    date = date.replace("ii", hour[1]);
    date = date.replace("ss", hour[2]);
    return date;
};
