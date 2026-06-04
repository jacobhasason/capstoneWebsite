const filters = {
    date: null,
    topic: [],
    medium: [],
    limit: 10
};

/* FILTER CLICK LOGIC */
document.querySelectorAll(".checkbox-box").forEach(box => {
    const item = box.closest("a");
    if (!item) return;

    item.addEventListener("click", function (e) {
        e.preventDefault();

        // Use the explicit HTML data attributes
        const value = this.getAttribute("data-value") || this.textContent.trim().toLowerCase();
        const filterType = this.getAttribute("data-type"); // matches: "date", "topic", or "medium"

        // Every time a filter category is changed, reset page limit back to 10
        filters.limit = 10;

        // 1. HANDLE DATE FILTERS (Radio-style behavior)
        if (filterType === "date") {
            const isAlreadyChecked = box.classList.contains("checked");

            // Only clear checkboxes inside this specific dropdown
            this.closest('.dropdown')
                ?.querySelectorAll(".checkbox-box")
                .forEach(b => b.classList.remove("checked"));

            if (isAlreadyChecked) {
                box.classList.remove("checked");
                filters.date = null;
            } else {
                box.classList.add("checked");
                filters.date = this.textContent.trim();
            }

        // 2. HANDLE TOPIC & MEDIUM FILTERS (Multi-select checkbox behavior)
        } else if (filterType === "topic" || filterType === "medium") {
            box.classList.toggle("checked");

            const arr = filterType === "topic" ? filters.topic : filters.medium;
            const index = arr.indexOf(value);

            if (index === -1) {
                arr.push(value); // Add filter item
            } else {
                arr.splice(index, 1); // Remove filter item
            }
        }

        // Run standard fetch list (false indicates it will overwrite, resetting the view)
        fetchListings(false);
    });
});

/* FETCH LISTINGS */
function fetchListings(isAppend = false) {
    fetch("getListing.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(filters)
    })
    .then(res => res.text())
    .then(html => {
        const container = document.querySelector(".listings-container");
        if (!container) return;

        if (isAppend) {
            // Drop the old button out of the way before inserting new contents
            const oldBtnContainer = container.querySelector(".show-more-container");
            if (oldBtnContainer) oldBtnContainer.remove();

            // Append new items safely to the bottom of the existing items
            container.insertAdjacentHTML("beforeend", html);
        } else {
            // Overwrite cleanly when completely swapping filters
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
        
        // Pass true to signify this is an append operation
        fetchListings(true);
    }
});
