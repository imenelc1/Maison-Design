//l'animation et le changement de slide de les categories
document.addEventListener('DOMContentLoaded', function() {
    const slidesWrapper = document.querySelector('.slides-wrapper');
    const slides = document.querySelectorAll('.slide');
    const descriptions = document.querySelectorAll('.category-description');
    const prevButton = document.querySelector('.prev-arrow');
    const nextButton = document.querySelector('.next-arrow');
    let currentIndex = 0;
    const slideCount = slides.length;

    function updateSlider() {
        const slideWidth = slides[0].clientWidth + 20; 
        slidesWrapper.style.transform = `translateX(${-currentIndex * slideWidth}px)`;
        slidesWrapper.style.transition = 'transform 0.5s ease-in-out';

        slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === currentIndex);
        });

        descriptions.forEach((desc, index) => {
            desc.classList.toggle('active', index === currentIndex);
        });
    }
    function goToSlide(index) {
        if (index !== currentIndex) {  
            currentIndex = (index + slideCount) % slideCount;
            updateSlider();
        }
    }

    prevButton.addEventListener('click', () => goToSlide(currentIndex - 1));
    nextButton.addEventListener('click', () => goToSlide(currentIndex + 1));
//chaqur 5 secondes le slider change de slide
    let autoSlideInterval = setInterval(() => goToSlide(currentIndex + 1), 5000);
//Lorsque la souris est sur le slider l’animation s'arrête
    document.querySelector('.categories-slider').addEventListener('mouseenter', () => {
        clearInterval(autoSlideInterval);
    });
//Lorsque la souris quitte le slider l'animation reprend
    document.querySelector('.categories-slider').addEventListener('mouseleave', () => {
        autoSlideInterval = setInterval(() => goToSlide(currentIndex + 1), 5000);
    });

   slides.forEach((slide, index) => {
        slide.addEventListener('click', () => goToSlide(index));
    });

    updateSlider();
});


   