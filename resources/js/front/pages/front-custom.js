document.addEventListener('DOMContentLoaded', loadFrontData)

function loadFrontData () {
    frontAlertInitialize()
    loadFrontSlider()
    loadScroll()

}

function frontAlertInitialize () {
    $('.alert').delay(5000).slideUp(300)
}

function loadFrontSlider () {

    const $servicesSection = $('.services-section')
    const $servicesCarousel = $servicesSection.find('.services-carousel')
    const $servicesPrev = $servicesSection.find('.services-carousel-control--prev')
    const $servicesNext = $servicesSection.find('.services-carousel-control--next')

    $servicesCarousel.slick({
        dots: false,
        arrows: true,
        centerPadding: '0',
        slidesToShow: 1,
        slidesToScroll: 1,
        prevArrow: $servicesPrev,
        nextArrow: $servicesNext,
    })
    $('.testimonial-carousel').slick({
        dots: true,
        centerPadding: '0',
        slidesToShow: 1,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 1,
                    arrows: false,
                },
            },
        ],
    })
}
function loadScroll () {
    $(window).scroll(function(){
        var sticky = $('.header'),
            scroll = $(window).scrollTop();
        if (scroll >= 50) sticky.addClass('fixed');
        else sticky.removeClass('fixed');
    });
}
