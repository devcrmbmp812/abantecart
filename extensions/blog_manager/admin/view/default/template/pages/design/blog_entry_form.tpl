

<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php echo $entry_tabs ?>
<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
        	<?php if( $blog_entry_id ) { ?>
                <div class="btn-group mr10 toolbar">
                    <a class="btn btn-primary tooltips" href="<?php echo $insert; ?>" title="<?php echo $button_add; ?>">
                    <i class="fa fa-plus"></i>
                    </a>
                </div>
            <?php } ?>
			<div class="btn-group mr10 toolbar">
				<?php echo $this->getHookVar('common_content_buttons'); ?>
					<?php if ($help) { ?>
                        <a class="btn btn-white tooltips"
                           href="<?php echo $help['link']; ?>"
                           data-toggle="modal" data-target="#help_modal"
                           title="<?php echo $text_more_help ?>"><i
                                    class="fa fa-flask fa-lg"></i> <?php echo $help['text'] ?></a>
                    <?php } ?>
				<?php echo $this->getHookVar('extension_toolbar_buttons'); ?>
			</div>
		</div>
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<div class="col-md-9 mb10">
        	<?php $edit_fields = array('content', 'enry_intro',); ?>
			<?php foreach ($form['fields'] as $name => $field) { ?>
            <?php 
				//Logic to calculate fields width
				$widthcasses = "col-sm-9";
				if ( is_int(stripos($field->style, 'large-field')) ) {
					$widthcasses = "col-sm-9";
				} else if ( is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date')) ) {
					$widthcasses = "col-sm-9";
				} else if ( is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch')) ) {
					$widthcasses = "col-sm-3";
				} else if ( is_int(stripos($field->style, 'tiny-field')) ) {
					$widthcasses = "col-sm-2";
				}
				$widthcasses .= " col-xs-12";
			?>
		<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
			<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
			<div id="field_<?php echo $name; ?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo (in_array($name, $edit_fields) ? 'ml_ckeditor' : '')?>">
				<?php if($name == 'keyword') { ?>
				<span class="input-group-btn">
					<?php echo $keyword_button; ?>
				</span>
				<?php } ?>
                <?php echo $field; ?>
			</div>
		    <?php if (!empty($error[$name])) { ?>
		    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
		    <?php } ?>
            
		</div>
        <?php }  ?>
        </div>
       <div class="col-md-3 mb10">
			<div id="image">
			   <?php if ( !empty($update) ) {
				echo $resources_html;
			} ?>
			</div>
		</div> 
	</div>

	<div class="panel-footer col-xs-12">
		<div class="text-center">
			<button id="page-submit" class="btn btn-primary lock-on-click">
			<i class="fa fa-cog fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<a class="btn btn-default" href="<?php echo $cancel; ?>">
			<i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
			</a>
		</div>
	</div>
	</form>

</div><!-- <div class="tab-content"> -->
<?php echo $resources_scripts; ?>
<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'help_modal',
				'modal_type' => 'lg',
				'data_source' => 'ajax'
		));
?>

<script type="text/javascript"><!--
$(document).ready(function () {
	$('#editEntryFrm_generate_seo_keyword').click(function(){
		var seo_name = $('#editEntryFrm_blog_entry_descriptions<?php echo $language_id; ?>entry_title').val().replace('%','');
		$.get('<?php echo $generate_seo_url;?>&seo_name='+seo_name, function(data){
			$('#editEntryFrm_keyword').val(data).change();
		});
	});
});	

//--></script>


