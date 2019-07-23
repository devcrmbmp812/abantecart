<h1 class="heading1">
  	<span class="maintext"><?php echo $heading_title; ?></span>
</h1>

<?php if ($success) { ?>
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo $success; ?>
    </div>
<?php } ?>
    
<?php if ($error) { ?>
    <div class="alert alert-error alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo $error; ?>
    </div>
<?php } ?>
  <h3><?php echo $text_form_edit_heading; ?></h3>
	<?php echo $form['form_open']; ?>
    <div class="registerbox form-horizontal">
		<fieldset>
        	<div class="form-group clear">    
                <div class="control-label col-md-4"><?php echo $entry_user_name; ?></div>
                <div class="value-text col-md-4"><?php echo $user_name; ?></div>
            </div>
            <div class="form-group clear">    
                <div class="control-label col-md-4"><?php echo $entry_role; ?></div>
                <div class="value-text col-md-4"><?php echo $role; ?></div>
            </div>
            <div class="form-group clear">
                <div class="control-label col-md-4"><?php echo $entry_first_name; ?></div>
                <div class="value-text col-md-4"><?php echo $first_name; ?></div>
            </div>
            <div class="form-group clear">
                <div class="control-label col-md-4"><?php echo $entry_last_name; ?></div>
                <div class="value-text col-md-4"><?php echo $last_name; ?></div>
            </div>
            <div class="form-group clear">
                <div class="control-label col-md-4"><?php echo $entry_email; ?></div>
                <div class="value-text col-md-4"><?php echo $email; ?></div>
            </div>
            <?php
                $field_list = array();
                array_push($field_list, 'name_option', 'site_url', 'users_tz', 'blog_user_id');
                
                foreach ($field_list as $field_name) {
            ?>
                <div class="form-group <?php if (${'error_'.$field_name}) echo 'has-error'; ?>">
                    <label class="control-label col-md-4"><?php echo ${'entry_'.$field_name}; ?></label>
                    <div class="input-group col-md-4">
                        <?php echo $form[$field_name]; ?>
                    </div>
                    <span class="help-block"><?php echo ${'error_'.$field_name}; ?></span>
                </div>		
            <?php
                }
            ?>	
            
            <?php echo $this->getHookVar('customer_attributes'); ?>
                <div class="form-group clearfix">
                <div class="control-label col-sm-4"></div>
                <div class="input-group col-sm-">
                    <div class="pull-left">
                        <?php echo $form['submit']; ?>
                    </div>
                </div>
            </div>
            
      	</fieldset>
    </div>
</form>


<h3><?php echo $text_notifications; ?></h3>
	<div class="container-fluid table-responsive">
		<table class="table table-striped table-bordered notification_table">	
    		<tr>
				<th class="align_left"><?php echo $text_article; ?></th>
				<th class="align_left"><?php echo $text_notice_type; ?></th>
				<th class="align_center"><?php echo $text_action; ?></th>
          	</tr>
			<?php if($notifications) {
				foreach ($notifications as $notify) { 
                	if ($notify['type'] == '1') {
                    	$type = $text_all_comment; 
                     }elseif ($notify['type'] == '2') {
                     	$type = $text_on_reply;
                     } ?>
   					<tr>
                        <td class="align_left"><?php echo $notify['entry_title']; ?></td>
                        <td class="align_left"><?php echo $type; ?></td>
                        <td class="align_center"><a href="<?php echo $notify['cust_remove_url']; ?>" alt="Remove" title="Remove" class="btn btn-xs btn-default"><i class="fa fa-trash-o fa-fw"></i></a></td>
                    </tr>
   				<?php }
                }else{ ?>
                	<tr>
                    	<td colspan="3"><?php echo $text_no_notifications; ?></td>
                    </tr>
				<?php } ?>
		</table>
  	</div>




<script type="text/javascript"><!--
	$('#blogUEFrm_submit').on('click', function(event) {
		event.preventDefault();
		$("#blogUEFrm").submit();
	});
	$('#blogUEFrm_cancel').on('click', function(event) {
		event.preventDefault();
		$("#blogUEFrm")[0].reset();
	});
	$(document).ready(function(){
		var tz = jstz.determine();
		var zone = $('#blogUEFrm_users_tz').val();
		if(zone == 0) {
			$('#blogUEFrm_users_tz').val(tz.name());
		}
	});
//--></script>