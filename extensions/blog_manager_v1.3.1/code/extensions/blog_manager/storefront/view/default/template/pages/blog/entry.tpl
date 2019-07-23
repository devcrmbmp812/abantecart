<h1 class="heading1">
  	<span class="maintext"><?php echo $heading_title; ?></span>
</h1>

<div class="contentpanel">
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
    
 	<?php echo $this->getHookVar('p3_social_1'); ?>
    <div class="col-md-12 col-xs-12 mt20">
        <h3 class="entry-title underline"><?php echo $entry_title; ?></h3>
        <div class="author-block">
            <?php echo $text_entered; ?>
            <?php echo $text_on; ?>
            <?php echo $release_date; ?> 
            <?php echo $text_by; ?>
            <?php if ($author_info['show_author_page']) { ?>
                <a href="<?php echo $author_link; ?>"><?php echo $name; ?></a>
            <?php } else { ?>
                <?php echo $name; ?>
            <?php } ?>
        </div>
        <div class="comment-count-block pull-right">
            <?php if($allow_comment) { ?>
                <a class="comment_count go_comments" href="#">
                <?php if ($comments_count) { ?>
                    <?php echo $text_comments; ?> (<?php echo $comments_count; ?>) 
                    &nbsp;- <?php echo $text_join_conversation; ?>
                <?php }else{ ?>
                    <?php echo $text_be_first; ?>
                <?php } ?>
                </a>
            <?php }else{ ?>
                <?php echo $text_comments_off; ?>
            <?php } ?>  
        </div>
    </div>
    
    <?php echo $this->getHookVar('p3_social_3'); ?>
    
    
    <div class="entry-content col-md-12 col-xs-12 mt20">
        <?php if ($show_image) { ?>
            <div class="entry_image">
                <?php echo $thumb_html; ?>
            </div>
        <?php } ?>
        <?php echo $content; ?>
    </div> 
    
    <div class="col-md-12 col-xs-12 mt20">
        <?php if($allow_comment) { ?>   
        	<div class="comments-count-block pull-right">             	
                <a class="go_comments" href="#">
                    <?php if ($comments_count) { ?>
                        <?php echo $text_comments; ?> (<?php echo $comments_count; ?>) 
                        &nbsp;- <?php echo $text_join_conversation; ?>
                    <?php }else{ ?>
                        <?php echo $text_be_first; ?>
                    <?php } ?>
                </a>
            </div>
        <?php }else{ ?>
            <?php echo $text_comments_off; ?>
        <?php } ?>  
    </div>
    
    <?php echo $this->getHookVar('p3_social_4'); ?>
    <?php if($related_products) { ?> 
        <div class="col-md-12 col-xs-12">
            <?php if($product_lead) { ?><h3 class="related-title"><?php echo $product_lead; ?></h3><?php } ?>
            <ul class="thumbnails grid row list-inline related-product">
                <?php
                foreach ($related_products as $product) {
                    $item = array();
                    $item['image'] = $product['thumb']['thumb_html'];
                    $item['title'] = $product['name'];
                    $item['description'] = $product['model'];
                    $item['rating'] = ($product['rating']) ? "<img src='" . $this->templateResource('/image/stars_' . $product['rating'] . '.png') . "' alt='" . $product['stars'] . "' />" : '';
            
                    $item['info_url'] = $product['href'];
                    $item['buy_url'] = $product['add'];
            
                    if (!$display_price) {
                        $item['price'] = '';
                    }
            
                    $review = $button_write;
                    if ($item['rating']) {
                        $review = $item['rating'];
                    }
                ?>
                    <li class="col-md-3 col-sm-6 col-xs-12">
                        <div class="fixed_wrapper">
                            <div class="fixed">
                                <a class="prdocutname related-product-name" href="<?php echo $item['info_url'] ?>"
                                   title="<?php echo $item['title'] ?>"><?php echo $item['title'] ?></a>
                                <?php echo $this->getHookvar('product_listing_name_'.$product['product_id']);?>
                            </div>
                        </div>
                        <div class="thumbnail related-product-text">
                            <?php if ($product['special']) { ?>
                                <span class="sale tooltip-test"><?php echo $text_sale_label; ?></span>
                            <?php } ?>
                            <?php if ($product['new_product']) { ?>
                                <span class="new tooltip-test"><?php echo $text_new_label; ?></span>
                            <?php } ?>
                            <a href="<?php echo $item['info_url'] ?>"><?php echo $item['image'] ?></a>
            
                            <div class="shortlinks">
                                <a class="details" href="<?php echo $item['info_url'] ?>"><?php echo $button_view ?></a>
                                <?php if ($review_status) { ?>
                                    <a class="compare" href="<?php echo $item['info_url'] ?>#review"><?php echo $review ?></a>
                                <?php } ?>
                                <?php echo $product['buttons']; ?>
                            </div>
                            <div class="blurb"><?php echo $product['blurb'] ?></div>
                            <?php echo $this->getHookvar('product_listing_details0_'.$product['product_id']);?>
                            <?php if ($display_price && $blog_info['show_related_prices']) { ?>
                                <div class="pricetag jumbotron">
                                    <span class="spiral"></span>
                                    <?php if($product['call_to_order']){ ?>
                                        <a data-id="<?php echo $product['product_id'] ?>" href="#"
                                               class="btn call_to_order"><?php echo $text_call_to_order?>&nbsp;&nbsp;<i class="fa fa-phone"></i></a>
                                    <?php } else if ($product['track_stock'] && !$product['in_stock']) { ?>
                                        <span class="nostock"><?php echo $product['no_stock_text']; ?></span>
                                    <?php } else { ?>
                                        <a data-id="<?php echo $product['product_id'] ?>"
                                                                   href="<?php echo $item['buy_url'] ?>"
                                                                   class="productcart"><?php echo $button_add_to_cart ?></a>
                                    <?php } ?>
                                    <div class="price">
                                        <?php if ($product['special']) { ?>
                                            <div class="pricenew"><?php echo $product['special'] ?></div>
                                            <div class="priceold"><?php echo $product['price'] ?></div>
                                        <?php } else { ?>
                                            <div class="oneprice"><?php echo $product['price'] ?></div>
                                        <?php } ?>
                                    </div>
                                    <?php echo $this->getHookvar('product_listing_details1_'.$product['product_id']);?>
                                </div>
                            <?php } ?>
                        </div>
                    </li>
                <?php
                }
                ?>
            </ul>
		</div>
    <?php } ?>
    
    <?php if($related_category) { ?>          
        <div class="col-md-12 col-xs-12">
            <div class="related-category-block">
                <?php if($category_lead) { ?><h3 class="related-title"><?php echo $category_lead; ?></h3><?php } ?>
                <?php $c = $related_category_count;
                    $i = 1;
                	foreach($related_category as $category) { 
                    	$comma = ''; ?>
                    	<a href="<?php echo $category['link']; ?>"><?php echo $category['name']; ?></a>
                    <?php ($c!=$i ? $comma = ', ' : ''); if ($comma) { echo $comma; }	
                    $i++; 
                 } ?>
            </div>
        </div>
    <?php } ?>
    
    <?php if($posted_categories) { ?>
        <div class="col-md-12 col-xs-12 mt20">
            <div class="posted-category-box">
                <?php if ($view_count) {
                    echo $text_viewed . ' ' . $view_count . ' ' . $text_times;
                 } ?>
                <?php echo $text_posted_categories; ?>
                <?php 
                $c = $posted_category_count;
                $i = 1;
                foreach($posted_categories as $category) { 
                    $comma = ''; ?> 
                    <a href="<?php echo $category['link']; ?>"><?php echo $category['name']; ?></a>
                    <?php ($c!=$i ? $comma = ', ' : ''); if ($comma) { echo $comma; }	
                    $i++; 
                 } ?>
            </div>  
        </div>
    <?php } ?>
    
    <?php if($reference) { ?>
        <div class="col-md-12 col-xs-12 mt20">
        	<div class="entry-ref-box">
            	<class class="ref-text"><?php echo $reference; ?></span>  
            </div>             
        </div>        
    <?php } ?> 
    
    <?php if($copyright) { ?>
        <div class="col-md-12 col-xs-12 mt20">
        	<div class="entry-copyright-box">
            	<div class="copyright-text">
                	<span class="copyright-title-text"><?php echo $text_copyright_title; ?></span>
                	<?php echo $copyright; ?>
            	</div>
          	</div>             
        </div>        
    <?php } ?> 
    <?php if($button_prev || $button_next) { ?>
        <div class="button-block col-md-12 col-xs-12 mt20">
            <div class="col-md-6">
                <?php if($button_prev) { ?>
                    <a href="<?php echo $button_prev->href; ?>" class="btn btn-default mr10 pull-left" title="<?php echo $button_prev->text ?>">
                        <i class="fa fa-arrow-left"></i>
                        <?php echo $button_prev->text ?>
                    </a>  
                <?php } ?>	
            </div>
            <div class="col-md-6">
                <?php if($button_next) { ?>
                    <a href="<?php echo $button_next->href; ?>" class="btn btn-default mr10 pull-right" title="<?php echo $button_next->text ?>">
                        <?php echo $button_next->text; ?> 
                        <i class="fa fa-arrow-right"></i>
                    </a>  	
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    
    <?php if($related_entry) { ?>
        <div class="col-md-12 col-xs-12 mt20">
 			<div class="related-entry-box">
                <?php if($entries_lead) { ?><h3 class="related-title"><?php echo $entries_lead; ?></h3><?php } ?>
                <ul class="nav related-entry">
                    <?php foreach($related_entry as $related) { ?>
                        <li> <a href="<?php echo $related['link']; ?>"><?php echo $related['entry_title']; ?> </a></li>
                    <?php } ?>
                </ul>
       		</div>
        </div>
  	<?php } ?>
    
    <?php if($author_info['show_details']) { ?>  
        <div class="col-md-12 col-xs-12 mt20">
      		<div class="author-bio">
                <div class="author-name"><?php echo $text_about_author . ' ' . $author_info['name']; ?></div>
                <?php if($author_info['author_title']) { ?> <div class="author-subtext"><?php echo $author_info['author_title']; ?></div> <?php } ?>
                <?php if($author_info['author_description']) { ?><div class="author-detail"><?php echo html_entity_decode($author_info['author_description'], ENT_QUOTES, 'UTF-8'); ?></div> <?php } ?>
                <?php if($author_info['site_url'] && $author_info['show_author_link']) { ?> <div class="author-subtext"><?php echo $text_website; ?> <a href="<?php echo $author_info['site_url']; ?>" target="_blank"><?php echo $author_info['site_url']; ?></a></div> <?php } ?>
                <?php if($author_info['show_author_page']) { ?><div class="author-subtext"><a href="<?php echo $author_link; ?>"><?php echo $text_more_from_author; ?></a> </div> <?php } ?>
                
        	</div>
        </div>
    <?php } ?>
    <?php echo $this->getHookVar('p3_social_2'); ?>
    
    <?php if($allow_comment) { ?>
        <div class="col-md-12 col-xs-12 mt20" id="comments">
            <div class="comments-count-block col-md-6">
                <span class="comments-title pull-left"><?php echo $comments_count . ' ' . ($comments_count < 2 ? $text_comment : $text_comments); ?></span>
            </div>
            <?php if($comments) { ?>
                <div class="col-md-6">
                     <a href="<?php echo $comment_form_button->href; ?>" class="btn btn-default <?php echo $comment_form_button->attr; ?> pull-right" title="<?php echo $comment_form_button->text ?>">
                            <i class="fa fa-comment-o"></i>
                            <?php echo $comment_form_button->text; ?> 
                        </a> 
                    
                </div> 
            <?php } ?>
        </div>
        <div class="col-md-12 col-xs-12 mt20">
            <div class="comments-block">
                <ol class="comments-list">
                    <?php echo $comments; ?>
                </ol>
            </div>  
        </div>
        
        
      
            <div class="col-md-12 col-xs-12 mt20" id="reply-form">
                <div class="form-wrapper">
                	<div id="respond" class="respond_block">
                        
                        <h3 id="form-title"><?php echo $text_comment_form_title; ?></h3>
                        <?php if(isset($restrict) && $restrict && isset($blog_logged_in) && $blog_logged_in == 'false') { ?>
                            
                            <span id="login-message" class="login-message"><?php echo $text_must_login; ?></span>
                            
                            <div class="inline-login-error"></div>
                            <?php echo $l_form['form_open']; ?>
                                <?php foreach ($l_form['fields'] as $name => $field) { ?>
                                    <?php if($field->type != 'hidden') { ?>
                                        <div id="field_<?php echo $name; ?>" class="input-group afield">
                                            <?php echo $field; ?>
                                        </div>
                                    <?php }else{ ?>
                                        <div>
                                            <?php echo $field; ?>
                                        </div>
                                    <?php } ?>   
                                <?php } ?>
                                <a href="<?php echo $login_submit->href; ?>" class="btn btn-default mr10 <?php echo $login_submit->attr; ?>" title="<?php echo $login_submit->text ?>">
                                    <i class="fa fa-lock"></i>
                                    <?php echo $login_submit->text; ?> 
                                </a>
                            </form>
                            
                        
                        <?php } ?>
                            <?php echo $form['form_open']; ?>
                                <?php foreach ($form['fields'] as $name => $field) { ?>
                                    <?php if($field->type != 'hidden') { ?>
                                        <div id="field_<?php echo $name; ?>" class="input-group afield">
                                            <span class="form-label"><?php echo ${'entry_' . $name}; ?></span><br />
                                            <span id="error-<?php echo $name; ?>" class="comment-error"><?php echo ${'form_error_' . $name}; ?></span>
                                            <?php echo $field; ?>
                                        </div>
                                    <?php }else{ ?>
                                        <div>
                                            <?php echo $field; ?>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                                <?php if($comment_policy) { ?>
                                    <div class="comment-policy-block"> 
                                        <?php echo $comment_policy; ?>
                                    </div>
                                <?php } ?>
                                <div class="form-button-block">
                                    <div class="pull-left">
                                        <?php if($all_notify_on) { ?>
                                            <span id="notify_all" class="notification"><?php echo $form['bottom']['notification_all']; ?> <?php echo $text_notification_all; ?></span>
                                            <?php if($reply_notify_on) { echo '<br />'; } ?>
                                        <?php } ?>
                                        <?php if($reply_notify_on) { ?>
                                            <span id="notify_reply" class="notification"><?php echo $form['bottom']['notification_reply']; ?> <?php echo $text_notification_reply; ?></span>
                                        <?php } ?>
                                    </div>
                                    <div class="pull-right">
                                        <?php echo $form['cancel']; ?>
                                        <?php echo $form['submit']; ?>
                                    </div>
                                </div>
                            </form> 
      
                   	</div>
               </div> 
                
         
        </div>
    <?php } ?>
    <?php if($disclaimer) { ?>
        <div class="disclaimer col-md-12 col-xs-12 mt20">
            <?php echo $disclaimer; ?>
        </div>
    <?php } ?>
</div>
<?php echo $this->getHookVar('hk_social_js'); ?>
<script type="text/javascript"><!--
$('document').ready(function () {

	<?php if(isset($restrict) && $restrict && isset($blog_logged_in) && $blog_logged_in == 'false') { ?>
		$('#blogCommentFrm').hide();
	<?php } ?>
	
});

//--></script>

