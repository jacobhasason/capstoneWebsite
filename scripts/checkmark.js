const filters = {
    date: null,
    topic: [],
    medium: []
};

document.querySelectorAll('.dropdown li a').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();

        const checkbox = this.querySelector('.checkbox-box');
        const parentList = this.closest('.dropdown');

        const parentCategory = this.closest('.main-nav > li')
            .querySelector('a')
            .innerText.trim();

        // clean value from clicked item
        let value = this.textContent.trim().toLowerCase();

        // ----------------------------
        // NORMALIZE MEDIUM VALUES
        // ----------------------------
        if (parentCategory === "Medium") {
            if (value === "presentations") value = "presentation";
            if (value === "videos") value = "video";
            if (value === "podcasts") value = "podcast";
            if (value === "tutorials") value = "tutorial";
            if (value === "papers") value = "paper";
        }

        // ----------------------------
        // DATE (single selection)
        // ----------------------------
        if (parentCategory === "Date") {

            parentList.querySelectorAll('.checkbox-box')
                .forEach(box => box.classList.remove('checked'));

            checkbox.classList.add('checked');

            filters.date = this.textContent.trim(); // keep original label for display logic
        }

        // ----------------------------
        // TOPIC + MEDIUM (multi selection)
        // ----------------------------
        else {

            checkbox.classList.toggle('checked');

            const arr = parentCategory === "Topic"
                ? filters.topic
                : filters.medium;

            const index = arr.indexOf(value);

            if (index === -1) {
                arr.push(value);
            } else {
                arr.splice(index, 1);
            }
        }

        console.log(filters);

        fetchListings();
    });
});

// ----------------------------
// AJAX FETCH
// ----------------------------
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
        document.querySelector(".listings-container").innerHTML = html;
    })
    .catch(err => console.error(err));
}