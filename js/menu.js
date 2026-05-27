// мобильное меню — бургер

document.addEventListener('DOMContentLoaded', () => {
    const burgerBtn  = document.querySelector('.burger-btn');
    const mobileMenu = document.getElementById('mobileMenu');
    const navOverlay = document.getElementById('navOverlay');

    if (!burgerBtn || !mobileMenu) return;

    function openMenu() {
        burgerBtn.classList.add('active');
        mobileMenu.classList.add('active');
        if (navOverlay) navOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        burgerBtn.classList.remove('active');
        mobileMenu.classList.remove('active');
        if (navOverlay) navOverlay.classList.remove('active');
        document.body.style.overflow = '';
        document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
    }

    burgerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileMenu.classList.contains('active') ? closeMenu() : openMenu();
    });

    if (navOverlay) navOverlay.addEventListener('click', closeMenu);

    // выпадающие меню на мобильных
    document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 428) {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = this.classList.contains('open');
                document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
                if (!isOpen) this.classList.add('open');
            }
        });
    });

    document.querySelectorAll('.dropdown-menu a').forEach(link => {
        link.addEventListener('click', () => setTimeout(closeMenu, 150));
    });

    document.querySelectorAll('.mobile-menu .nav-link:not(.nav-dropdown .nav-link)').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 428) closeMenu();
    });

    // свайп для закрытия
    let touchStartX = 0;
    mobileMenu.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    mobileMenu.addEventListener('touchend', (e) => {
        if (touchStartX - e.changedTouches[0].clientX > 80) closeMenu();
    }, { passive: true });
});
