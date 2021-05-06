(function ($) {

  Drupal.behaviors.mask = {
    attach: function () {
      $('.not-logged-in .main-container').removeClass('container-fluid').addClass('container');
    }
  }

  Drupal.behaviors.bannerHome = {
    attach: function () {
      $('.owl-carousel').owlCarousel({
        loop: true,
        margin:10,
        nav:true,
        items:1,
        autoplay:true,
        autoplayTimeout:4000,
        autoplayHoverPause:true
      })
    }
  }

})(jQuery);
