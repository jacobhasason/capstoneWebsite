document.addEventListener("DOMContentLoaded", () => {

    function setupDropZone(zoneId) {
        const zone = document.getElementById(zoneId);
        if (!zone) return;

        const input = zone.querySelector("input[type='file']");
        const message = zone.querySelector(".drop-message");
        const removeBtn = zone.querySelector(".remove-file");

        if (!removeBtn) return; // safety check

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

});