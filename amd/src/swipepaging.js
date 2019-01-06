define(['jquery', 'theme_stardust/slick'], function ($, slick) {
  'use strict';

  return {
    init: function () {

      $('.qn_buttons').slick({
        infinite: false,
        slidesToShow: 5,
        slidesToScroll: 1,
        dots: true,
        swipeToSlide: true
      });

    }
  }
});
