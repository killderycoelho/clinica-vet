<a class='show-modal' data-toggle='modal' data-target='#schedulerModal'>Ver</a>
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
            <p><strong>Tutor:</strong> <?php print $dados->title; ?> </p>
            <p><strong>Pet:</strong> <?php print $dados->field_petname['und'][0]['value']; ?></p>
            <p><strong>Data:</strong> <?php print date('d/m/Y', $dados->field_day['und'][0]['value']) .' - '. $dados->field_horario['und'][0]['value'] .'h'; ?> </p>
            <p><strong>Tipo de Serviço:</strong> <?php print $dados->field_type['und'][0]['value']; ?></p>
          </div>
          <div class='col-md-6'>
            <img width='162px' src="<?php print file_create_url(drupal_get_path('theme', 'clinica') . '/assets/images/calendar.png'); ?>">
          </div>
        </div>
        <div class='row row-description'>
          <p><strong>Descriçao do serviço:</strong> <?php print $dados->field_description['und'][0]['value']; ?></p>
        </div>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
      </div>
    </div>

  </div>
</div>
