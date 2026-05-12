async function loadScholar() {

    try {
        const res = await fetch("/capstoneWebsite/Scraper/scrape.php");

        const data = await res.json();

        console.log(data);

        let html = "";

        data.results.forEach(item => {
            html += `
                <div class="paper">
                    <h3>${item.title}</h3>
                </div>
            `;
        });

        document.getElementById("results").innerHTML = html;

    } catch (err) {
        console.error("Error:", err);
    }
}