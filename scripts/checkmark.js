// Central State Object
const filters = {
    date: null,
    topic: [],
    medium: [],
    limit: 10
};

/* GLOBAL EVENT DELEGATION LISTENER */
document.addEventListener("click", function (e) {
    // Find if the click happened directly on or inside a link with the .filter-item class
    const item = e.target.closest(".filter-item");
    if (!item) return; // Ignore clicks on tree layout toggles, arrows, or general links

    e.preventDefault();

    // Pull values directly from HTML attributes
    const filterType = item.getAttribute("data-type");   // "date", "topic", or "medium"
    const filterValue = item.getAttribute("data-value"); // e.g., "most_recent", "1", "book"
    const box = item.querySelector(".checkbox-box");

    if (!box || !filterType) return;

    // Reset pagination viewing ceiling on any state change
    filters.limit = 10;

    // 1. HANDLE DATE SELECTION (Mutual Exclusion Rule)
    if (filterType === "date") {
        const isAlreadyChecked = box.classList.contains("checked");

        // Clear layout check visual indicator classes ONLY from items inside the Date column drop-down
        document.querySelectorAll('.filter-item[data-type="date"] .checkbox-box').forEach(b => {
            b.classList.remove("checked");
        });

        if (isAlreadyChecked) {
            // Unchecking an active choice fully clears the sorting restriction parameter
            filters.date = null;
        } else {
            // Check the current element box container and update sorting variables explicitly
            box.classList.add("checked");
            
            if (filterValue === "most_recent") {
                filters.date = "Most Recent";
            } else if (filterValue === "oldest") {
                filters.date = "Oldest";
            }
        }

    // 2. MULTI-SELECT ARRAY HANDLING (Topics & Mediums)
    } else if (filterType === "topic" || filterType === "medium") {
        const isAlreadyChecked = box.classList.contains("checked");
        
        // Use standard layout toggles to change individual visibility instantly
        box.classList.toggle("checked");

        const arr = filterType === "topic" ? filters.topic : filters.medium;
        const index = arr.indexOf(filterValue);

        if (isAlreadyChecked) {
            // Yeet item cleanly out of arrays if it is being unchecked
            if (index > -1) arr.splice(index, 1);
        } else {
            // Push search value tracking details to array objects if its missing
            if (index === -1) arr.push(filterValue);
        }
    }

    // Hand execution pipeline off to backend queries engine
    fetchListings(false);
});

/* FETCH LISTINGS ENGINE */
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
            const oldBtnContainer = container.querySelector(".show-more-container");
            if (oldBtnContainer) oldBtnContainer.remove();
            container.insertAdjacentHTML("beforeend", html);
        } else {
            container.innerHTML = html;
        }
    })
    .catch(console.error);
}

/* PAGINATION INTERCEPT HANDLER */
document.addEventListener("click", (e) => {
    if (e.target.id === "show-more-btn") {
        e.preventDefault();
        filters.limit += 10;
        fetchListings(true);
    }
});