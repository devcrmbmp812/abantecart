

<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php echo $blog_manager_tabs ?>
<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
			<div class="btn-group mr10 toolbar">
				<?php echo $this->getHookVar('common_content_buttons'); ?>
					<?php if ($extension_info['help']['file']) {
						?>
						<a class="btn btn-white tooltips"
						   href="<?php echo $extension_info['help']['file']['link']; ?>"
						   data-toggle="modal" data-target="#howto_modal"
						   title="<?php echo $text_more_help ?>"><i
									class="fa fa-flask fa-lg"></i> <?php echo $extension_info['help']['file']['text'] ?></a>
					<?php } ?>
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
		<?php $edit_fields = array('message_top', 'message_bottom', 'comment_policy', 'disclaimer'); ?>
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
            
		<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
			<label class="control-label <?php echo ($active == 'settings' ? 'col-sm-4' : 'col-sm-3') ?> col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
			<div id="field_<?php echo $name; ?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo (in_array($name, $edit_fields) ? 'ml_ckeditor' : '')?>">
            	<?php echo $field; ?>                                                                
			</div>
		    <?php if (!empty($error[$name])) { ?>
		    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
		    <?php } ?>
            
		</div>
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
<?php echo $resources_scripts; ?>
<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'howto_modal',
				'modal_type' => 'lg',
				'data_source' => 'ajax'
		));
?>
<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'help_modal',
				'modal_type' => 'lg',
				'data_source' => 'ajax'
		));
?>
<script type="text/javascript"><!--
	$(document).ready(function () {
		
		if($('#blogManagerFrm_blog_ssl_layer button.btn-primary').hasClass('btn-off')) {
			$('#field_blog_ssl_url span.input-group-addon').css('display','none');
		}
		
		$('#blogManagerFrm_blog_ssl_layer button.btn').on('click', function(e) {
			if($(this).text() == 'OFF') {
				$('#field_blog_ssl_url span.input-group-addon').css('display','none');
				$('#blogManagerFrm_blog_ssl_url').attr('readonly', 'readonly').val('');
			}else{
				$('#blogManagerFrm_blog_ssl_url').removeProp('readonly');
				$('#field_blog_ssl_url span.input-group-addon').removeAttr('style').val('');
			}
		});
		$('#blogManagerFrm_use_store_url').on('change', function(){
			var sel = $('#blogManagerFrm_use_store_url').val();
			if(sel == 1) {
				$('#blogManagerFrm_blog_url').removeProp('readonly').val('');
				$('#blogManagerFrm_blog_ssl_url').removeProp('readonly').val('');
			}else{
				getStoreData();
				
			}
		});
	
	});
	
	function getStoreData() {
		
		var store = $('#blogManagerFrm_blog_store_id').val();
		$.ajax({
			type: 'GET',
			url: "<?php echo $this->html->getSecureURL('r/tool/content/stores'); ?>&store_id" + store,
			dataType: 'json',
			success: function (data, status, XHR){
				$('#blogManagerFrm_blog_url').attr('value',data.config_url+'blog');
				if(data.config_ssl_url) {
					$('#blogManagerFrm_blog_ssl_url').attr('value',data.config_ssl_url+'blog');
				}
				$('#blogManagerFrm_blog_url').attr('readonly', 'readonly');
				$('#blogManagerFrm_blog_ssl_url').attr('readonly', 'readonly');
			},
			error: function (jqXHR, textStatus, errorThrown) {
				 alert(textStatus + ": " + errorThrown);
			}
		});
		
	}

//--></script>
