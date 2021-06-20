/* global pageVM, MAX_MOBILE_SIZE, INTERVAL_TIME */

/* Drag and drop for ordering */
var draggedElement;
$(".contents").on('dragstart', 'div[draggable=true]', function (event) {
    clearInterval(pageVM.interval);
    draggedElement = event.currentTarget.parentNode.parentNode;
    $(draggedElement).addClass('dragged');
    $('#page').addClass('no-click');
});

$(".contents").on('dragend', 'div[draggable=true]', function () {
    var dataPrevious = ko.dataFor(draggedElement.previousElementSibling);
    var dataCurrent = ko.dataFor(draggedElement);
    var parents = ko.contextFor(draggedElement).$parents;
    var context = parents[parents.length - 2];
    if (dataPrevious.ordering === undefined ||
            dataPrevious.constructor.name !== dataCurrent.constructor.name) {
        dataCurrent.ordering(1);
    } else {
        dataCurrent.ordering(Number(dataPrevious.ordering()) + 1);
    }
    dataCurrent.upload().then(async function () {
        await context.loadData();
        pageVM.setInterval(context.interval, INTERVAL_TIME);
    }).finally(function () {
        $(draggedElement).removeClass("dragged");
        $('#page').removeClass('no-click');
    });
});

var currentElement;
$(".contents").on('dragenter', '.element:not(:nth-child(1))', function (event) {
    var element = event.currentTarget;
    if (currentElement !== element) {
        currentElement = element;
        var dataElement = ko.dataFor(element);
        var dataDraggedElement = ko.dataFor(draggedElement);
        var parents = ko.contextFor(draggedElement).$parents;
        var context = parents[parents.length - 2];
        switch (context) {
            case pageVM.listinoVM:
                if (dataElement.categoria() === dataDraggedElement.categoria()) {
                    var categoria = pageVM.listinoVM.categorie().find((cat) => cat.id() === dataElement.categoria());
                    var index1 = categoria.pietanze().findIndex((pietanza) => pietanza.id() === dataElement.id());
                    var index2 = categoria.pietanze().findIndex((pietanza) => pietanza.id() === dataDraggedElement.id());
                    var bounding = event.currentTarget.getBoundingClientRect();
                    var offset = bounding.y + (bounding.height / 2);
                    if (event.clientY - offset > 0) {
                        categoria.pietanze.swap(index1, index2);
                    } else {
                        categoria.pietanze.swap(index2, index1);
                    }
                }
                break; 
            default:
                var index1 = context.elements().findIndex((element) => element.id() === dataElement.id());
                var index2 = context.elements().findIndex((element) => element.id() === dataDraggedElement.id());
                var bounding = event.currentTarget.getBoundingClientRect();
                var offset = bounding.y + (bounding.height / 2);
                if (event.clientY - offset > 0) {
                    context.elements.swap(index1, index2);
                } else {
                    context.elements.swap(index2, index1);
                }
                break;
        }
    }
});