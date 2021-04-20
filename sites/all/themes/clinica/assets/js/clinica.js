(function ($) {

  Drupal.behaviors.mask = {
    attach: function () {
      $('.not-logged-in .main-container').removeClass('container-fluid').addClass('container');
    }
  }

})(jQuery);
