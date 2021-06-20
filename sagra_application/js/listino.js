/* global INTERVAL_TIME */

class ListinoViewModel extends APIViewModel {

    constructor(pageVM) {
        var newPietanza = {
            quantita: 100000,
            ordering: 1
        };
        super(pageVM, CORE_API + "/pietanze", "pietanze", "Listino", "Pietanza", newPietanza, true);
        this.categorie = ko.observableArray([]);
        this.aggiunte = ko.observableArray([]);
    }

    interval() {
        $('#page').addClass('no-click');
        this.pageVM.listinoVM.loadData().finally(function () {
            $('#page').removeClass('no-click');
        });
    }

    loadPietanze() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CORE_API + "/pietanze",
                type: "get",
                contentType: "application/json"
            }).done(function (data) {
                self.categorie().forEach(function (categoria) {
                    //remove pietanze if their category changed
                    categoria.pietanze(categoria.pietanze().filter((p) => p.categoria() === categoria.id()));
                    //mark all pietanze to be removed
                    categoria.pietanze().forEach(function (pietanza) {
                        pietanza.setRemove(true);
                    });
                });
                data.pietanze.forEach(function (newObj) {
                    //if the pietanza was already present in a wrong category, remove it
                    self.categorie().forEach(function (categoria) {
                        categoria.pietanze.remove(function (pietanza) {
                            return pietanza.id() === newObj.id && newObj.categoria !== categoria.id();
                        });
                    });
                    //found the correct category and insert the pietanza or update the new one
                    var categoria = self.categorie().find((cat) => cat.id() === newObj.categoria);
                    var oldObj = categoria.pietanze().find((obj) => newObj.id === obj.id());
                    if (oldObj === undefined) {
                        categoria.pietanze.push(new Pietanza(newObj));
                    } else {
                        oldObj.update(newObj);
                    }
                });
                self.categorie().forEach(function (categoria) {
                    //remove deleted pietanze
                    categoria.pietanze.remove((pietanza) => pietanza.getRemove());
                    //order pietanze
                    categoria.pietanze.sort((a, b) => a.ordering() - b.ordering());
                });
                resolve();
            }).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    loadCategorie() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CORE_API + "/categorie",
                type: "get",
                contentType: "application/json"
            }).done(function (data) {
                self.categorie().forEach(function (categoria) {
                    categoria.setRemove(true);
                });
                data.categorie.forEach(function (newObj) {
                    var oldObj = self.categorie().find((obj) => newObj.id === obj.id());
                    if (oldObj === undefined) {
                        self.categorie.push(new Categoria(newObj));
                    } else {
                        oldObj.update(newObj);
                    }
                });
                self.categorie.remove((categoria) => categoria.getRemove());
                self.categorie.sort((a, b) => a.ordering() - b.ordering());
                resolve();
            }).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    loadAggiunte() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CORE_API + "/aggiunte",
                type: "get",
                contentType: "application/json"
            }).done(function (data) {
                self.aggiunte().forEach(function (aggiunta) {
                    aggiunta.setRemove(true);
                });
                data.aggiunte.forEach(function (newObj) {
                    var oldObj = self.aggiunte().find((obj) => newObj.id === obj.id());
                    if (oldObj === undefined) {
                        self.aggiunte.push(new Aggiunta(newObj));
                    } else {
                        oldObj.update(newObj);
                    }
                });
                self.aggiunte.remove((aggiunta) => aggiunta.getRemove());
                self.aggiunte.sort((a, b) => a.ordering() - b.ordering());
                resolve();
            }).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    loadData() {
        var self = this;
        return new Promise(function (resolve, reject) {
            var aggiuntePr = self.loadAggiunte();
            var categoriePr = new Promise((res, rej) => {
                self.loadCategorie().then(function () {
                    self.loadPietanze().then(res).catch(rej);
                }).catch(rej);
            });
            Promise.all([aggiuntePr, categoriePr]).then(function () {
                resolve();
            }).catch(reject);
        });
    }

    display(reload) {
        var self = this;
        if (reload === true) {
            self.loadData().then(function () {
                self.pageVM.loading(false);
                self.pageVM.setInterval(() => self.interval(), INTERVAL_TIME);
            });
        } else {
            self.pageVM.loading(false);
        }
    }

}
