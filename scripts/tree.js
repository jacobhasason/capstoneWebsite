document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".tree-toggle").forEach(button => {

        button.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            const parentLi = button.closest("li");
            if (!parentLi) return;

            const submenu = parentLi.querySelector("ul");
            if (!submenu) return;

            submenu.classList.toggle("hidden");
        });

    });

});
