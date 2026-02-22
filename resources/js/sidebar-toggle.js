document.addEventListener("DOMContentLoaded", function() {

    const sidebarToggle = document.getElementById('sidebar-toggle');
    const wrapper = document.querySelector('.wrapper');
    const sidebar = document.querySelector('.sidebar');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const mobileQuery = window.matchMedia('(max-width: 849.98px)');

    if (sidebarToggle && wrapper) {

        const isMobile = () => mobileQuery.matches;
        const closeSidebar = () => wrapper.classList.remove('sidebar-toggled');

        sidebarToggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            wrapper.classList.toggle('sidebar-toggled');
        });

        document.addEventListener('click', function(event) {
            if (!isMobile() || !wrapper.classList.contains('sidebar-toggled')) {
                return;
            }

            const clickInsideSidebar = sidebar ? sidebar.contains(event.target) : false;
            const clickOnToggle = sidebarToggle.contains(event.target);

            if (!clickInsideSidebar && !clickOnToggle) {
                closeSidebar();
            }
        });

        sidebarLinks.forEach((link) => {
            link.addEventListener('click', function() {
                if (isMobile()) {
                    closeSidebar();
                }
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        mobileQuery.addEventListener('change', function(event) {
            if (!event.matches) {
                closeSidebar();
            }
        });
        
    }

});