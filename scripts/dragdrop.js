document.addEventListener("DOMContentLoaded", () => {

    function setupDropZone(zoneId) {
        const zone = document.getElementById(zoneId);
        if (!zone)
            return;
        const input = zone.querySelector("input[type='file']");
        const message = zone.querySelector(".drop-message");
        const removeBtn = zone.querySelector(".remove-file");
        if (!removeBtn)
            return; // safety check

        function showFile(fileName) {
            message.textContent = fileName;
            removeBtn.style.display = "block";
        }

        function resetZone() {
            input.value = "";
            removeBtn.style.display = "none";
            message.innerHTML = zoneId === "drop-zone-thumb"
                    ? "Drag & Drop (jpeg/png)<br>Icon"
                    : "Drag & Drop (pdf/mp3/mp4/wav)<br>File";
        }

// click opens file picker
        zone.addEventListener("click", () => input.click());
        // drag over
        zone.addEventListener("dragover", (e) => {
            e.preventDefault();
            zone.classList.add("drag-over");
        });
        zone.addEventListener("dragleave", () => {
            zone.classList.remove("drag-over");
        });
        // drop file
        zone.addEventListener("drop", (e) => {
            e.preventDefault();
            zone.classList.remove("drag-over");
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                showFile(files[0].name);
            }
        });
        // manual select
        input.addEventListener("change", () => {
            if (input.files.length > 0) {
                showFile(input.files[0].name);
            }
        });
        // remove button
        removeBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            resetZone();
        });
    }

    setupDropZone("drop-zone-thumb");
    setupDropZone("drop-zone-icon");

    document.addEventListener("input", async function (e) {
        if (!e.target.classList.contains("listing-search"))
            return;

        const searchInput = e.target;

        const wrapper = searchInput.closest(".related-listing");
        if (!wrapper)
            return;

        const hiddenID = wrapper.querySelector('input[type="hidden"]');
        const resultsBox = wrapper.querySelector(".listing-results");
        const query = searchInput.value.trim();
        hiddenID.value = "";
        resultsBox.innerHTML = "";
        if (query.length < 2)
            return;
        console.log("Searching for:", query);
        console.log("Fetch URL:", `SearchListings.php?q=${encodeURIComponent(query)}`);

        const response = await fetch(`SearchListings.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        console.log("Search response:", data);

        if (!Array.isArray(data)) {
            resultsBox.innerHTML = `<div class="listing-result no-result">Search error</div>`;
            return;
        }

        if (data.length === 0) {
            resultsBox.innerHTML = `<div class="listing-result no-result">No matches found</div>`;
            return;
        }

        data.forEach(listing => {
            const option = document.createElement("div");
            option.className = "listing-result";
            option.textContent = `${listing.title} — ${listing.author ?? "Unknown author"}`;

            option.addEventListener("click", function () {
                searchInput.value = listing.title;
                hiddenID.value = listing.id;
                resultsBox.innerHTML = "";
            });

            resultsBox.appendChild(option);
        });
    });

    document.addEventListener("click", function (e) {
        if (!e.target.closest(".related-listing")) {
            document.querySelectorAll(".listing-results").forEach(box => {
                box.innerHTML = "";
            });
        }
    });


});