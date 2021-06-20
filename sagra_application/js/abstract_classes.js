/* global Pietanza, Categoria, Serata, Aggiunta, Cassa, Scontrino, Stampante */

const classes = {
    Pietanza,
    Categoria,
    Serata,
    Aggiunta,
    Cassa,
    Scontrino,
    Stampante
};

class ClassCreator {
    constructor(className, opts, parent) {
        return new classes[className](opts, parent);
    }
}


class APIViewModel {
    constructor(pageVM, url, apiName, pageName, className, newElementData = null, needsOrdering = false, orderingField = "ordering") {
        this.pageVM = pageVM;
        this.url = url;
        this.elements = ko.observableArray([]);
        this.className = className;
        this.apiName = apiName;
        this.pageName = pageName;
        this.newElement = new ClassCreator(className, newElementData, this);
        this.newElementData = newElementData;
        this.needsOrdering = needsOrdering;
        this.orderingField = orderingField;
    }

    loadElements() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: self.url,
                type: "get",
                contentType: "application/json"
            }).done(function (data) {
                self.elements().forEach(function (element) {
                    element.setRemove(true);
                });
                data[self.apiName].forEach(function (newObj) {
                    var oldObj = self.elements().find((obj) => newObj.id === obj.id());
                    if (oldObj === undefined) {
                        self.elements.push(new ClassCreator(self.className, newObj));
                    } else {
                        oldObj.update(newObj);
                    }
                });
                self.elements.remove((element) => element.getRemove());
                if (self.needsOrdering) {
                    self.elements.sort((a, b) => a[self.orderingField]() - b[self.orderingField]());
                }
                resolve();
            }).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    loadData() {
        return this.loadElements();
    }

    display(reload) {
        var self = this;
        if (reload === true) {
            self.loadData().then(function () {
                self.pageVM.loading(false);
            });
        } else {
            self.pageVM.loading(false);
        }
    }

    displayName() {
        this.pageVM.pageName(this.pageName);
    }

    addElement() {
        var self = this;
        self.newElement.add().then(function () {
            $('.element').removeClass('open');
            $('.rotate').removeClass('rotate');
            self.newElement.update(self.newElementData);
            self.loadData();
        });
    }
}