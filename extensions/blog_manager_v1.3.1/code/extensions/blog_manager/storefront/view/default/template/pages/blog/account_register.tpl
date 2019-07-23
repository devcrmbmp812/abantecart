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

 <h3><?php echo $text_form_heading; ?></h3>
   <br /><br />     
 
        <?php foreach ($form['fields'] as $name => $field) { ?>
        	<?php if ($name == 'captcha') { ?>
            	<div class="form-group clear <?php if ($error_captcha) echo 'has-error'; ?>">
                    <?php if ($form['fields']['captcha']->type == 'recaptcha') { ?>
                    	<label class="control-label col-sm-4"></label>
                    <?php } else { ?>
                    	<label class="control-label col-sm-4"><?php echo $entry_captcha; ?></label>
                    <?php } ?>
                    <div class="input-group col-sm-7">
                        <?php echo $form['fields']['captcha']; ?>
                    </div>
                    <span class="help-block"><?php echo $error_captcha; ?></span>
                </div>
            <?php }else{ ?>
                <div class="form-group clear <?php echo ${'error_'.$name} ? 'has-error' : ''; ?>">
                    <label class="control-label col-sm-4"><?php echo ${'entry_'.$name}; ?></label>
                    <div class="input-group col-sm-7">
                        <?php echo $field; ?>
                    </div>
                    <div class="help-block"><?php echo ${'error_'.$name}; ?></div>
                </div>		
          	<?php } ?>
        <?php } ?>	
    </div>
    
    <div class="contentpanel clearfix">
    	<div class="control-label col-sm-4"></div>
        <div class="col-md-4 col-sm-7">
            <div class="pull-left">
                <?php echo $form['cancel']; ?>
                <?php echo $form['submit']; ?>
            </div>
        </div>
    </div>
</form>



<script type="text/javascript"><!--
	$('#blogRegFrm_submit').on('click', function(event) {
		event.preventDefault();
		$("#blogRegFrm").submit();
	});
	$('#blogRegFrm_cancel').on('click', function(event) {
		event.preventDefault();
		$("#blogRegFrm")[0].reset();
	});

//--></script>