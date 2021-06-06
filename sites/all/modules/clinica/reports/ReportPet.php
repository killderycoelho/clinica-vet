<?php


class ReportPet
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
    header('Content-Disposition: attachment; filename="relatorio_pets_' . date('Ymd_His') . '.xls"');
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
            array('data' => $value->raca),
            array('data' => $value->cor),
            array('data' => date("d/m/Y", $value->birthday)),
            array('data' => "<a href='node/$value->nid/edit?destination=schedules'>Editar</a> | <a href='node/$value->nid/delete?destination=schedules'>Deletar</a>"),
          );
        }else {
          $newRows = array(
            array('data' => $value->title),
            array('data' => $value->raca),
            array('data' => $value->cor),
            array('data' => date("d/m/Y", $value->birthday))
          );
        }

        $rows[] = $newRows;
      }
    } else {
      $rows[] = array(
        array('data' => 'Nenhum registro encontrado', 'colspan' => count($header), 'style' => 'text-align:center'),
      );
    }

    return theme('clinica_report_pets', array(
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
    $query->leftJoin('field_data_field_raca', 'raca', 'n.nid = raca.entity_id');
    $query->leftJoin('field_data_field_cor', 'cor', 'n.nid = cor.entity_id');
    $query->leftJoin('field_data_field_birthday', 'bday', 'n.nid = bday.entity_id');
    $query->condition('n.type', 'pet');
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
          array('data' => 'Nome'),
          array('data' => 'Raça'),
          array('data' => 'Cor'),
          array('data' => 'Data de nascimento')
        )
      );
    } else {
      $header = array(
        'Nome' => array('data' => 'Pet', 'field' => 'title'),
        'Raça' => array('data' => 'Raça', 'field' => 'pet_name'),
        'Cor' => array('data' => 'Cor', 'field' => 'type'),
        'Data de nascimento' => array('data' => 'Data de Nascimento', 'field' => 'day'),
        'Ações' => array('data' => 'Ações', 'field' => 'status'),
      );
    }
    return $header;
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
    $query->leftJoin('field_data_field_raca', 'raca', 'n.nid = raca.entity_id');
    $query->leftJoin('field_data_field_cor', 'cor', 'n.nid = cor.entity_id');
    $query->leftJoin('field_data_field_birthday', 'bday', 'n.nid = bday.entity_id');

    // Expressions
    $query->addExpression('raca.field_raca_value', 'raca');
    $query->addExpression('cor.field_cor_value', 'cor');
    $query->addExpression('bday.field_birthday_value', 'birthday');

    $query->orderBy('title', 'ASC');
    $query->condition('n.type', 'pet');

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

    if (!empty($_GET['raca'])) {
      $query->where("lower(raca.field_raca_value) LIKE '%".strtolower($_GET['raca'])."%'");
    }
  }
}

/**
 * Retorna uma instância da Classe
 * @staticvar null $new
 * @return ReportPet
 */
function report_pet()
{
  static $new = null;
  if ($new !== null) {
    return $new;
  }
  return $new = new ReportPet();
}
