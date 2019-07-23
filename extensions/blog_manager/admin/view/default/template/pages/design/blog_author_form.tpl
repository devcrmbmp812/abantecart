

<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php echo $blog_author_tabs ?>
<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
        	<?php if( $blog_author_id ) { ?>
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
			<?php foreach ($form['fields'] as $name => $field) { ?>
             <?php if($field->type == 'hidden') { ?>
             	<?php echo $field; ?>
            <?php }else{ ?>
            <?php 
				//Logic to calculate fields width
				$widthcasses = "col-sm-7";
				if ( is_int(stripos($field->style, 'large-field')) ) {
					$widthcasses = "col-sm-7";
				} else if ( is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date')) ) {
					$widthcasses = "col-sm-7";
				} else if ( is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch')) ) {
					$widthcasses = "col-sm-3";
				} else if ( is_int(stripos($field->style, 'tiny-field')) ) {
					$widthcasses = "col-sm-2";
				}
				$widthcasses .= " col-xs-12";
			?>
		<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
			<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
			<div id="field_<?php echo $name; ?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
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
        <?php }  ?>
      <!--- </div>
       <div class="col-md-3 mb10">
			<div id="image">
			   <?php if ( !empty($update) ) {
				echo $resources_html;
				echo $resources_scripts;
			} ?>
			</div>
		</div> --->
            
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
	
	$('#blogAuthorFrm_generate_seo_keyword').click(function(){
		var name = $('#blogAuthorFrm_firstname').val() + ' ' + $('#blogAuthorFrm_lastname').val();
		var seo_name = name.replace('%','');
		$.get('<?php echo $generate_seo_url;?>&seo_name='+seo_name, function(data){
			$('#blogAuthorFrm_keyword').val(data).change();
		});
	});

	<?php if($access == 'restrict') { ?>	
		$('#blogAuthorFrm_firstname').focus(function() {
			if($('#blogAuthorFrm_blog_users').val() == 0) {
				alert('It is recommended to populate author data from user data. You may need to create a user first.');	
			}
		});
	<?php } ?>	
	$('#blogAuthorFrm_blog_users').on('change', function () {
		
		var id = $('#blogAuthorFrm_blog_users').val();
		if(id){
			$.ajax({
				url: "<?php echo $user_link; ?>&id=" + id,
				cache: false,
				type: 'GET',
				dataType: 'json',
				success: function(data){
					if(data) {
						$.each(data, function(index, value) {
							$('#blogAuthorFrm_'+index).val('');
							if(value) {
								$('#blogAuthorFrm_'+index).val(value);
							}
						});
					}
					
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus+" "+errorThrown);
				}
			});
			
		}
		
	});

});
//--></script>

