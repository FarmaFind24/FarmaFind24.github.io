
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.getElementById('mainSearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const query = document.getElementById('obj-name').value;
            const filter = document.querySelector('input[name="type"]:checked').value;

            let destination = "";

            if (filter === "farmacia") {
                destination = "farm-search.php";
            } else {
                destination = "med-search.php";
            }
            window.location.href = destination + "?q=" + encodeURIComponent(query);
        });
    }
});
