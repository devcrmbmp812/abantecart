<h1 class="heading1">
  <span class="maintext"><?php echo $heading_title; ?></span>

</h1>


    <div style="display: none;" class="alert alert-error alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span class="e_message"></span>
    </div>


<?php echo $form['form_open']; ?>
    <div class="registerbox form-horizontal">
    	 <br />
         <br />
         
		 <?php foreach ($form['fields'] as $name => $field) { ?>
            <div class="form-group clear group_<?php echo $name; ?>">
                <label class="reg-label col-sm-4"><?php echo ${'entry_'.$name}; ?></label>
                <div class="input-group col-sm-4">
                    <?php echo $field; ?>
                </div>
                <div class="reg-label col-sm-4"></div>
                <div class="input-group col-sm-4">
                	<div class="login-error error_<?php echo $name; ?>"><?php echo ${'error_'.$name}; ?></div>
               	</div>
            </div>		
        <?php } ?>	
        <div class="form-group clear">
        	<div class="reg-label col-sm-4"></div>
            <div class="input-group col-sm-4">
            	<a href="<?php echo $forgotten_pass; ?>" title="<?php echo $text_forgot_password; ?>"><?php echo $text_forgot_password; ?></a>
                <a href="<?php echo $register; ?>" title="<?php echo $text_register; ?>"><?php echo $text_register; ?></a>
           	</div>
       	</div>
    </div>
    
    <div class="clearfix">
    	<div class="reg-label col-sm-4"></div>
        <div class="col-md-4 col-xs-4">
            <div class="pull-left">
                <a href="<?php echo $login_submit->href; ?>" class="btn btn-primary mr10 <?php echo $login_submit->attr; ?>" title="<?php echo $login_submit->text ?>">
                    <i class="fa fa-lock"></i>
                    <?php echo $login_submit->text; ?> 
                </a>
            </div>
        </div>
    </div>
</form>


<script type="text/javascript"><!--
$('document').ready(function () {

	$('.go_page_login').on('click', function(event) {
			event.preventDefault();
			var error = 0;
			var msg = '';
			
			if(!$('#pageloginFrm_loginname').val()) {
				error = 1;
				$('.group_loginname').addClass('has-error');
				$('.error_loginname').text('You must enter a username');
				
			}
			if(!$('#pageloginFrm_password').val()) {
				error = 1;
				$('.group_password').addClass('has-error');
				$('.error_password').text('You must enter a username');
				
			}
			if (!error) {
				processPageLogin();
			}
	});
    
});    

function processPageLogin() {
	var blog_home = '<?php echo $blog_home; ?>';
	$.ajax({
		type: 'POST',
		url: '<?php echo $login_url; ?>',
		dataType: 'json',
		data: $("#pageloginFrm").serialize(),
		success: function (data) {
			if(data['error']) {
				$('.alert-error').removeAttr("style");
				
				$('.e_message').text(data['error']);
			}else if (data['message']) {
				 
				window.location.href = blog_home;
			}
			
		}
	});
}

//--></script>