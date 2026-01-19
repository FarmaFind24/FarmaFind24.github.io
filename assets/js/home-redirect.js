
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.getElementById('mainSearchForm');

    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            // Impediamo l'invio standard per decidere noi la destinazione
            e.preventDefault();

            const query = document.getElementById('obj-name').value;
            const filter = document.querySelector('input[name="type"]:checked').value;

            let destination = "";

            if (filter === "farmacia") {
                destination = "farm-search.php";
            } else {
                destination = "med-search.php";
            }

            // Costruiamo l'URL con il parametro q
            // Esempio: farm-search.php?q=nomefarmacia
            window.location.href = destination + "?q=" + encodeURIComponent(query);
        });
    }
});
