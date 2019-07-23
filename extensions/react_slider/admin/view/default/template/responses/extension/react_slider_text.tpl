<?php
$language = $this->registry->get('language');
$language->load('react_slider/react_slider');
$this->load->model('localisation/language_definitions');
$definitions = $this->model_localisation_language_definitions->getLanguageDefinitions(
    array(
      'subsql_filter' => "section = 'storefront'
          AND block = 'react_slider_react_slider'  ",
      'language_id' =>  (int)$this->config->get('storefront_language_id')
    )
 );
?>
<div class="table-responsive" style="max-height: 100%; overflow: auto;">
	<table class="table table-striped">
  <?php foreach ($definitions as $definition) {
      $modal = '<a class="btn btn-xs btn_grid tooltips grid_action_edit" title="" data-action-type="edit" href="'.$this->html->getSecureURL('localisation/language_definition_form/update',
      '&language_definition_id='.$definition['language_definition_id']).'" rel="4689" data-original-title="Edit " data-toggle="modal" data-target="#message_modal"><i class="fa fa-edit fa-lg"></i></a>'; ?>
			<tr id="lang_def_<?php echo $definition['language_definition_id']; ?>">
				<td class="text-left"><?php echo $definition['language_key']; ?></a></td>
				<td class="text-left"><?php echo $definition['language_value']; ?></a></td>
				<td class="text-center"><?php echo $modal; ?></td>
			</tr>
  <?php } ?>
	</table>
</div>
