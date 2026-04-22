document.querySelectorAll('.dropdown li a').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault(); // Stop page from jumping to top
        
        const checkbox = this.querySelector('.checkbox-box');
        const parentList = this.closest('.dropdown');
        const parentCategory = this.closest('.main-nav > li').querySelector('a').innerText;

        // SPECIFIC RULE: For "Date" (Most Recent vs Oldest)
        if (parentCategory === "Date") {
            // Find all checkboxes in the Date dropdown and uncheck them
            parentList.querySelectorAll('.checkbox-box').forEach(box => {
                box.classList.remove('checked');
            });
            // Check the one that was clicked
            checkbox.classList.add('checked');
        } 
        
        // GENERAL RULE: For Topic and Medium (Toggle on/off)
        else {
            checkbox.classList.toggle('checked');
        }
    });
});
