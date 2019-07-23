<h1 class="heading1">
  	<span class="maintext"><?php echo $heading_title; ?></span>
</h1>

<?php if ($success) { ?>
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<?php echo $success; ?>
</div>
<?php } ?>

<?php if ($error_warning) { ?>
<div class="alert alert-error alert-danger">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<?php echo $error_warning; ?>
</div>
<?php } ?>
 
<?php echo $form['form_open']; ?>
    <div class="registerbox form-horizontal">

 <h3><?php echo $text_form_edit_heading; ?></h3>
 <?php if($source == 'customer') { ?>
    <h3>
        <?php echo $text_cust_edit; ?> <a href="<?php echo $cust_edit_link; ?>"><?php echo $text_click_here; ?></a>
    </h3>
<?php } ?>
   <br /><br />
   		<div class="form-group clear">    
 			<div class="control-label col-sm-4"><?php echo $entry_user_name; ?></div>
        	<div class="input-group value-text col-sm-4"><?php echo $user_name; ?></div>
        </div>
        <div class="form-group clear">    
 			<div class="control-label col-sm-4"><?php echo $entry_role; ?></div>
        	<div class="input-group value-text col-sm-4"><?php echo $role; ?></div>
		</div>
        <?php if($source && $source == 'customer') { ?>
            <div class="form-group clear">
                <div class="control-label col-sm-4"><?php echo $entry_first_name; ?></div>
                <div class="input-group value-text col-sm-4"><?php echo $first_name; ?></div>
            </div>
            <div class="form-group clear">
                <div class="control-label col-sm-4"><?php echo $entry_last_name; ?></div>
                <div class="input-group value-text col-sm-4"><?php echo $last_name; ?></div>
            </div>
            <div class="form-group clear">
                <div class="control-label col-sm-4"><?php echo $entry_email; ?></div>
                <div class="input-group value-text col-sm-4"><?php echo $email; ?></div>
            </div>
        <?php } ?>
        <?php foreach ($form['fields'] as $name => $field) { ?>
            <div class="form-group clear <?php echo ${'error_'.$name} ? 'has-error' : ''; ?>">
                <label class="control-label col-sm-4"><?php echo ${'entry_'.$name}; ?></label>
                <div class="input-group col-sm-7">
                    <?php echo $field; ?> 
                </div>
                <span class="help-block"><?php echo ${'error_'.$name}; ?></span>
            </div>		
        <?php } ?>
        <div class="form-group clearfix">
            <div class="control-label col-sm-4"></div>
            <div class="input-group col-sm-4">
                <div class="pull-left">
                    <?php echo $form['submit']; ?>
                </div>
            </div>
        </div>
        <?php if(!$source) { ?>
            <div class="form-group clearfix">
                <div class="control-label col-sm-4"></div>
                <div class="input-group col-sm-4">
                    <div class="pull-left">
                        <a href="<?php echo $password_link; ?>"><?php echo $text_change_password; ?></a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</form>


<h3><?php echo $text_notifications; ?></h3>
	<div id="notifications" class="container-fluid table-responsive">
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
                        <td class="align_center"><a href="<?php echo $notify['remove_url']; ?>" alt="Remove" title="Remove" class="btn btn-xs btn-default" data-toggle="confirmation" data-singleton="true" data-placement="left"><i class="fa fa-trash-o fa-fw"></i></a></td>
                    </tr>
            	<?php } ?>
        	<?php }else{ ?>
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
	$(document).ready(function(){
		var tz = jstz.determine();
		var zone = $('#blogUEFrm_users_tz').val();
		if(zone == 0) {
			$('#blogUEFrm_users_tz').val(tz.name());
		}
		$('[data-toggle=confirmation]').confirmation();
	});

//--></script>