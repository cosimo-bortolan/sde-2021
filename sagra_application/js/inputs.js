/* global pageVM, MAX_MOBILE_SIZE, INTERVAL_TIME */

/* Show input when doubleclick or doubletap on .text */
var showInput = function (event) {
    clearInterval(pageVM.interval);
    var page = $("#page");
    var target = event.currentTarget;
    var textDiv = $(target);
    if (page.width() > MAX_MOBILE_SIZE || textDiv.parents().hasClass('open')) {
        var inputDiv = $(target.nextElementSibling);
        var input = $(target.nextElementSibling.firstElementChild);
        textDiv.hide();
        inputDiv.css('display', 'flex');
        input.focus();
    }
};

$(".contents:not(#cassa)").on('dblclick', '.body .element .text', showInput);

var tapedTwice = false;
$(".contents:not(#cassa)").on('touchstart', '.body .element .text', function (event) {
    event.preventDefault();
    if (!tapedTwice) {
        tapedTwice = true;
        setTimeout(function () {
            tapedTwice = false;
        }, 300);
        $(':focus').blur();
        return true;
    }
    showInput(event);
});

/* Upload data when input loses focus or enter pressed */
var uploadData = function (event) {
    $('#page').addClass('no-click');
    var target = event.currentTarget;
    var data = ko.dataFor(target.parentNode.parentNode);
    var parents = ko.contextFor(target.parentNode.parentNode).$parents;
    var context = parents[parents.length - 2];
    var root = parents[parents.length - 1];
    var inputDiv = $(target);
    var input = $(target.firstElementChild);
    var textDiv = $(target.previousElementSibling);
    input.attr("disabled", true);
    data.upload(data).finally(function () {
        context.loadData().then(function () {
            root.setInterval(() => context.interval(), INTERVAL_TIME);
        }).finally(function () {
            if (!inputDiv.hasClass('fixed')) {
                inputDiv.hide();
                textDiv.show();
            }
            input.attr("disabled", false);
            $('#page').removeClass('no-click');
        });
    });
};

$(".contents").on('click', '.body .element .input.checkbox input', uploadData);
$(".contents").on('blur', '.body .element .input:not(.checkbox)', uploadData);
$(".contents").on('keypress', '.body .element .input', function (event) {
    if (event.key === 'Enter') {
        $(':focus').blur();
    }
});

/* Login when click on login button or enter pressed on password field */
$("#login input[type=button]").on('click', pageVM.loginVM.login);
$("#login input[type=password]").on('keypress', function (event) {
    if (event.key === 'Enter') {
        //this.blur();
        pageVM.loginVM.login();
    }
});

/* Increase reserved quantity for a pientaza on click */
$("#cassa.contents").on('click', '.body .element .text', function (event) {
    $('#page').addClass('no-click');
    var target = event.currentTarget;
    var data = ko.dataFor(target.parentNode.parentNode);
    var parents = ko.contextFor(target.parentNode.parentNode).$parents;
    var context = parents[parents.length - 2];
    var root = parents[parents.length - 1];
    data.increaseReservation().finally(function () {
        context.loadData().then(function () {
            root.setInterval(() => context.interval(), INTERVAL_TIME);
        }).finally(function () {
            $('#page').removeClass('no-click');
        });
    });
});


/* Decrease reserved quantity for a pientaza on click on .elimina */
$("#cassa.contents").on('click', '.body .element .elimina', function (event) {
    var target = event.currentTarget;
    var data = ko.dataFor(target.parentNode.parentNode);
    if (data.quantita_prenotazione() > 0) {
        if (data.canDecreaseReservation()) {
            var parents = ko.contextFor(target.parentNode.parentNode).$parents;
            var context = parents[parents.length - 2];
            var root = parents[parents.length - 1];
            $('#page').addClass('no-click');
            data.decreaseReservation().finally(function () {
                context.loadData().then(function () {
                    root.setInterval(() => context.interval(), INTERVAL_TIME);
                }).finally(function () {
                    $('#page').removeClass('no-click');
                });
            });
        } else {
            showAlertPopup("Sono presenti aggiunte. Per eliminare la pietanza usare il popup delle aggiunte.");
        }
    }
});

/* Delete prenotazione for a pientaza on click on .elimina inside the #aggiunte-popup*/
$("#aggiunte-popup.popup").on('click', '.body .element-container .elimina', function (event) {
    var target = event.currentTarget;
    var data = ko.dataFor(target.parentNode.parentNode);
    var root = ko.contextFor(target.parentNode.parentNode).$root;
    $('#page').addClass('no-click');
    data.delete().finally(function () {
        root.cassaVM.loadData().then(function () {
            root.setInterval(() => root.cassaVM.interval(), INTERVAL_TIME);
        }).finally(function () {
            $('#page').removeClass('no-click');
        });
    });
});

/* Switch tab when click on .title */
$("#cassa.contents").on('click', '.body .tab-navigation .title', function (event) {
    var target = event.currentTarget;
    var index = elementIndex(target) + 1;
    $('.tab-navigation .title.selected').removeClass('selected');
    $(target).addClass('selected');
    $('.tab-container>.tab.selected').removeClass('selected');
    $('.tab-container>.tab:nth-child(' + index + ')').addClass('selected');
});

var elementIndex = function (elem) {
    var i = 0;
    while ((elem = elem.previousElementSibling) !== null) {
        ++i;
    }
    return i;
};

/* Send order when click on .ordina.button */
$("#cassa.contents").on('click', '.ordina.button:not(.disable)', function (event) {
    var target = event.currentTarget;
    var data = ko.dataFor(target.parentNode.parentNode);
    if (data.ordine().length > 0) {
        $('#page').addClass('no-click');
        data.ordina().then(function () {
            $('.tab-container>.tab.selected').removeClass('selected');
            $('.tab-container>.tab:nth-child(4)').addClass('selected');
        }).finally(function () {
            pageVM.cassaVM.loadData().finally(function () {
                $('#page').removeClass('no-click');
            });
        });
    }
});

/* Reset view on .nuovo-ordine.button */
$("#cassa.contents").on('click', '.nuovo-ordine.button', function (event) {
    $('.tab-navigation .title.selected').removeClass('selected');
    $('.tab-navigation .title:nth-child(1)').addClass('selected');
    $('.tab-container>.tab.selected').removeClass('selected');
    $('.tab-container>.tab:nth-child(1)').addClass('selected');
});

/* Block sconto input when .omaggio is checked */
$("#cassa.contents").on('click', '.omaggio input', function (event) {
    var target = event.currentTarget;
    if ($(target).prop('checked')) {
        $('#cassa.contents .sconto input').prop("disabled", true);
    } else {
        $('#cassa.contents .sconto input').prop("disabled", false);
    }
});