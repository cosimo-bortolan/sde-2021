/* global INTERVAL_TIME */

class CassaViewModel {

  constructor(pageVM) {
    this.pageVM = pageVM;
    this.categorie = ko.observableArray([]);
    this.ordine = ko.observableArray([]);
    this.totale = ko.observable(0);
    this.asporto = ko.observable('0');
    this.nome = ko.observable("");
    this.asportoCheckbox = ko.pureComputed({
      read: function () {
        return this.asporto() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.asporto(value);
      },
      owner: this
    });
    this.tavolo = ko.observable(0);
    this.coperti = ko.observable();
    this.contanti = ko.observable("");
    this.resto = ko.computed(function () {
      if (this.contanti() !== "") {
        return (this.contanti() - this.totale() + Number(this.sconto())).toFixed(2) + " €";
      } else {
        return "0.00 €";
      }
    }, this);
    this.omaggio = ko.observable(0);
    this.omaggioCheckbox = ko.pureComputed({
      read: function () {
        if (this.omaggio() === "1") {
          return true;
        } else {
          this.sconto(0);
          return false;
        }
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.omaggio(value);
      },
      owner: this
    });
    this.sconto = ko.observable(0);
    this.scontoString = ko.computed({
      read: function () {
        if (this.omaggio() === "1") {
          this.sconto(this.totale());
          return this.totale().toFixed(2);
        } else {
          return this.sconto() ? this.sconto() : "";
        }
      },
      write: function (value) {
        (value !== "") ? this.sconto(value) : this.sconto(0);
      },
      owner: this
    });
    this.totaleString = ko.computed(function () {
      if (this.sconto()) {
        return (this.totale() - this.sconto()).toFixed(2) + " € (" + this.totale().toFixed(2) + ")";
      } else if (this.omaggio() === "1") {
        return "0.00 € (" + this.totale().toFixed(2) + ")";
      } else {
        return this.totale().toFixed(2) + " €";
      }
    }, this);
    this.ordineConfermato = ko.observable();
    this.pietanzaAggiunte = ko.observable();
    this.pagamenti = ko.observableArray([]);
    this.loadingPagamenti = ko.observable();
    this.pagamento = null;
  }

  loadPietanze() {
    var self = this;
    return new Promise((resolve, reject) => {
      $.ajax({
        url: ORDER_API + "/pietanze",
        type: "get",
        contentType: "application/json"
      }).done(function (data) {
        self.totale(0);
        self.ordine([]);
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
            oldObj = new Pietanza(newObj);
            categoria.pietanze.push(oldObj);
          } else {
            oldObj.update(newObj);
          }
          if (Number(oldObj.quantita_prenotazione()) !== 0) {
            self.ordine.push(oldObj);
            self.totale(self.totale() + oldObj.prezzo() * oldObj.quantita_prenotazione());
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
        console.log("fail pietanze")
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
        console.log("fail categorie")
        ajaxError(xhr);
        reject();
      });
    });
  }

  loadPagamenti() {
    var self = this;
    self.loadingPagamenti(true);
    return new Promise((resolve, reject) => {
      $.ajax({
        url: ORDER_API + "/pagamenti",
        type: "get",
        contentType: "application/json"
      }).done(function (data) {
        self.pagamenti().forEach(function (categoria) {
          categoria.setRemove(true);
        });
        data.pagamenti.forEach(function (newObj) {
          var oldObj = self.pagamenti().find((obj) => newObj.id === obj.id());
          if (oldObj === undefined) {
            self.pagamenti.push(new Pagamento(newObj));
          } else {
            oldObj.update(newObj);
          }
        });
        self.pagamenti.remove((payment) => payment.getRemove());
        resolve();
      }).fail(function (xhr) {
        ajaxError(xhr);
        reject();
      }).always(function(){
        self.loadingPagamenti(false);
      });
    });
  }

  loadData() {
    var self = this;
    return new Promise(function (resolve, reject) {
      var categoriePr = new Promise((res, rej) => {
        self.loadCategorie().then(function () {
          self.loadPietanze().then(res).catch(rej);
        }).catch(rej);
      });
      Promise.all([categoriePr]).then(function () {
        resolve();
      }).catch(reject);
    });
  }

  display(reload) {
    var self = this;
    if (reload === true) {
      self.loadData().then(function () {
        self.pageVM.loading(false);
        self.pageVM.setInterval(self.interval, INTERVAL_TIME);
      }).catch(function(){
        console.log('here');
      });
    } else {
      self.pageVM.loading(false);
    }
  }

  displayName() {
    this.pageVM.pageName('Cassa');
  }

  ordina() {
    var self = this;
    var pietanze = [];
    self.ordine().forEach(function (pietanza) {
      pietanza.prenotazioni().forEach(function (prenotazione) {
        pietanze.push({
          id: pietanza.id(),
          prenotazione: prenotazione.id()
        });
      });
    });
    if (self.asporto() === '1') {
      self.coperti(0);
    }
    return new Promise((resolve, reject) => {
      $.ajax({
        url: ORDER_API + "/ordini",
        type: "post",
        contentType: "application/json",
        data: ko.toJSON({
          pietanze: pietanze,
          omaggio: self.sconto,
          asporto: self.asporto,
          nome: self.nome,
          tavolo: self.tavolo,
          coperti: self.coperti,
          pagamento: self.pagamento
        }),
        timeout: 10000
      }).done(function (data) {
        self.ordineConfermato(data.ordine.numero_ordine);
        self.tavolo(0);
        self.asporto('0');
        self.coperti(undefined);
        self.nome("");
        self.contanti("");
        self.omaggio(0);
        self.sconto(0);
        self.pagamento = null;
        resolve();
      }).fail(function (xhr) {
        ajaxError(xhr, null, null, null, null, function (data) {
          var response = xhr.responseJSON
          self.ordineConfermato(response.details.ordine.numero_ordine);
          self.tavolo(0);
          self.asporto('0');
          self.coperti(undefined);
          self.nome("");
          self.contanti("");
          self.omaggio(0);
          self.sconto(0);
          self.pagamento = null;
          switch(response.error){
            case 19: alert("Ordine confermato ma non stampato.\r\nScontrini stampati: " + response.details.printed + "\r\nScontrini non stampati: " + response.details.not_printed); break;
          }
          resolve()
        });
        reject();
      });
    });
  }


  reset() {
    var self = this;
    return new Promise((resolve, reject) => {
      $.ajax({
        url: ORDER_API + "/prenotazioni",
        type: "delete",
        contentType: "application/json"
      }).done(function (data) {
        self.tavolo(0);
        self.asporto('0');
        self.coperti(undefined);
        self.nome("");
        self.contanti("");
        self.omaggio(0);
        self.sconto(0);
        self.pagamento = null;
        resolve();
      }).fail(function (xhr) {
        ajaxError(xhr);
        reject();
      });
    });
  }
}
