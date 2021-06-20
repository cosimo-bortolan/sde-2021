class CategorieViewModel extends APIViewModel {

    constructor(pageVM){
        var newCategoria = { gruppo: "0", ordering: 1 };
        super(pageVM, CORE_API + "/categorie", "categorie", "Categorie", "Categoria", newCategoria, true);
        this.gruppi = ["CUCINA", "BAR"];
    }
}
