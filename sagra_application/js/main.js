/* global MAX_MOBILE_SIZE */

var pageVM = new PageVM();
ko.applyBindings(pageVM);

/* Show page on load */
$(window).on('load', function () {
  $("#page").css("display", "flex");
});

/* Open/close navbar when click on menu logo */
$("#navbar>.title>.item>.image").on('click', function () {
  var page = $("#page");
  if (page.width() <= MAX_MOBILE_SIZE) {
    page.toggleClass("open-mobile");
  } else {
    page.toggleClass("close-desktop");
  }
});

/* Close navbar when click on item */
$("#navbar .item:not(.title>.item)").on('click', function () {
  $("#page").removeClass("open-mobile");
});

/* Open element when click on .expand */
$('.contents').on('click', '.expand', function (event) {
  var currentElement = $(event.currentTarget.parentNode.parentNode);
  var image = $(event.currentTarget.firstElementChild);

  var otherElements = $('.element');
  var otherImages = $('.rotate');
  if (!currentElement.hasClass('open')) {
    otherImages.removeClass('rotate');
    otherElements.removeClass('open');
    currentElement.addClass('open');
    image.addClass('rotate');
  } else {
    otherElements.removeClass('open');
    image.removeClass('rotate');
  }
});

var ajaxError = function (xhr, err1 = null, err2 = null, err3 = null, err4 = null, err5 = null, err6 = null, err7 = null) {
  if (xhr.readyState === 4) {
    switch (xhr.status) {
      case 400:
      if (err1 !== null) {
        err1();
      } else {
        var response = xhr.responseJSON;
        switch (response.error) {
          case 7:
          alert("Valore mancante per il campo " + response.details.field);
          break;
          case 8:
          alert("Il valore immesso per il campo " + response.details.field + " non è corretto");
          break;
          case 9:
          var msg = "I valori inseriti non rispettano il seguente vincolo: ";
          switch (response.details.constraint) {
            case 0:
            msg += "la data di fine non può essere minore o uguale alla data di inizio";
            break;
            case 1:
            msg += "le serate non possono sovrapporsi";
            break;
            case 2:
            msg += "il campo ricevuta e l'elenco delle categorie non possono essere entrambi vuoti";
            break;
            case 3:
            msg += "il campo asporto e il campo tavolo non possono essere entrambi vuoti";
            break;
            case 4:
            msg += "il campo asporto e il campo tavolo non possono essere entrambi valorizzati";
            break;
            case 5:
            msg += "il campo nome non può essere vuoto se il campo asporto è valorizzato";
            break;
          }
          alert(msg);
          break;
          case 10:
          alert("Valore duplicato per il campo " + response.details.duplicate_field);
          break;
          case 20:
          alert("Pagamento fallito. Ordine non confermato");
          break;
        }
      }
      break;
      case 401:
      if (err2 !== null) {
        err2();
      } else {
        pageVM.app.refresh();
      }
      break;
      case 403:
      if (err3 !== null) {
        err3();
      } else {
        alert("Permessi non sufficienti per eseguire l'azione corrente.");
        pageVM.app.refresh();
      }
      break;
      case 404:
      if (err4 !== null) {
        err4();
      } else {
        pageVM.app.refresh();
      }
      break;
      case 500:
      if (err5 !== null) {
        err5();
      } else {
        alert("Il server non può gestire la richiesta a causa di un errore interno.");
        pageVM.app.refresh();
      }
      break;
      default:
      if (err6 !== null) {
        err6();
      } else {
        alert("Errore imprevisto. Impossibile procedere.");
        pageVM.app.refresh();
      }
    }
  } else {
    if (err7 !== null) {
      err7();
    } else {
      pageVM.app.refresh();
    }
  }
};
