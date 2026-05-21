/**
 * Product slider — used on index.html and other pages with .slider-wrapper
 */
document.addEventListener('DOMContentLoaded', () => {
    const slider  = document.querySelector('.slider-wrapper');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');

    if (!slider || !prevBtn || !nextBtn) return;

    const scrollAmount = 257; // card width + gap

    prevBtn.addEventListener('click', () => {
        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });

    nextBtn.addEventListener('click', () => {
        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });

    const checkButtons = () => {
        prevBtn.style.opacity = slider.scrollLeft <= 10 ? '0.5' : '1';
        nextBtn.style.opacity = (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) ? '0.5' : '1';
    };

    slider.addEventListener('scroll', checkButtons);
    window.addEventListener('resize', checkButtons);
    checkButtons();
});
