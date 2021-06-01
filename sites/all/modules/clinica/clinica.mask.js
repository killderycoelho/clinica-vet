(function($) {
  Drupal.behaviors.clinica_mask = {
    attach: function(context) {
      $('input[name*="date_filter"]').each(function(obj) {
        $(this).mask('99/99/9999');
      });

      $('input[class*="mask"]').each(function(obj){
        $(this).mask($(this).attr('mask'));
      });
    }
  };
})(jQuery);
