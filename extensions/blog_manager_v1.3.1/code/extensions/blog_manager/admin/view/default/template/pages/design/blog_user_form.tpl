

<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
        	<?php if( $blog_user_id ) { ?>
                <div class="btn-group mr10 toolbar">
                    <a class="btn btn-primary tooltips" href="<?php echo $insert; ?>" title="<?php echo $button_add; ?>">
                    <i class="fa fa-plus"></i>
                    </a>
                </div>
            <?php } ?>
			<div class="btn-group mr10 toolbar">
				<?php echo $this->getHookVar('common_content_buttons'); ?>
					<?php if ($help) {
						?>
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
        <?php $customer_fields = array('name', 'username', 'email', ); ?>
        <?php $no_fields = array('password'); ?>
        <?php foreach ($form['fields'] as $name => $field) { ?>
            <?php 
				//Logic to calculate fields width
				$widthcasses = "col-sm-7";
				if ( is_int(stripos($field->style, 'large-field')) ) {
					$widthcasses = "col-sm-7";
				} else if ( is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date')) ) {
					$widthcasses = "col-sm-5";
				} else if ( is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch')) ) {
					$widthcasses = "col-sm-3";
				} else if ( is_int(stripos($field->style, 'tiny-field')) ) {
					$widthcasses = "col-sm-2";
				}
				$widthcasses .= " col-xs-12";
			?>
    		<?php if(isset($source) && $source == 'customer') { ?>
            	<?php if(!in_array($name, $no_fields)) { ?>
                    <div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
                        <label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
                        <?php if (in_array($name,$customer_fields)) { ?>
                            <div style="margin-top: 7px;" class="<?php echo $widthcasses; ?>">
                                <?php echo $field->value; ?>
                            </div>
                        <?php }else{ ?>
                            <div id="field_<?php echo $name; ?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
                                <?php echo $field; ?>
                            </div>
                        <?php } ?>
                        
                        <?php if (!empty($error[$name])) { ?>
                        <span class="help-block field_err"><?php echo $error[$name]; ?></span>
                        <?php } ?>
                    </div>
                <?php }  ?>
          	<?php }else{ ?>
            	<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
                    <label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
                    <div id="field_<?php echo $name; ?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
                        <?php echo $field; ?>
                    </div>
                    <?php if (!empty($error[$name])) { ?>
                    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
                    <?php } ?>
                    
                </div>
    		<?php }  ?>
        <?php }  ?>
       
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

<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'help_modal',
				'modal_type' => 'lg',
				'data_source' => 'ajax'
		));
?>

<script type="text/javascript"><!--

$(document).ready(function () {
		$('#bloguserFrm_role_id').on('change', function () {
			var author_alert = '<?php echo $text_author_change; ?>';
			var contrib_alert = '<?php echo $text_contrib_change; ?>';
			var role = $('#bloguserFrm_role_id').val();
			if(role == '2'){alert(author_alert);}
			if(role == '3'){alert(contrib_alert);}
			
		});
	
});
//--></script>


