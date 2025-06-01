document.addEventListener("DOMContentLoaded", () => {
    // Gestion du menu mobile
    const openMenuBtn = document.getElementById("open-menu");
    const closeMenuBtn = document.getElementById("close-menu");
    const mobileMenu = document.getElementById("mobile-menu");
    const menuOverlay = document.getElementById("menu-overlay");

    if (openMenuBtn && closeMenuBtn && mobileMenu) {
        openMenuBtn.addEventListener("click", () => {
            mobileMenu.style.right = "0";
            document.body.style.overflow = "hidden";
            if (menuOverlay) {
                menuOverlay.classList.remove("opacity-0", "invisible");
                menuOverlay.classList.add("opacity-100", "visible");
            }
        });

        const closeMobileMenu = () => {
            mobileMenu.style.right = "-280px";
            document.body.style.overflow = "auto";
            if (menuOverlay) {
                menuOverlay.classList.add("opacity-0", "invisible");
                menuOverlay.classList.remove("opacity-100", "visible");
            }
        };

        closeMenuBtn.addEventListener("click", closeMobileMenu);

        if (menuOverlay) {
            menuOverlay.addEventListener("click", closeMobileMenu);
        }
    }

    // Gestion du sous-menu catégories en mobile
    const mobileDropdown = document.querySelector(".mobile-dropdown > button");
    const mobileSubmenu = document.getElementById("mobile-submenu");
    const chevronMobile = document.getElementById("chevron-mobile");

    if (mobileDropdown && mobileSubmenu) {
        mobileDropdown.addEventListener("click", (e) => {
            e.preventDefault();

            if (mobileSubmenu.style.maxHeight === "300px") {
                mobileSubmenu.style.maxHeight = "0";
                if (chevronMobile) {
                    chevronMobile.style.transform = "rotate(0deg)";
                }
            } else {
                mobileSubmenu.style.maxHeight = "300px";
                if (chevronMobile) {
                    chevronMobile.style.transform = "rotate(180deg)";
                }
            }
        });
    }

    // Gestion de la recherche
    const searchToggle = document.getElementById("search-toggle");
    const searchDropdown = document.getElementById("search-dropdown");

    if (searchToggle && searchDropdown) {
        searchToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            if (searchDropdown.classList.contains("opacity-0")) {
                searchDropdown.classList.remove("opacity-0", "invisible", "translate-y-2");
                searchDropdown.classList.add("opacity-100", "visible", "translate-y-0");
            } else {
                searchDropdown.classList.add("opacity-0", "invisible", "translate-y-2");
                searchDropdown.classList.remove("opacity-100", "visible", "translate-y-0");
            }
        });

        document.addEventListener("click", (e) => {
            if (!searchToggle.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.add("opacity-0", "invisible", "translate-y-2");
                searchDropdown.classList.remove("opacity-100", "visible", "translate-y-0");
            }
        });
    }
});

