

let activeSlider = 0;
const count = $('.hp-slider').length;
if (count > 1) {
    $('.hp-slider').first().show()
    setInterval(function () {
        $('.hp-slider').eq(activeSlider).fadeOut();
        activeSlider++;
        if (activeSlider === count) {
            activeSlider = 0;
        }

        $('.hp-slider').eq(activeSlider).fadeIn();
    }, 5000);
}