const filters = {

    date: null,
    topic: [],
    medium: [],
    limit: 10

};


/* FILTER CLICK LOGIC */
document.querySelectorAll(".checkbox-box").forEach(box => {

    const item = box.closest("a");
    if (!item)
        return;

    item.addEventListener("click", function (e) {

        e.preventDefault();

        const value =
                this.textContent.trim().toLowerCase();

        const parentCategory =
                this.closest('.main-nav > li')
                ?.querySelector(':scope > a')
                ?.innerText.trim();

        if (parentCategory === "Date") {

            document.querySelectorAll(".checkbox-box")
                    .forEach(b => b.classList.remove("checked"));

            box.classList.add("checked");

            filters.date = this.textContent.trim();

        } else {

            box.classList.toggle("checked");

            const arr =
                    parentCategory === "Topic"
                    ? filters.topic
                    : filters.medium;

            const index = arr.indexOf(value);

            if (index === -1) {
                arr.push(value);
            } else {
                arr.splice(index, 1);
            }
        }

        fetchListings();

    });

});


/* FETCH LISTINGS */
function fetchListings() {

    fetch("getListing.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify(filters)

    })

            .then(res => res.text())

            .then(html => {

                const container =
                        document.querySelector(".listings-container");

                if (container) {
                    container.innerHTML = html;
                }

            })

            .catch(console.error);

}


/* SHOW MORE */
document.addEventListener("click", (e) => {

    if (e.target.id === "show-more-btn") {

        e.preventDefault();

        filters.limit += 10;

        fetchListings();
    }

});