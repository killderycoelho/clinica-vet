<?php
$variables = array(
  'tags' => array(),
  'element' => 0,
  'parameters' => array(),
  'quantity' => 5,
);

$rowTotal = array(array(
  array('data' => 'Total', 'header' => 1, 'width' => '80'),
  array('data' => $dados['total'], 'header' => 1),
));

if ($toDownload !== true) {
  avanz_relatorios_include_css();
  drupal_add_js(libraries_get_path('maskedinput') . '/jquery.maskedinput-1.3.js');
  drupal_add_js(drupal_get_path('module', 'avanz') . "/avanz.mask.js");

  drupal_add_js(drupal_get_path('module', 'jquery_update') . "/replace/ui/ui/minified/jquery-ui.min.js");
  drupal_add_css(drupal_get_path('module', 'jquery_update') . "/replace/ui/themes/base/minified/jquery-ui.min.css");

  ?>
  <div class="view">
    <div class="view-filters">
      <form action="" method="get" id="form_busca" accept-charset="UTF-8">
        <div class="container-report clearfix">
          <div class="row">
            <div class="col-sm-5 form-item">
              <!-- <label>Nome</label> -->
              <input class="form-control form-text" type="text" name="nome" placeholder="Nome"
                     value="<?php print isset($_GET['nome']) ? $_GET['nome'] : ''; ?>" size="25" maxlength="128">
            </div>

            <div class="col-sm-3 form-item css-field-data">
              <div class="data data_inicio">
                <input mask="99/99/9999" class="mask datepicker form-control form-text" type="text" id="data_ini"
                       name="data_ini" value="<?php print isset($_GET['data_ini']) ? $_GET['data_ini'] : null; ?>"
                       size="10" maxlength="10" placeholder="Data início">
              </div>
            </div>
            <div class="col-sm-3 form-item css-field-data">
              <div class="data data_fim">
                <input mask="99/99/9999" class="mask datepicker form-control form-text" type="text" id="data_fim"
                       name="data_fim" value="<?php print isset($_GET['data_fim']) ? $_GET['data_fim'] : null; ?>"
                       size="10" maxlength="10"  placeholder="Data final">
              </div>
            </div>
            <div class="col-sm-3 form-item views-submit-button">
              <?php
              $attributes_submit = [
                'id' => 'edit-submit-comunicado',
                'class' => ['btn', 'btn-primary'],
                'value' => 'Buscar',
              ];
              print theme_button([
                'element' => [
                  '#attributes' => $attributes_submit,
                  '#button_type' => 'submit',
                ],
              ]);
              ?>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="container-alt clearfix">
    <div class="form-item form-item-download text-right">
    <?php
      $link_attributes = array('query' => drupal_get_query_parameters(),'attributes' => array('class' => array('btn','btn-primary','btn-download','button')));
      print l('Download','admin/schedules/download',$link_attributes);
      ?>
    </div>
    <div class="table-responsive">
      <?php
      if (!empty($dados)) {
        print theme('table', array('rows' => $rowTotal, 'attributes' => array('class' => ['table-bordered', 'table-hover', 'table-report'])));
        print theme('table', array('header' => array_keys($dados['header']), 'rows' => $dados['rows'], 'attributes' => array('class' => ['table-bordered', 'table-hover', 'table-report'])))
          .theme_pager($variables);
      }
      ?>
    </div>
  </div>
  <?php
} else {
  print theme('table', array('rows' => $rowTotal, 'attributes' => array('class' => ['table-bordered', 'table-hover', 'table-report'])));
  print theme('report_scheduler', array('header' => $dados['header'], 'attributes' => array('border' => "1"), 'rows' => $dados['rows']));
  //print theme('table', array('header' => array_keys($dados['header']), 'attributes' => array('border' => "1"), 'rows' => $dados['rows']));
  exit;

}