function PageVM() {
    var self = this;
    self.serateVM = new SerateViewModel(self);
    self.listinoVM = new ListinoViewModel(self);
    self.categorieVM = new CategorieViewModel(self);
    self.aggiunteVM = new AggiunteViewModel(self);
    self.casseVM = new CasseViewModel(self);
    self.stampantiVM = new StampantiViewModel(self);
    self.scontriniVM = new ScontriniViewModel(self);
    self.loginVM = new LoginViewModel(self, 1, self.serateVM, '#serate');
    self.pageName = ko.observable();
    self.user = ko.observable({id: -1, nome: "Login"});
    self.loading = ko.observable();
    self.networkError = ko.observable();
    self.interval = null;
    self.popupFunction = null;
    self.popupReset = null;
    self.popupError = null;
    self.popupAlert = ko.observable();
    self.popupDetails = ko.observable();
    self.popupConfirm = ko.observable();
    self.popupInput = ko.observable();

    self.setInterval = function (callback, seconds) {
        clearInterval(self.interval);
        self.interval = setInterval(callback, seconds);
    };

    self.linkTo = function (page) {
        if (location.hash === page) {
            self.app.refresh();
        } else {
            self.app.setLocation(page);
        }
    };

    self.checkLogin = function (page) {
        clearInterval(self.interval);
        self.loading(true);
        page.displayName();
        $.ajax({
            url: AUTH_API + "/login?permessi=1"
        }).done(function (data) {
            self.networkError(false);
            self.user(data.cassa);
            self.loginVM.buttonName("Esci");
            page.display(true);
        }).fail(function (xhr) {
            ajaxError(xhr, null, null, null, function () {
                self.networkError(false);
                self.user({id: -1, nome: "Login"});
                self.loginVM.buttonName("Login");
                self.loginVM.previousPage = page;
                self.loginVM.display(true);
            }, null, null, function () {
                self.networkError(true);
                self.setInterval(function () {
                    self.checkLogin(page);
                }, 2000);
            });
        });
    };

    self.app = $.sammy('.contents', function () {
        var sammy = this;

        sammy.quiet = false;

        sammy.quietRoute = function (location) {
            sammy.quiet = true;
            sammy.setLocation(location);
        };

        sammy.after(function () {
            sammy.quiet = false;
        });

        sammy.get('#serate', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.serateVM);
            }
        });
        sammy.get('#listino', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.listinoVM);
            }
        });
        sammy.get('#categorie', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.categorieVM);
            }
        });
        sammy.get('#aggiunte', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.aggiunteVM);
            }
        });
        sammy.get('#casse', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.casseVM);
            }
        });
        sammy.get('#stampanti', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.stampantiVM);
            }
        });
        sammy.get('#scontrini', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.scontriniVM);
            }
        });
        sammy.get('', function () {
            location.hash = 'serate';
        });
    }).run();
}
