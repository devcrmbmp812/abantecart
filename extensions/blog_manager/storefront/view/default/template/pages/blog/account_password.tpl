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
<?php if(!$sub_heading_title) {
    echo $sub_heading_title;
 } ?>
 
<?php echo $form['form_open']; ?>
    <div class="registerbox form-horizontal">

 <div><?php echo $text_change_password_message; ?></div>
   <br /><br /> 
   		
        <?php foreach ($form['fields'] as $name => $field) { ?>
            <div class="form-group clear <?php echo ${'error_'.$name} ? 'has-error' : ''; ?>">
                <label class="control-label col-sm-4"><?php echo ${'entry_'.$name}; ?></label>
                <div class="input-group col-sm-4">
                    <?php echo $field; ?>
                </div>
                <div class="help-block"><?php echo ${'error_'.$name}; ?></div>
            </div>		
        <?php } ?>
    
    
        <div class="contentpanel clearfix">
            <div class="reg-label col-sm-4"></div>
            <div class="col-md-4 col-xs-4">
                <div class="pull-left">
                    <?php echo $form['cancel']; ?>
                    <?php echo $form['submit']; ?>
                </div>
            </div>
        </div>
  	</div>
</form>


<script type="text/javascript"><!--
	$('#blogUPFrm_submit').on('click', function(event) {
		event.preventDefault();
		$("#blogUPFrm").submit();
	});
	$('#blogUPFrm_cancel').on('click', function(event) {
		event.preventDefault();
		$("#blogUPFrm")[0].reset();
	});

//--></script>