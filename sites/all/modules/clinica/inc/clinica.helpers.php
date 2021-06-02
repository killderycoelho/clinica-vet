<?php



/**
 * @param $variables
 * @return string
 */
function _bootstrap_avanz_table($variables)
{
  $header = isset($variables['header']) ? $variables['header'] : array();
  $rows = isset($variables['rows']) ? $variables['rows'] : array();
  $attributes = isset($variables['attributes']) ? $variables['attributes'] : array();
  $caption = isset($variables['caption']) ? $variables['caption'] : null;
  $colgroups = isset($variables['colgroups']) ? $variables['colgroups'] : array();
  $sticky = isset($variables['sticky']) ? $variables['sticky'] : null;
  $empty = isset($variables['empty']) ? $variables['empty'] : null;

  // Add sticky headers, if applicable.
  if (count($header) && $sticky) {
    drupal_add_js('misc/tableheader.js');
    // Add 'sticky-enabled' class to the table to identify it for JS.
    // This is needed to target tables constructed by this function.
    $attributes['class'][] = 'sticky-enabled';
  }

  $output = '<table' . drupal_attributes($attributes) . ">\n";

  if (isset($caption) && !empty($caption)) {
    $output .= '<caption>' . $caption . "</caption>\n";
  }

  // Add the 'empty' row message if available.
  if (!count($rows) && $empty) {
    $header_count = 0;
    foreach ($header as $header_cell) {
      if (is_array($header_cell)) {
        $header_count += isset($header_cell['colspan']) ? $header_cell['colspan'] : 1;
      } else {
        $header_count++;
      }
    }
    $rows[] = array(array('data' => $empty, 'colspan' => $header_count, 'class' => array('empty', 'message')));
  }

  // Format the table header:
  if (count($header)) {
    $ts = tablesort_init($header);
    // HTML requires that the thead tag has tr tags in it followed by tbody
    // tags. Using ternary operator to check and see if we have any rows.
    $output .= (count($rows) ? ' <thead><tr>' : ' <tr>');

    /* */
    if (isset($header[0])) {
      $trs = array();
      foreach ($header as $head) {
        $tds = '';
        foreach ($head as $cell) {
          $cell = tablesort_header($cell, $head, $ts);
          $tds .= _theme_table_cell($cell, TRUE);
        }
        $trs[] .= $tds;
      }
      $output .= join('</tr><tr>', $trs);
    }else {
      foreach ($header as $cell) {
        $cell = tablesort_header($cell, $header, $ts);
        $output .= _theme_table_cell($cell, TRUE);
      }
    }/* */

    // Using ternary operator to close the tags based on whether or not there are rows
    $output .= (count($rows) ? " </tr></thead>\n" : "</tr>\n");
  } else {
    $ts = array();
  }

  // Format the table rows:
  if (count($rows)) {
    $output .= "<tbody>\n";
    $flip = array('even' => 'odd', 'odd' => 'even');
    $class = 'even';
    foreach ($rows as $number => $row) {
      // Check if we're dealing with a simple or complex row
      if (isset($row['data'])) {
        $cells = $row['data'];
        $no_striping = isset($row['no_striping']) ? $row['no_striping'] : FALSE;

        // Set the attributes array and exclude 'data' and 'no_striping'.
        $attributes = $row;
        unset($attributes['data']);
        unset($attributes['no_striping']);
      } else {
        $cells = $row;
        $attributes = array();
        $no_striping = FALSE;
      }
      if (count($cells)) {
        // Add odd/even class
        if (!$no_striping) {
          $class = $flip[$class];
          $attributes['class'][] = $class;
        }

        // Build row
        $output .= ' <tr' . drupal_attributes($attributes) . '>';
        $i = 0;
        foreach ($cells as $cell) {
          $cell = tablesort_cell($cell, $header, $ts, $i++);
          $output .= _theme_table_cell($cell);
        }
        $output .= " </tr>\n";
      }
    }
    $output .= "</tbody>\n";
  }

  $output .= "</table>\n";
  return $output;
}

function __bootstrap_avanz_theme_table_cell($cell, $header = FALSE)
{
  $attributes = '';

  if (is_array($cell)) {
    $data = isset($cell['data']) ? $cell['data'] : '';
    // Cell's data property can be a string or a renderable array.
    if (is_array($data)) {
      $data = drupal_render($data);
    }
    $header |= isset($cell['header']);
    unset($cell['data']);
    unset($cell['header']);
    $attributes = drupal_attributes($cell);
  }
  else {
    $data = $cell;
  }
  if ($header) {
    $output = "<th$attributes>$data</th>";
  }
  else {
    $output = "<td$attributes>$data</td>";
  }

  return $output;
}


/**
 * @param $user
 * @return DatabaseStatementInterface|null
 */
function clinica_reset_password($user){
  define('DRUPAL_ROOT', getcwd());
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  require_once DRUPAL_ROOT . '/includes/password.inc';

  $newhash = user_hash_password('qwe123');
  return db_update('users')->fields(array('pass' => $newhash))->condition('uid', $user->uid, '=')->execute();
}

/**
 * Add a role to a user.
 *
 * @param $user
 * @param $role_name
 * @throws Exception
 */
function clinica_add_role_to_user($user, $role_name) {

  if (is_numeric($user)) {
    $user = user_load($user);
  }

  $key = array_search($role_name, $user->roles);
  if ($key == FALSE) {
    $roles = user_roles(TRUE);
    $rid = array_search($role_name, $roles);
    if ($rid != FALSE) {

      $new_role[$rid] = $role_name;
      $all_roles = $user->roles + $new_role;
      user_save($user, array('roles' => $all_roles));
    }
  }
}


/**
 * Unpublish schedule one day after scheduled
 */
function clinica_unpublish_schedule_last_day(){

  $query = db_select('node', 'n');
  $query->fields('n');
  $query->leftJoin('field_data_field_day', 'day', 'n.nid = day.entity_id');
  $query->leftJoin('field_data_field_checked_scheduler', 'chk', 'n.nid = chk.entity_id');
  $query->addExpression('day.field_day_value', 'day');
  $query->addExpression('chk.field_checked_scheduler_value', 'answered');
  $query->condition('n.type', 'scheduler');
  $schedules = $query->execute()->fetchAll();
  foreach ($schedules as $schedule){
    if (time() - $schedule->day > 60*60*24) {
      $node = node_load($schedule->nid);
      //$node->field_checked_scheduler['und'][0]['value'] = true;
      $node->status = 0;
      node_save($node);
    }
  }
}


/**
 * Disable Vertical tabs on a form with simply changing the value of $form['additional_settings']['#type']
 * @see https://drupal.org/node/1048644
 */
function form_disable_vertical_tabs(&$form){
  // originally $form['additional_settings']['#type'] equals to 'vertical_tabs'
  if(isset($form['additional_settings']['#type']) && ($form['additional_settings']['#type'] === 'vertical_tabs')){
    $form['additional_settings']['#type'] = 'fieldset';
  }
}
