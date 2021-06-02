(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.validator = {

    attach: function (context, settings) {

      $("#edit-created-1").change(function () {
        var startDate = $('#edit-created').val();
        var endDate = $('#edit-created-1').val();

        if (startDate) {
          if ((Date.parse(startDate) > Date.parse(endDate))) {
            alert("End date should be greater than Start date");
            $('#edit-created').val("");
            $('#edit-created-1').val("");
            $("#edit-submit-scheduler").prop('disabled', true);
          }
          if ((Date.parse(startDate) <= Date.parse(endDate))) {
            $("#edit-submit-scheduler").prop('disabled', false);
          }
        }
      });
      $('#edit-created').change(function () {
        var startDate = $('#edit-created').val();
        var endDate = $('#edit-created-1').val();

        if (endDate) {
          if ((Date.parse(startDate) > Date.parse(endDate))) {
            alert("End date should be greater than Start date");
            $('#edit-created').val("");
            $('#edit-created-1').val("");
            $("#edit-submit-scheduler").prop('disabled', true);
          }
          if ((Date.parse(startDate) <= Date.parse(endDate))) {
            $("#edit-submit-kpi").prop('disabled', false);
          }
        }
      });
      $("#edit-submit-kpi").click(function () {
        var startDate = $('#edit-created').val();
        var endDate = $('#edit-created-1').val();

        if (!startDate && endDate){
          $('#edit-created').val("");
          $('#edit-created-1').val("");
          // console.log('startdate');
          return alert("Select the start date");;
        }

        if (!endDate && startDate){
          $('#edit-created').val("");
          $('#edit-created-1').val("");
          // console.log('enddate');
          return alert("Select the end date");;
        }
      });
    }
  };

  Drupal.behaviors.mask = {
    attach: function(context) {

      $(function() {
        // datepicker
        $.datepicker.setDefaults( $.datepicker.regional[ "pt" ] );
        $(".datepicker").datepicker({
          dateFormat: 'dd/mm/yy',
          dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
          dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
          dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
          monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
          monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
          nextText: 'Próximo',
          prevText: 'Anterior'
        });
        // mascara
        $('input[class*="mask"]').each(function(){
          if($(this).attr('mask')) {
            $(this).mask($(this).attr('mask'));
          }
        });
      });
    }
  };

})(jQuery, Drupal);
