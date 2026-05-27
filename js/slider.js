// слайдер товаров на главной

document.addEventListener('DOMContentLoaded', () => {
    const slider  = document.querySelector('.slider-wrapper');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');

    if (!slider || !prevBtn || !nextBtn) return;

    const scrollAmount = 257; // ширина карточки + отступ

    prevBtn.addEventListener('click', () => {
        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });

    nextBtn.addEventListener('click', () => {
        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });

    function checkButtons() {
        prevBtn.style.opacity = slider.scrollLeft <= 10 ? '0.5' : '1';
        nextBtn.style.opacity = (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) ? '0.5' : '1';
    }

    slider.addEventListener('scroll', checkButtons);
    window.addEventListener('resize', checkButtons);
    checkButtons();
});
