class AggiunteViewModel extends APIViewModel {

    constructor(pageVM){
        var newAggiunta = { ordering: 1 };
        super(pageVM, CORE_API + "/aggiunte", "aggiunte", "Aggiunte", "Aggiunta", newAggiunta, true);
    }
}
