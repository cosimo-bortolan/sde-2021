class APIEntity {
    constructor(url) {
        if (new.target === APIEntity) {
            throw new TypeError("Cannot construct APIEntity instances directly");
        }
        this.url = url;
        this.remove = true;
    }

    add() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: self.url,
                type: "post",
                contentType: "application/json",
                data: ko.toJSON(self)
            }).done(resolve).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    upload() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: self.url + "/" + self.id(),
                type: "patch",
                contentType: "application/json",
                data: ko.toJSON(self)
            }).done(resolve).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    delete() {
        var self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: self.url + "/" + self.id(),
                type: "delete",
                contentType: "application/json"
            }).done(resolve).fail(function (xhr) {
                ajaxError(xhr);
                reject();
            });
        });
    }

    setRemove(value) {
        this.remove = value;
    }

    getRemove() {
        return this.remove;
    }
}