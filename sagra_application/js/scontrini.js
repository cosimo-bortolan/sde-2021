class ScontriniViewModel extends APIViewModel {

    constructor(pageVM) {
        var newScontrino = {
            nome: "",
            ricevuta: 0,
            immagine: "",
            intestazione: "",
            dimensione: 1,
            prezzi: 0,
            totale: 0,
            tavolo: 0,
            barcode: 0,
            dataora: 1,
            ordering: 1
        };
        super(pageVM, CORE_API + "/scontrini", "scontrini", "Scontrini", "Scontrino", newScontrino, true);
        this.categorie = ko.observableArray([]);
        this.modScontrino = ko.observable(new Scontrino());
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

    loadData() {
        var self = this;
        return new Promise(function (resolve, reject) {
            var categoriePr = self.loadCategorie();
            Promise.all([categoriePr, self.loadElements()]).then(function () {
                resolve();
            }).catch(reject);
        });
    }

}
