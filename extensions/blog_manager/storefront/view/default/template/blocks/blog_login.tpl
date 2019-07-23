
<div class="sidewidt">
	<div id="login_form_anchor"></div>
    
    <?php if ($customer_logged) { ?>
        <?php if ( $block_framed ) { ?>
            <div class="block_frame block_frame_<?php echo $block_details['block_txt_id']; ?>" id="block_frame_<?php echo $block_details['block_txt_id'] . '_' . $block_details['instance_id'] ?>">
                <div class="welcome-text"><?php echo $text_welcome . ' ' . $customer_name; ?></div>
        <?php } ?>
			<ul class="nav list-group side_list">     
                <li><a href="<?php echo $settings_link; ?>"><?php echo $text_edit_settings; ?></a></li>
                 <li> <a href="<?php echo $notification_link; ?>"><?php echo $text_notifications; ?></a></li>
                <?php echo $this->getHookVar('blog_user_menu_item'); ?>
                
                
                <?php if($logout_link) { ?>
                    <li><a href="<?php echo $logout_link; ?>"><?php echo $text_logoff; ?></a></li>
                <?php } ?>        
                
            </ul>
                
    	<?php if ( $block_framed ) { ?>
			</div>
		<?php } ?>
    <?php }else{ ?> 
    	<?php if ( $block_framed ) { ?>
			<div class="block_frame block_frame_<?php echo $block_details['block_txt_id']; ?>" id="block_frame_<?php echo $block_details['block_txt_id'] . '_' . $block_details['instance_id'] ?>">
             	<h2 class="blog_login_heading"><?php echo $heading_title; ?></h2>
			<?php } ?>
                 <div class="blog-side-login-box">
                    <div class="login-error"></div>
                    <?php echo $form['form_open']; ?>
                        <?php echo $form['loginname']?>
                        <?php echo $form['password']?>
                        <?php echo $form['source']?>
                        <a href="<?php echo $login_submit->href; ?>" class="btn btn-default mr10 <?php echo $login_submit->attr; ?>" title="<?php echo $login_submit->text ?>">
                            <i class="fa fa-lock"></i>
                            <?php echo $login_submit->text; ?> 
                        </a>
                    </form>
                    <div class="login-message clear">
                        <a href="<?php echo $forgotten_pass; ?>" title="<?php echo $text_forgot_password; ?>"><?php echo $text_forgot_password; ?></a>
                        <a href="<?php echo $register; ?>" title="<?php echo $text_register; ?>"><?php echo $text_register; ?></a>
                    </div>
                </div>
            
                
    		<?php if ( $block_framed ) { ?>
			</div>

		<?php } ?>
  	<?php } ?>
</div>