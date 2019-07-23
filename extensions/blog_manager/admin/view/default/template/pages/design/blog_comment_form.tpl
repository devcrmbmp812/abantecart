

<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
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
        <div class="primary_content_actions pull-left">
        	<div class="btn-group mr10 toolbar">
            <span> In Response To: </span>
            </div>
        	<div class="btn-group mr10 toolbar">
                <a class="btn btn-white tooltips"
						   href="<?php echo $article_info['link']; ?>"
						   data-toggle="modal" data-target="#data_modal"
						   title=""><i
									class="fa fa-folder fa-lg"></i> <?php echo $article_info['text']; ?></a>
                                    
            </div>
            <?php if($comment_info) { ?>
            <div class="btn-group mr10 toolbar">
                <a class="btn btn-white tooltips"
						   href="<?php echo $comment_info['link']; ?>"
						   data-toggle="modal" data-target="#data_modal"
						   title=""><i
									class="fa fa-comments fa-lg"></i> <?php echo $comment_info['text']; ?></a>
                                    
            </div>
            <?php } ?>
            <?php if (!$allow_comment) { ?>
            <div class="btn-group mr10 toolbar">
            	<?php echo $comment_warning_message; ?> 
            </div>
            <div class="btn-group mr10 toolbar">
            	<?php echo $comment_on_button; ?>
            </div>
            <?php } ?>
        </div>
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
					$widthcasses = "col-sm-5";
				} else if ( is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch')) ) {
					$widthcasses = "col-sm-3";
				} else if ( is_int(stripos($field->style, 'tiny-field')) ) {
					$widthcasses = "col-sm-2";
				}
				$widthcasses .= " col-xs-12";
			?>
		<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
			<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?>
            	 <?php if ($name == 'comment') {
                	echo '<br /><div class="comment_list_date"><span class="comment_list_head">' . $comment_on . '</span> <br /><span class="comment_list_date"> ' . $date_added . '</span>';
                    if ($date_added != $date_modified) {
                    	echo '<br /><span class="comment_list_edited">' . $last_edited . '<br />' . $date_modified . '</span>';
                    }
                    echo '</div>';
                } ?>
            </label>
			<div id="field_<?php echo $name; ?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'comment' ? 'ml_ckeditor' : '')?>">
            	
				<?php echo $field; ?>
                <?php if ($name == 'approved' && $approve_comments == 0) {
                	echo '<div style="margin-top: 7px;">' . $automatic_approval . '</div>';
                 } ?>
              
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

</div>

<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'help_modal',
				'modal_type' => 'lg',
				'data_source' => 'ajax'
		));
?>
<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'data_modal',
				'modal_type' => 'lg',
				'data_source' => 'ajax'
		));
?>


