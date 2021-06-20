class LoginViewModel {

    constructor(pageVM, permessi, startPage, startPageUrl) {
        this.pageVM = pageVM;
        this.casse = ko.observableArray([]);
        this.password = ko.observable();
        this.selectedCassa = ko.observable();
        this.previousPage = null;
        this.buttonName = ko.observable('Login');
        this.permessi = permessi;
        this.startPage = startPage;
        this.startPageUrl = startPageUrl;

        this.click = this.click.bind(this);
        this.login = this.login.bind(this);
    }

    click() {
        var self = this;
        self.pageVM.loading(true);
        if (self.pageVM.user().id === -1) {
            self.display(true);
        } else {
            $.ajax({
                url: AUTH_API + "/login/" + self.pageVM.user().id,
                type: "delete",
                contentType: "application/json"
            }).done(function () {
                self.pageVM.user({nome: "Login", id: -1});
                self.previousPage = self.startPage;
                self.display(true);
                clearInterval(self.pageVM.interval);
                self.pageVM.app.quietRoute(self.startPageUrl);
                self.buttonName("Login");
            }).fail(function (xhr) {
                ajaxError(xhr);
            });
        }
    }

    loadData() {
        var self = this;
        return new Promise((resolve, reject) => {
            self.casse([]);
            $.ajax({
                url: AUTH_API + "/users?permessi=" + self.permessi
            }).done(function (data) {
                data.users.forEach(function (item) {
                    self.casse.push(new Cassa(item));
                });
                resolve();
            }).fail(function (xhr) {
                ajaxError(xhr);
                reject()
            });
        });
    }

    display(reload) {
        var self = this;
        if (reload === true) {
            self.loadData().then(function () {
                self.pageVM.loading(false);
                $('#login select:nth-child(1)').focus();
            });
        } else {
            self.pageVM.loading(false);
        }
        self.pageVM.pageName("Login");
    }

    login() {
        var self = this;
        $.ajax({
            url: AUTH_API + "/login/" + self.selectedCassa(),
            type: "put",
            crossDomain: true,
            contentType: "application/json",
            data: ko.toJSON({password: self.password()})
        }).done(function (data) {
            self.pageVM.user(data.cassa);
            $('#login input[type=password]').removeClass('errore');
            $('#login input[type=password]').attr("placeholder", "password");
            self.buttonName("Esci");
            self.previousPage.displayName();
            self.previousPage.display(true);
        }).fail(function (xhr) {
            ajaxError(xhr, function () {
                switch (xhr.responseJSON.error) {
                    case 13:
                        $('#login input[type=password]').addClass('errore');
                        $('#login input[type=password]').attr("placeholder", "password errata");
                        break;
                }
            }, null, function () {
                switch (xhr.responseJSON.error) {
                    case 18:
                        showAlertPopup("Login non consentito. Nessuna serata attiva al momento.");
                        break;
                }
            });
        }).always(function () {
            self.password("");
        });
    }

}
