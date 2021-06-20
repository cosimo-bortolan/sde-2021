/* global pageVM */

class Id {
  constructor(data) {
    this.id = ko.observable((data) ? data.id : "");
  }
}

class Prenotazione extends APIEntity{
  constructor(data) {
    super(ORDER_API + "/prenotazioni");
    this.id = ko.observable();
    this.pietanza = ko.observable();
    this.cassa = ko.observable();
    this.aggiunte = ko.observableArray([]);
    this.aggiunte_disponibili = ko.observableArray([]);
    this.update(data);
  }

  update(data) {
    var self = this;
    self.id((data) ? data.id : "");
    self.pietanza((data) ? data.pietanza : "");
    self.cassa((data) ? data.cassa : "");
    self.aggiunte([]);
    if (data && data.aggiunte) {
      data.aggiunte.forEach(function (aggiunta) {
        self.aggiunte.push(new Id(aggiunta));
      });
    }
    self.remove = false;
  }
}

class Pietanza extends APIEntity {
  constructor(data) {
    super(CORE_API + "/pietanze");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.categoria = ko.observable();
    this.quantita = ko.observable();
    this.quantita_prenotazione = ko.observable();
    this.prezzo = ko.observable();
    this.aggiunte = ko.observableArray([]);
    this.prenotazioni = ko.observableArray([]);
    this.ordering = ko.observable();
    this.update(data);
  }

  update(data) {
    var self = this;
    self.id((data) ? data.id : "");
    self.nome((data) ? data.nome : "");
    self.categoria((data) ? data.categoria : "");
    self.quantita((data) ? data.quantita : "");
    self.quantita_prenotazione((data) ? data.quantita_prenotazione : "");
    self.prezzo((data) ? data.prezzo : "");
    self.aggiunte([]);
    if (data && data.aggiunte) {
      data.aggiunte.forEach(function (aggiunta) {
        self.aggiunte.push(new Aggiunta(aggiunta));
      });
    }
    if (data && data.prenotazioni) {
      //mark all prenotazioni to be removed
      self.prenotazioni().forEach(function (prenotazione) {
        prenotazione.setRemove(true);
      });
      //update existing prenotazioni and add new ones
      data.prenotazioni.forEach(function (newObj) {
        var oldObj = self.prenotazioni().find((obj) => newObj.id === obj.id());
        if (oldObj === undefined) {
          oldObj = new Prenotazione(newObj);
          self.prenotazioni.push(oldObj);
        } else {
          oldObj.update(newObj);
        }
      });
      //remove deleted pietanze
      self.prenotazioni.remove((prenotazione) => prenotazione.getRemove());
    }
    self.ordering((data) ? data.ordering : "");
    self.remove = false;
  }

  increaseReservation() {
    this.quantita_prenotazione(Number(this.quantita_prenotazione()) + 1);
    var self = this;
    return new Promise((resolve, reject) => {
      $.ajax({
        url: ORDER_API + "/pietanze/" + self.id() + "/prenotazioni",
        type: "post",
        contentType: "application/json",
        data: ko.toJSON({
          aggiunte: []
        })
      }).done(resolve).fail(function (xhr) {
        ajaxError(xhr);
        reject();
      });
    });
  }

  canDecreaseReservation() {
    return this.prenotazioni().every(function (prenotazione) {
      return prenotazione.aggiunte().length === 0;
    });
    return true;
  }

  decreaseReservation() {
    if (this.quantita_prenotazione() > 0) {
      this.quantita_prenotazione(Number(this.quantita_prenotazione()) - 1);
    }
    return this.prenotazioni()[0].delete();
  }
}

class Categoria extends APIEntity {
  constructor(data) {
    super(CORE_API + "/categorie");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.gruppo = ko.observable();
    this.gruppoSelect = ko.pureComputed({
      read: function () {
        return this.gruppo() === "0" ? "CUCINA" : "BAR";
      },
      write: function (value) {
        value = value === "CUCINA" ? "0" : "1";
        this.gruppo(value);
      },
      owner: this
    });
    this.ordering = ko.observable();
    this.pietanze = ko.observableArray([]);
    this.selected = ko.observable();
    this.update(data);
  }

  update(data) {
    this.id((data) ? data.id : "");
    this.nome((data) ? data.nome : "");
    this.gruppo((data) ? data.gruppo : "");
    this.ordering((data) ? data.ordering : "");
    this.remove = false;
  }

  setSelected(value) {
    this.selected(value);
  }

  getSelected() {
    return this.selected();
  }
}

class Pagamento extends APIEntity {
  constructor(data) {
    super(ORDER_API + "/pagamenti");
    this.id = ko.observable();
    this.importo = ko.observable();
    this.mittente = ko.observable();
    this.selected = ko.observable();
    this.selectedCheckbox = ko.pureComputed({
      read: function () {
        return this.selected();
      },
      write: function (value) {
        if(value){
          pageVM.cassaVM.pagamenti().forEach((pagamento) => {
            pagamento.selected(false);
          });
        }
        this.selected(value);
      },
      owner: this
    });
    this.update(data);
  }

  update(data) {
    this.id((data) ? data.id : "");
    this.importo((data) ? data.amount : "");
    this.mittente((data) ? data.sender : "");
    this.remove = false;
  }

  setSelected(value) {
    this.selected(value);
  }

  getSelected() {
    return this.selected();
  }
}

class Aggiunta extends APIEntity {
  constructor(data) {
    super(CORE_API + "/aggiunte");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.ordering = ko.observable();
    this.selected = ko.observable();
    this.update(data);
  }

  update(data) {
    this.id((data) ? data.id : "");
    this.nome((data) ? data.nome : "");
    this.ordering((data) ? data.ordering : "");
    this.remove = false;
  }

  setSelected(value) {
    this.selected(value);
  }

  getSelected() {
    return this.selected();
  }
}

class Cassa extends APIEntity {
  constructor(data) {
    super(CORE_API + "/casse");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.password = ko.observable();
    this.permessi = ko.observable();
    this.permessiSelect = ko.pureComputed({
      read: function () {
        var permessi = "";
        switch (this.permessi()) {
          case 0:
          permessi = "AUTH";
          break;
          case 1:
          permessi = "ADMIN";
          break;
          case 2:
          permessi = "CASSA";
          break;
          case 4:
          permessi = "USER";
          break;
          case 511:
          permessi = "ALL";
          break;
        }
        return permessi;
      },
      write: function (value) {
        var permessi = null;
        switch (value) {
          case "AUTH":
          permessi = 0;
          break;
          case "ADMIN":
          permessi = 1;
          break;
          case "CASSA":
          permessi = 2;
          break;
          case "USER":
          permessi = 4;
          break;
          case "ALL":
          permessi = 511;
          break;
        }
        this.permessi(permessi);
      },
      owner: this
    });
    this.stampante = ko.observable();
    this.stampanteNome = ko.observable();
    this.stampanteId = ko.pureComputed({
      read: function () {
        return this.stampante();
      },
      write: function (value) {
        this.stampante(value ? value : null);
      },
      owner: this
    });
    this.asporto = ko.observable();
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
    this.update(data);
  }

  update(data) {
    this.id((data) ? data.id : "");
    this.nome((data) ? data.nome : "");
    this.permessi((data) ? data.permessi : "");
    this.password((data) ? data.password : "");
    this.stampante((data && data.stampante) ? data.stampante.id : null);
    this.stampanteNome((data && data.stampante) ? data.stampante.nome : "Nessuna");
    this.asporto((data) ? data.asporto : "");
    this.remove = false;
  }
}

class Serata extends APIEntity {
  constructor(data) {
    super(CORE_API + "/serate");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.inizio = ko.observable("");
    this.textInizio = ko.pureComputed(function () {
      var options = {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: "2-digit"};
      var date = new Date(this.inizio().substr(0, 10) + 'T' + this.inizio().substr(11, 5));
      return date.toLocaleDateString("it-IT", options);
    }, this);
    this.inputInizio = ko.pureComputed({
      read: function () {
        return this.inizio().substr(0, 10) + 'T' + this.inizio().substr(11, 5);
      },
      write: function (value) {
        value = value.substr(0, 10) + ' ' + value.substr(11, 5) + ':00';
        this.inizio(value);
      },
      owner: this
    });
    this.inizioTimestamp = ko.observable();
    this.fine = ko.observable("");
    this.textFine = ko.pureComputed(function () {
      var options = {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: "2-digit"};
      var date = new Date(this.fine().substr(0, 10) + 'T' + this.fine().substr(11, 5));
      return date.toLocaleDateString("it-IT", options);
    }, this);
    this.inputFine = ko.pureComputed({
      read: function () {
        return this.fine().substr(0, 10) + 'T' + this.fine().substr(11, 5);
      },
      write: function (value) {
        value = value.substr(0, 10) + ' ' + value.substr(11, 5) + ':00';
        this.fine(value);
      },
      owner: this
    });
    this.attiva = ko.observable();
    this.attivaCheckbox = ko.pureComputed({
      read: function () {
        return this.attiva() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.attiva(value);
      },
      owner: this
    });
    this.update(data);
  }

  update(data) {
    var self = this;
    self.id((data) ? data.id : "");
    self.nome((data) ? data.nome : "");
    self.inizio((data) ? data.inizio : "");
    self.inizioTimestamp((data) ? data.inizioTimestamp : "");
    self.fine((data) ? data.fine : "");
    self.attiva((data) ? data.attiva : "");
    self.remove = false;
  }
}


class Scontrino extends APIEntity {
  constructor(data) {
    super(CORE_API + "/scontrini");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.ricevuta = ko.observable();
    this.ricevutaCheckbox = ko.pureComputed({
      read: function () {
        return this.ricevuta() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.ricevuta(value);
      },
      owner: this
    });
    this.immagine = ko.observable("");
    this.dimensione = ko.observable();
    this.dimensioneCheckbox = ko.pureComputed({
      read: function () {
        return this.dimensione() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.dimensione(value);
      },
      owner: this
    });
    this.intestazione = ko.observable();
    this.prezzi = ko.observable();
    this.prezziCheckbox = ko.pureComputed({
      read: function () {
        return this.prezzi() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.prezzi(value);
      },
      owner: this
    });
    this.totale = ko.observable();
    this.totaleCheckbox = ko.pureComputed({
      read: function () {
        return this.totale() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.totale(value);
      },
      owner: this
    });
    this.tavolo = ko.observable();
    this.tavoloCheckbox = ko.pureComputed({
      read: function () {
        return this.tavolo() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.tavolo(value);
      },
      owner: this
    });
    this.barcode = ko.observable();
    this.barcodeCheckbox = ko.pureComputed({
      read: function () {
        return this.barcode() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.barcode(value);
      },
      owner: this
    });
    this.dataora = ko.observable();
    this.dataoraCheckbox = ko.pureComputed({
      read: function () {
        return this.dataora() === "1" ? true : false;
      },
      write: function (value) {
        value = value ? "1" : "0";
        this.dataora(value);
      },
      owner: this
    });
    this.categorie = ko.observableArray([]);
    this.selected = ko.observable();
    this.ordering = ko.observable();
    this.update(data);
  }

  update(data) {
    var self = this;
    this.id((data) ? data.id : "");
    this.nome((data) ? data.nome : "");
    this.ricevuta((data) ? data.ricevuta : "");
    this.immagine((data) ? data.immagine : "");
    this.dimensione((data) ? data.dimensione : "");
    this.intestazione((data) ? data.intestazione : "");
    this.prezzi((data) ? data.prezzi : "");
    this.totale((data) ? data.totale : "");
    this.tavolo((data) ? data.tavolo : "");
    this.barcode((data) ? data.barcode : "");
    this.dataora((data) ? data.dataora : "");
    this.categorie([]);
    if (data && data.categorie) {
      data.categorie.forEach(function (categoria) {
        self.categorie.push(new Id(categoria));
      });
    }
    this.ordering((data) ? data.ordering : "");
    this.remove = false;
  }

  copy(scontrino) {
    this.nome(scontrino.nome());
    this.ricevuta(scontrino.ricevuta());
    this.immagine(scontrino.immagine());
    this.dimensione(scontrino.dimensione());
    this.intestazione(scontrino.intestazione());
    this.prezzi(scontrino.prezzi());
    this.totale(scontrino.totale());
    this.tavolo(scontrino.tavolo());
    this.barcode(scontrino.barcode());
    this.dataora(scontrino.dataora());
  }

  setSelected(value) {
    this.selected(value);
  }

  getSelected() {
    return this.selected();
  }
}

class Stampante extends APIEntity {
  constructor(data) {
    super(CORE_API + "/stampanti");
    this.id = ko.observable();
    this.nome = ko.observable();
    this.ip = ko.observable();
    this.scontrini = ko.observableArray([]);
    this.update(data);
  }

  update(data) {
    var self = this;
    this.id((data) ? data.id : "");
    this.nome((data) ? data.nome : "");
    this.ip((data) ? data.ip : "");
    this.scontrini([]);
    if (data && data.scontrini) {
      data.scontrini.forEach(function (scontrino) {
        self.scontrini.push(new Id(scontrino));
      });
    }
    this.remove = false;
  }
}
