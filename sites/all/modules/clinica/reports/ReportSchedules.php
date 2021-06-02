<?php


class ReportSchedules
{
  /**
   * @var boolean
   */
  public $toDownload = false;
  public $perPage = 30;
  public $hasCountTotal = false;
  public $isAdmin = false;

  /**
   * @return $this
   */
  public function toDownload()
  {
    $this->toDownload = true;

    setlocale(LC_ALL,'pt_BR.UTF8');
    mb_internal_encoding('UTF8');
    mb_regex_encoding('UTF8');
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="relatorio_agendamentos_' . date('Ymd_His') . '.xls"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Pragma: no-cache');

    return $this;
  }

  /**
   * @return string
   * @throws Exception
   */
  public function getTable()
  {
    $rows = array();

    global $user;

    if (in_array('admin master', $user->roles)) $this->isAdmin = true;

    // paginacao
    if (!$this->toDownload) {
      $totalData = $this->getTableDataTotal();
      // Initialize the pager
      pager_default_initialize($totalData, $this->perPage);
    }

    $results = $this->getTableData();

    $header = $this->getHeadersTable();

    if (!empty($results)) {
      foreach ($results as $i => $value) {
        if(!$this->toDownload){
          $newRows = array(
            array('data' => $value->title),
            array('data' => $value->pet_name),
            array('data' => $value->type),
            array('data' => date("d/m/Y", $value->day) . ' ' . $value->time . 'h'),
            array('data' => $this->getStatusSchedule($value->status, $value->answered)),
            array('data' => $this->renderModalItem($value->nid)),
            array('data' => "<a href='node/$value->nid/edit?destination=schedules'>Editar</a> | <a href='node/$value->nid/delete?destination=schedules'>Deletar</a>"),
          );
        }else {
          $newRows = array(
            array('data' => $value->title),
            array('data' => $value->pet_name),
            array('data' => $value->type),
            array('data' => date("d/m/Y", $value->day) . ' ' . $value->time . 'h'),
            array('data' => $this->getStatusSchedule($value->status, $value->answered)),
          );
        }

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
        'total' => $this->getTableDataTotal(),
      ),
      'isAdmin' => $this->isAdmin,
      'toDownload' => $this->toDownload,
    ));
  }

  /**
   * Retorna a quantidade total dos dados para o relatório
   *
   * @return type
   */
  private function getTableDataTotal()
  {
    static $total = null;
    if ($total !== null) {
      return $total;
    }
    global $user;

    $this->hasCountTotal = true;
    $query = db_select('node', 'n');
    $query->leftJoin('field_data_field_day', 'day', 'n.nid = day.entity_id');
    $query->leftJoin('field_data_field_type', 'type', 'n.nid = type.entity_id');
    $query->leftJoin('field_data_field_petname', 'pt', 'n.nid = pt.entity_id');
    $query->condition('n.type', 'scheduler');
    $query->addExpression('COUNT(*)', 'total');
    if (!$this->isAdmin) $query->condition('n.uid', $user->uid);
    $this->addWhereByParams($query);
    $this->hasCountTotal = false;
    return $total = $query->execute()->fetchColumn();
  }

  /**
   * Retorna os headers da tabela do relatório
   *
   * @return array
   */
  private function getHeadersTable(){
    $header = array();
    if ($this->toDownload) {
      $header = array(
        array(
          array('data' => 'Tutor'),
          array('data' => 'Nome do pet'),
          array('data' => 'Tipo de agendamento'),
          array('data' => 'Data do Agendamento'),
          array('data' => 'Status')
        )
      );
    } else {
      $header = array(
        'Tutor' => array('data' => 'Tutor', 'field' => 'title'),
        'Nome do pet' => array('data' => 'Nome do pet', 'field' => 'pet_name'),
        'Tipo de agendamento' => array('data' => 'Tipo de agendamento', 'field' => 'type'),
        'Data do Agendamento' => array('data' => 'Data do Agendamento', 'field' => 'day'),
        'Status' => array('data' => 'Status', 'field' => 'status'),
        'Detalhes' => array('data' => 'Detalhes', 'field' => 'detalhes'),
        'Ações' => array('data' => 'Ações', 'field' => 'ações'),
      );
    }
    return $header;
  }

  /**
   * @param $nodeStatus
   * @param $nodeScheduler
   * @return string
   */
  private function getStatusSchedule($nodeStatus, $nodeScheduler) {
    if ($nodeStatus == 0 &&  !$nodeScheduler){
      return 'Cancelado';
    }
    if ($nodeStatus == 1 &&  $nodeScheduler){
      return 'Realizado';
    }
    if ($nodeStatus == 0 &&  $nodeScheduler){
      return 'Realizado';
    }
    if ($nodeStatus == 1 &&  !$nodeScheduler){
      return 'Agendado';
    }
  }
  /**
   * Retorna os dados para o relatório
   *
   * @return type
   */
  private function getTableData()
  {
    global $user;

    $query = db_select('node', 'n');
    $query->fields('n');

    // Joins
    $query->leftJoin('field_data_field_type', 'type', 'n.nid = type.entity_id');
    $query->leftJoin('field_data_field_petname', 'pt', 'n.nid = pt.entity_id');
    $query->leftJoin('field_data_field_description', 'desc', 'n.nid = desc.entity_id');
    $query->leftJoin('field_data_field_day', 'day', 'n.nid = day.entity_id');
    $query->leftJoin('field_data_field_horario', 'time', 'n.nid = time.entity_id');
    $query->leftJoin('field_data_field_checked_scheduler', 'chk', 'n.nid = chk.entity_id');

    // Expressions
    $query->addExpression('type.field_type_value', 'type');
    $query->addExpression('pt.field_petname_value', 'pet_name');
    $query->addExpression('desc.field_description_value', 'description');
    $query->addExpression('day.field_day_value', 'day');
    $query->addExpression('time.field_horario_value', 'time');
    $query->addExpression('chk.field_checked_scheduler_value', 'answered');

    $query->orderBy('day', 'DESC');
    $query->condition('n.type', 'scheduler');

    if (!$this->isAdmin) $query->condition('n.uid', $user->uid);

    $this->addWhereByParams($query);

    return $query->execute()->fetchAll();
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
      $query->where("lower(n.title) LIKE '%".strtolower($_GET['nome'])."%'");
    }

    if (!empty($_GET['type']) && $_GET['type'] !== 'Tipo') {
      $query->where("lower(type.field_type_value) LIKE '%".strtolower($_GET['type'])."%'");
    }

    if (!empty($_GET['petname'])) {
      $query->where("lower(pt.field_petname_value) LIKE '%".strtolower($_GET['petname'])."%'");
    }



//    if (!empty($_GET['data_ini'])) {
//      $dataInicio = join('-', array_reverse(explode('/', $_GET['data_ini']))) . ' 00:00:00';
//      $query->condition('day', "'".strtotime($dataInicio)."'", '>=');
//    }
//    if (!empty($_GET['data_fim'])) {
//      $dataFim = join('-', array_reverse(explode('/', $_GET['data_fim']))) . ' 23:59:59';
//      $query->condition('day', "'".strtotime($dataFim)."'", '<=');
//    }
  }

  /**
   * Render modal item
   *
   * @param $nid
   * @return string
   * @throws Exception
   */
  public function renderModalItem($nid) {
    return theme('clinica_modal', array(
      'dados' => node_load($nid),
    ));
  }
}

/**
 * Retorna uma instância da Classe
 * @staticvar null $new
 * @return ReportSchedules
 */
function report_schedules()
{
  static $new = null;
  if ($new !== null) {
    return $new;
  }
  return $new = new ReportSchedules();
}
