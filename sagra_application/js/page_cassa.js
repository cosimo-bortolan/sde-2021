function PageVM() {
    var self = this;
    self.cassaVM = new CassaViewModel(self);
    self.loginVM = new LoginViewModel(self, 2, self.cassaVM, '#cassa');
    self.pageName = ko.observable();
    self.user = ko.observable({id: -1, nome: "Login"});
    self.loading = ko.observable();
    self.networkError = ko.observable();
    self.interval = null;
    self.popupFunction = null;
    self.popupAlert = ko.observable();
    self.popupDetails = ko.observable();
    self.popupConfirm = ko.observable();
    self.popupInput = ko.observable();
    self.popupError = ko.observable();

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
            url: AUTH_API + "/login?permessi=2"
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

        sammy.get('#cassa', function () {
            if (!sammy.quiet) {
                self.checkLogin(self.cassaVM);
            }
        });
        sammy.get('', function () {
            location.hash = 'cassa';
        });
    }).run();
}
