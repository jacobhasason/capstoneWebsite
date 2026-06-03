/**
 * Fetches listings matching the selected topics from local PHP bridge
 * and updates all dynamic related listing dropdown menus.
 */
async function updateRelatedListings() {
    // 1. Get all checked topic IDs from real-topic checkboxes
    const checkedBoxes = document.querySelectorAll(".real-topic:checked");
    
    // Ensure values are parsed as integers to cleanly match PHP intval filter
    const selectedTopicIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value, 10));

    // Find all the dropdowns on the page
    const dropdowns = document.querySelectorAll(".related-listing-select");

    // If no topics are checked, reset the dropdowns and stop
    if (selectedTopicIds.length === 0) {
        dropdowns.forEach(select => {
            select.innerHTML = '<option value="">Select a topic first</option>';
        });
        return;
    }

    try {
        // 2. Fetch data from PHP handler instead of directly hitting Supabase REST

        const response = await fetch("getRelatedListings.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
       
            body: JSON.stringify({ topics: selectedTopicIds })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const listings = await response.json();

        // 3. Update each dropdown while preserving what the user already selected
        dropdowns.forEach(select => {
            const currentSelection = select.value; // Save what they picked

            // Reset with default option
            select.innerHTML = '<option value="">Select Related Listing</option>';

            // Populate fresh matching data returned from SQL database query
            listings.forEach(item => {
                const option = document.createElement("option");
                
                // Matches the column case output l."listingID" and l.title from  query
                option.value = item.listingID; 
                option.textContent = item.title;

                // Re-select their option if it still exists in the filtered results
                if (item.listingID == currentSelection) {
                    option.selected = true;
                }

                select.appendChild(option);
            });
        });

    } catch (error) {
        console.error("Failed to populate related listings dropdown:", error);
    }
}