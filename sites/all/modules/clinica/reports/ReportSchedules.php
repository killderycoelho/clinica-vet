<?php


class ReportSchedules
{
  /**
   * @var boolean
   */
  public $toDownload = false;
  public $perPage = 30;
  public $hasCountTotal = false;


  /**
   * @return $this
   */
  public function toDownload()
  {
    $this->toDownload = true;

    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="relatorios_agendamentos_' . date('Ymd_His') . '.xls"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Pragma: no-cache');

    return $this;
  }

  /**
   *
   * @return type
   */
  public function getTable($user = null)
  {
    $rows = array();

    $results = $this->getTableData($user = null);

    $header = $this->getHeadersTable();

    if (!empty($results)) {
      foreach ($results as $i => $value) {
        //var_dump($value); die;
        $newRows = array(
          array('data' => $value->title),
          array('data' => $value->pet_name),
          array('data' => $value->type),
          array('data' => date("d.m.y", $value->day) . ' ' . $value->time),
          array('data' => $this->renderModalItem($value->nid)),
          array('data' => '<a href="node/$value->nid/edit?destination=schedules">Editar</a> | <a href="node/$value->nid/delete?destination=schedules">Deletar</a>'),
        );
        $rows[] = $newRows;
      }
    } else {
      $rows[] = array(
        array('data' => 'Nenhum registro encontrado', 'colspan' => count($header), 'style' => 'text-align:center'),
      );
    }

    return theme('clinica_report_scheduler', array(
      'dados' => array(
        'header' => $header,
        'rows' => $rows,
      ),
      'toDownload' => $this->toDownload,
    ));
  }

  /**
   * Retorna os headers da tabela do relatório
   *
   * @return array
   */
  private function getHeadersTable(){
    $header = array();
    //if ($this->toDownload) {
      $header = array(
        array(
          array('data' => 'Tutor'),
          array('data' => 'Nome do pet'),
          array('data' => 'Tipo de agendamento'),
          array('data' => 'Data do Agendamento'),
          array('data' => 'Detalhes'),
          array('data' => 'Ações'),
        )
      );
//    } else {
//      $header = array(
//        'Tutor' => array('data' => 'title', 'field' => 'title'),
//        'Nome do pet' => array('data' => 'pet_name', 'field' => 'pet_name'),
//        'Tipo de agendamento' => array('data' => 'type', 'field' => 'type'),
//        'Data do Agendamento' => array('data' => 'day', 'field' => 'day'),
//        'Detalhes' => array('data' => 'detalhes', 'field' => 'detalhes'),
//        'Ações' => array('data' => 'actions', 'field' => 'actions'),
//      );
//    }
    return $header;
  }

  /**
   * Retorna os dados para o relatório
   *
   * @return type
   */
  private function getTableData($user = null)
  {

    $query = db_select('node', 'n');
    $query->fields('n');

    // Joins
    $query->leftJoin('field_data_field_type', 'type', 'n.nid = type.entity_id');
    $query->leftJoin('field_data_field_petname', 'pt', 'n.nid = pt.entity_id');
    $query->leftJoin('field_data_field_description', 'desc', 'n.nid = desc.entity_id');
    $query->leftJoin('field_data_field_day', 'day', 'n.nid = day.entity_id');
    $query->leftJoin('field_data_field_horario', 'time', 'n.nid = time.entity_id');

    // Expressions
    $query->addExpression('type.field_type_value', 'type');
    $query->addExpression('pt.field_petname_value', 'pet_name');
    $query->addExpression('desc.field_description_value', 'description');
    $query->addExpression('day.field_day_value', 'day');
    $query->addExpression('time.field_horario_value', 'time');

    $query->orderBy('day');
    $query->condition('n.type', 'scheduler');

    //if ($user) $query->condition('n.uid', $user->uid);

    $this->addWhereByParams($query);

    return $return = $query->execute()->fetchAll();
  }

  /**
   *
   * @param db_select $query
   */
  private function addWhereByParams(&$query)
  {

    if (!$this->toDownload && $this->hasCountTotal == false) {
      $page = isset($_GET['page']) ? $_GET['page'] : 0;
      $start = $page * $this->perPage;
      $query->range($start, $this->perPage);
    }

    // limpando espacos em branco
    if (!empty($_GET)) {
      foreach($_GET as $pos => $value) {
        $_GET[$pos] = trim($value);
      }
    }

    if (!empty($_GET['nome'])) {
      $query->where("lower(s.nome) LIKE '%".strtolower($_GET['nome'])."%'");
    }

    if (!empty($_GET['data_ini'])) {
      $dataInicio = join('-', array_reverse(explode('/', $_GET['data_ini']))) . ' 00:00:00';
      $query->condition('s.created', "'".$dataInicio."'", '>=');
    }
    if (!empty($_GET['data_fim'])) {
      $dataFim = join('-', array_reverse(explode('/', $_GET['data_fim']))) . ' 23:59:59';
      $query->condition('s.created', "'".$dataFim."'", '<=');
    }
  }

  public function renderModalItem($nid) {
    $node = node_load($nid);
    $petname = $node->field_petname['und'][0]['value'];
    $date = date('d/m/Y', $node->field_day['und'][0]['value']) .' - '. $node->field_horario['und'][0]['value'] .'h';
    $type = $node->field_type['und'][0]['value'];
    $description = $node->field_description['und'][0]['value'];

    $out = " <a class='show-modal' data-toggle='modal' data-target='#schedulerModal'>Ver</a>
        <div class='modal fade' id='schedulerModal' role='dialog'>
          <div class='modal-dialog'>
            <div class='modal-content'>
              <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal'>&times;</button>
                <h4 class='modal-title'>Detalhes</h4>
              </div>
              <div class='modal-body'>
                <div class='row'>
                  <div class='col-md-6 info-text'>
                      <p><strong>Tutor:</strong> $node->title; </p>
                      <p><strong>Pet:</strong> $petname</p>
                      <p><strong>Data:</strong> $date </p>
                      <p><strong>Tipo de Serviço: $type</p>
                  </div>
                  <div class='col-md-6'>
                    <img width='162px' src='file_create_url(drupal_get_path('theme', 'clinica') . '/assets/images/calendar.png')'>
                  </div>
                </div>
                <div class='row row-description'>
                  <p><strong>Descriçao do serviço:</strong> $description</p>
                </div>
              </div>
              <div class='modal-footer'>
                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
              </div>
            </div>

          </div>
        </div>";

    return $out;

  }

}

/**
 * Retorna uma instância da Classe
 * @staticvar null $new
 * @return \ReportSchedules
 */
function report_schedules()
{
  static $new = null;
  if ($new !== null) {
    return $new;
  }
  return $new = new ReportSchedules();
}
