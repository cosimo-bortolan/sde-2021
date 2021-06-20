class StampantiViewModel extends APIViewModel {

    constructor(pageVM){
        super(pageVM, CORE_API + "/stampanti", "stampanti", "Stampanti", "Stampante", {});
        this.scontrini = ko.observableArray([]);
    }

    loadScontrini() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CORE_API + "/scontrini",
                type: "get",
                contentType: "application/json"
            }).done(function (data) {
                self.scontrini().forEach(function (scontrino) {
                    scontrino.setRemove(true);
                });
                data.scontrini.forEach(function (newObj) {
                    var oldObj = self.scontrini().find((obj) => newObj.id === obj.id());
                    if (oldObj === undefined) {
                        self.scontrini.push(new Scontrino(newObj));
                    } else {
                        oldObj.update(newObj);
                    }
                });
                self.scontrini.remove((scontrino) => scontrino.getRemove());
                self.scontrini.sort((a, b) => a.ordering() - b.ordering());
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
            var scontriniPr = self.loadScontrini();
            Promise.all([scontriniPr, self.loadElements()]).then(function () {
                resolve();
            }).catch(reject);
        });
    }
}
