class SerateViewModel extends APIViewModel {

    constructor(pageVM) {
        var date = formatDate(new Date, "yyyy-mm-dd hh:00:00");
        var newSerata = {
            inizio: date,
            fine: date,
            attiva: "0"
        };
        super(pageVM, CORE_API + "/serate", "serate", "Serate", "Serata", newSerata, true, "inizioTimestamp");
    }

    addElement() {
        var date = formatDate(new Date, "yyyy-mm-dd hh:00:00");
        this.newElementData = {
            inizio: date,
            fine: date,
            attiva: "0"
        };
        super.addElement();
    }
}
