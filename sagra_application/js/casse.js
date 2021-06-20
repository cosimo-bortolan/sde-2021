/* global INTERVAL_TIME */

class CasseViewModel extends APIViewModel {

    constructor(pageVM) {
        var newCassa = {
            permessi: 2,
            stampante: null,
            password: null,
            asporto: 0
        };
        super(pageVM, CORE_API + "/casse", "casse", "Casse", "Cassa", newCassa, false);
        this.permessi = ["AUTH", "ADMIN", "CASSA", "USER", "ALL"];
        this.stampanti = ko.observableArray([]);
        this.newPassword1 = ko.observable("");
        this.newPassword2 = ko.observable("");
    }

    loadStampanti() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CORE_API + "/stampanti",
                type: "get",
                contentType: "application/json"
            }).done(function (data) {
                self.stampanti().forEach(function (stampante) {
                    stampante.setRemove(true);
                });
                data.stampanti.forEach(function (newObj) {
                    var oldObj = self.stampanti().find((obj) => newObj.id === obj.id());
                    if (oldObj === undefined) {
                        self.stampanti.push(new Stampante(newObj));
                    } else {
                        oldObj.update(newObj);
                    }
                });
                self.stampanti.remove((stampante) => stampante.getRemove());
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
            var stampantiPr = self.loadStampanti();
            Promise.all([stampantiPr, self.loadElements()]).then(function () {
                resolve();
            }).catch(reject);
        });
    }
}
