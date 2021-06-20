/* global pageVM */

/* Show delete popup when click on .elimina */
$(".contents:not(#cassa)").on('click', '.body .element .elimina', function (event) {
  //retrive caller
  var target = event.currentTarget;
  var data = ko.dataFor(target);
  //retrive context
  var parents = ko.contextFor(target.parentNode.parentNode).$parents;
  var context = parents[parents.length - 2];
  //set alert messages and inputs
  switch (context) {
    case pageVM.serateVM:
    pageVM.popupAlert("ATTENZIONE! Verranno eliminati tutti\
    gli ordini associati con questa serata!");
    pageVM.popupDetails("Digita&nbsp;<span class='bold-500'>" +
    data.nome().split(" ")[0] +
    "</span>&nbsp;per confermare");
    pageVM.popupConfirm(data.nome().split(" ")[0]);
    break;
    case pageVM.listinoVM:
    pageVM.popupAlert("ATTENZIONE! La pietanza verrà eliminata da\
    tutti gli ordini in cui è presente!");
    pageVM.popupDetails("Digita&nbsp;<span class='bold-500'>" +
    data.nome().split(" ")[0] +
    "</span>&nbsp;per confermare");
    pageVM.popupConfirm(data.nome().split(" ")[0]);
    break;
    case pageVM.categorieVM:
    pageVM.popupAlert("ATTENZIONE! Verranno eliminate tutte le\
    pietanze associate con questa categoria!");
    pageVM.popupDetails("Digita&nbsp;<span class='bold-500'>" +
    data.nome().split(" ")[0] +
    "</span>&nbsp;per confermare");
    pageVM.popupConfirm(data.nome().split(" ")[0]);
    break;
    case pageVM.aggiunteVM:
    pageVM.popupAlert("ATTENZIONE! L'aggiunta verrà eliminata da\
    tutti gli ordini in cui è presente!");
    pageVM.popupDetails("Digita&nbsp;<span class='bold-500'>" +
    data.nome().split(" ")[0] +
    "</span>&nbsp;per confermare");
    pageVM.popupConfirm(data.nome().split(" ")[0]);
    break;
    case pageVM.scontriniVM:
    pageVM.popupAlert("ATTENZIONE! L'eliminazione è definitiva!");
    pageVM.popupDetails("Digita&nbsp;<span class='bold-500'>" +
    data.nome().split(" ")[0] +
    "</span>&nbsp;per confermare");
    pageVM.popupConfirm(data.nome().split(" ")[0]);
    break;
  }
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      if (pageVM.popupConfirm() !== null &&
      pageVM.popupConfirm() !== pageVM.popupInput()) {
        //set error message and clean input
        $('#delete-popup>.body>.line:nth-child(3)>input').addClass('errore');
        $('#delete-popup>.body>.line:nth-child(3)>input').attr("placeholder", "il testo non corrisponde");
        reject();
      } else {
        data.delete()
        .then(() => context.loadData().then(resolve))
        .catch(() => closePopup());
      }
    });
  };
  pageVM.popupReset = function () {
    pageVM.popupAlert(null);
    pageVM.popupConfirm(null);
    pageVM.popupDetails(null);
    pageVM.popupInput(null);
    $('#delete-popup>.body>.line:nth-child(3)>input').attr("placeholder", "");
    $('#delete-popup>.body>.line:nth-child(3)>input').removeClass('errore');
  }
  pageVM.popupError = function () {
    pageVM.popupInput(null);
    $('#delete-popup input').focus();
  }
  //show popup
  $('#delete-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
  $('#delete-popup input').focus();
});

/* Show password popup when click on .password */
$(".contents").on('click', '.body .element .password', function (event) {
  //retrive caller
  var target = event.currentTarget;
  var data = ko.dataFor(target);
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      if (pageVM.casseVM.newPassword1() === "" && pageVM.casseVM.newPassword2() === "") {
        $('#password-popup>.body>.line:nth-child(1)>input').addClass('errore');
        $('#password-popup>.body>.line:nth-child(1)>input').attr("placeholder", "password richiesta");
        $('#password-popup>.body>.line:nth-child(2)>input').addClass('errore');
        $('#password-popup>.body>.line:nth-child(2)>input').attr("placeholder", "password richiesta");
        reject();
      } else if (pageVM.casseVM.newPassword1() !== pageVM.casseVM.newPassword2()) {
        $('#password-popup>.body>.line:nth-child(1)>input').addClass('errore');
        $('#password-popup>.body>.line:nth-child(1)>input').attr("placeholder", "le password");
        $('#password-popup>.body>.line:nth-child(2)>input').addClass('errore');
        $('#password-popup>.body>.line:nth-child(2)>input').attr("placeholder", "non corrispondono");
        pageVM.casseVM.newPassword1("");
        pageVM.casseVM.newPassword2("");
        reject();
      } else {
        data.password(pageVM.casseVM.newPassword1());
        data.upload()
        .then(() => pageVM.casseVM.loadData().then(resolve))
        .catch(() => closePopup());
      }
    });
  };
  pageVM.popupReset = function () {
    pageVM.casseVM.newPassword1("");
    pageVM.casseVM.newPassword2("");
    $('#password-popup>.body>.line:nth-child(1)>input').removeClass('errore');
    $('#password-popup>.body>.line:nth-child(1)>input').attr("placeholder", "nuova password");
    $('#password-popup>.body>.line:nth-child(2)>input').removeClass('errore');
    $('#password-popup>.body>.line:nth-child(2)>input').attr("placeholder", "conferma password");
  }
  pageVM.popupError = function () {
    $('#password-popup>.body>.line:nth-child(1)>input').focus();
  }
  //show popup
  $('#password-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
  $('#password-popup>.body>.line:nth-child(1)>input').focus();
});

/* Show aggiunte popup when click on #listino.aggiunte */
$("#listino.contents").on('click', '.element .aggiunte', function (event) {
  var data = ko.dataFor(event.currentTarget);
  pageVM.listinoVM.aggiunte().forEach(function (aggiunta) {
    if (data.aggiunte().find((id) => id.id() === aggiunta.id())) {
      aggiunta.setSelected(true);
    } else {
      aggiunta.setSelected(false);
    }
  });
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      data.aggiunte([]);
      pageVM.listinoVM.aggiunte().forEach(function (aggiunta) {
        if (aggiunta.getSelected()) {
          data.aggiunte.push(new Id({id: aggiunta.id()}));
        }
      });
      if (data.id()) {
        data.upload()
        .then(() => pageVM.listinoVM.loadPietanze().then(resolve))
        .catch(() => closePopup());
      } else {
        resolve();
      }
    });
  };
  $('#aggiunte-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
  ;
});

/* Show categorie popup when click on .categorie */
$(".contents").on('click', '.element .categorie', function (event) {
  var data = ko.dataFor(event.currentTarget);
  pageVM.scontriniVM.categorie().forEach(function (categoria) {
    if (data.categorie().find((id) => id.id() === categoria.id())) {
      categoria.setSelected(true);
    } else {
      categoria.setSelected(false);
    }
  });
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      data.categorie([]);
      pageVM.scontriniVM.categorie().forEach(function (categoria) {
        if (categoria.getSelected()) {
          data.categorie.push(new Id({id: categoria.id()}));
        }
      });
      if (data.id()) {
        data.upload()
        .then(() => pageVM.scontriniVM.loadData().then(resolve))
        .catch(() => closePopup())
      } else {
        resolve();
      }
    });
  };
  $('#categorie-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
  ;
});

/* Show scontrini popup when click on .scontrini */
$(".contents").on('click', '.element .scontrini', function (event) {
  var data = ko.dataFor(event.currentTarget);
  pageVM.stampantiVM.scontrini().forEach(function (scontrino) {
    if (data.scontrini().find((id) => id.id() === scontrino.id())) {
      scontrino.setSelected(true);
    } else {
      scontrino.setSelected(false);
    }
  });
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      data.scontrini([]);
      pageVM.stampantiVM.scontrini().forEach(function (scontrino) {
        if (scontrino.getSelected()) {
          data.scontrini.push(new Id({id: scontrino.id()}));
        }
      });
      if (data.id()) {
        data.upload()
        .then(() => pageVM.stampantiVM.loadData().then(resolve))
        .catch(() => closePopup());
      } else {
        resolve();
      }
    });
  };
  $('#scontrini-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
  ;
});

/* Show scontrino layout popup when click on .layout-scontrino */
$(".contents").on('click', '.element .layout-scontrino', function (event) {
  var data = ko.dataFor(event.currentTarget);
  pageVM.scontriniVM.modScontrino().copy(data);
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      data.copy(pageVM.scontriniVM.modScontrino());
      if (data.id()) {
        data.upload()
        .then(() => pageVM.scontriniVM.loadData().then(resolve))
        .catch(() => closePopup());
      } else {
        resolve();
      }
    });
  };
  $('#layout-scontrino-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
});

/* Show aggiunte popup when click on #cassa .aggiunte */
$("#cassa.contents").on('click', '.element .aggiunte', function (event) {
  var data = ko.dataFor(event.currentTarget);
  pageVM.cassaVM.pietanzaAggiunte(data);
  data.prenotazioni().forEach(function (prenotazione) {
    prenotazione.aggiunte_disponibili([]);
    data.aggiunte().forEach(function (aggiunta) {
      var newAggiunta = new Aggiunta(aggiunta);
      if (prenotazione.aggiunte().find((id) => id.id() === aggiunta.id())) {
        newAggiunta.setSelected(true);
      } else {
        newAggiunta.setSelected(false);
      }
      prenotazione.aggiunte_disponibili.push(newAggiunta);
    });
  });
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      var promises = [];
      data.prenotazioni().forEach(function (prenotazione) {
        prenotazione.aggiunte([]);
        prenotazione.aggiunte_disponibili().forEach(function (aggiunta) {
          if (aggiunta.getSelected()) {
            prenotazione.aggiunte.push(new Id({id: aggiunta.id()}));
          }
        });
        promises.push(new Promise((resolve, reject) => {
          $.ajax({
            url: ORDER_API + "/prenotazioni/" + prenotazione.id(),
            type: "patch",
            contentType: "application/json",
            data: ko.toJSON({
              aggiunte: prenotazione.aggiunte()
            })
          }).done(resolve).fail(function (xhr) {
            ajaxError(xhr);
            reject();
          });
        }));
      });
      Promise.all(promises)
      .then(() => pageVM.cassaVM.loadPietanze().then(resolve))
      .catch(() => closePopup());
    });
  };
  $('#aggiunte-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
});

/* Show tavolo popup when click on .tavolo */
$(".contents").on('click', '.tavolo', function (event) {
  $('#tavolo-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
});

/* Close tavolo popup when click on .tavolo-number */
$("#tavolo-popup").on('click', '.tavolo-number', function (event) {
  if (event.currentTarget.textContent === 'ASPORTO') {
    pageVM.cassaVM.tavolo(0);
    pageVM.cassaVM.coperti(undefined);
    pageVM.cassaVM.asporto('1');
    $('#cassa.contents .footer .nome input').focus();
    setTimeout(closePopup, 100);
  } else {
    pageVM.cassaVM.tavolo(event.currentTarget.textContent);
    pageVM.cassaVM.asporto('0');
    pageVM.cassaVM.nome('');
    if (pageVM.cassaVM.coperti() === undefined) {
      setTimeout(function () {
        $('#tavolo-popup').css("display", "none");
        $('#coperti-popup').css("display", "flex");
      }, 200);
    } else {
      setTimeout(closePopup, 100);
    }
  }

});

/* Show coperti popup when click on .coperti */
$(".contents").on('click', '.coperti', function (event) {
  $('#coperti-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
});

/* Close coperti popup when click on .coperti-number */
$("#coperti-popup").on('click', '.coperti-number', function (event) {
  if (event.currentTarget.textContent === 'NESSUNO') {
    pageVM.cassaVM.coperti(0);
  } else {
    pageVM.cassaVM.coperti(event.currentTarget.textContent);
  }
  setTimeout(closePopup, 100);
});

/* Show reset popup when click on .reset.button */
$("#cassa.contents").on('click', '.reset.button', function (event) {
  var target = event.currentTarget;
  var data = ko.dataFor(target.parentNode.parentNode);
  pageVM.popupFunction = function () {
    return new Promise(function (resolve, reject) {
      $('#page').addClass('no-click');
      data.reset().finally(function () {
        pageVM.cassaVM.loadData().finally(function () {
          resolve();
          $('#page').removeClass('no-click');
        });
      });
    });
  };
  $('#reset-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
});


/* Show pagamenti popup when click on .satispay.button */
$("#cassa.contents").on('click', '.pagamenti.button', function (event) {
  var target = event.currentTarget;
  var data = ko.dataFor(target.parentNode.parentNode);
  data.pagamenti([]);
  data.loadPagamenti();
  pageVM.popupFunction = function(){
    return new Promise(function (resolve, reject) {
      data.pagamento = data.pagamenti().find(obj => {
        return obj.selected() === true;
      });
      resolve();
    });
  }
  $('#pagamenti-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
});

var showAlertPopup = function (message) {
  pageVM.popupAlert(message);
  pageVM.popupReset = function () {
    pageVM.popupAlert(null);
  }
  $('#alert-popup').css("display", "flex");
  $('.popup-container').css("display", "flex").hide().fadeIn();
}


/* Close popup when click on .annulla/.popup-container or .conferma */
var closePopup = function () {
  $('#page').removeClass('no-click');
  $('.popup-container').fadeOut(function () {
    if (pageVM.popupReset) {
      pageVM.popupReset();
    }
    pageVM.popupReset = null;
    pageVM.popupError = null;
    $('.popup').css("display", "none");
    $('.conferma').show();
    $('.wait').hide();
  });
};

$(".popup").on('click', '.annulla', closePopup);
$(".popup-container").on('click', function (event) {
  if (event.target === this) {
    closePopup();
  }
});

var confermaPopup = function () {
  $('#page').addClass('no-click');
  $('.conferma').hide();
  $('.wait').css("display", "flex");
  pageVM.popupFunction().then(function () {
    closePopup();
  }).catch(function () {
    if (pageVM.popupError) {
      pageVM.popupError();
    }
    $('#page').removeClass('no-click');
    $('.conferma').show();
    $('.wait').hide();
  });
};
$(".popup").on('click', '.conferma', function () {
  confermaPopup();
});
$("#delete-popup input").on('keypress', function (event) {
  if (event.key === 'Enter') {
    confermaPopup();
  }
});
$("#password-popup input").on('keypress', function (event) {
  if (event.key === 'Enter') {
    confermaPopup();
  }
});
