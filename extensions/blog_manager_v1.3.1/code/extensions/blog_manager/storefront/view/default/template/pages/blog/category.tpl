
<h1 class="heading1">
  	<span class="maintext"><?php echo $heading_title; ?></span>
  	<span class="subtext">
  	
    </span>
</h1>


<div class="blog container-fluid">
	<?php echo $this->getHookVar('p2_social_1'); ?>
    <?php if($description) { ?>
        <div class="cat_description col-md-12 col-xs-12 mt20">
            <?php echo $description; ?>
        </div>
    <?php } ?>
    
    <?php if ($sub_categories && $show_sub_categories) { ?>
        <ul class="thumbnails row">
            <?php for ($i = 0; $i < sizeof($sub_categories); $i++) { ?>
             <li class="col-md-2 col-sm-2 col-xs-6 align_center">
                <a href="<?php echo $sub_categories[$i]['href']; ?>">
                    <?php echo $sub_categories[$i]['thumb']['thumb_html']; ?>
                </a>
                <div class="mt10 align_center" style="height: 40px;">
                <a href="<?php echo $sub_categories[$i]['href']; ?>"><?php echo $sub_categories[$i]['name']; ?></a>
                </div>
            </li>
            <?php } ?>
        </ul>
	<?php } ?>
	
	<?php if(category_total) { ?>
        <?php echo $this->getHookVar('p2_social_3'); ?>
        
        <div class="col-md-12 col-xs-12 mt20">
            <ul class="entry-list">
                <?php foreach($entries as $entry) { ?>
                    <li class="clearfix">
                        <p class="entry-title"><a href="<?php echo $entry['href']; ?>"><?php echo $entry['entry_title']; ?></a></p>
                        <?php if($entry['use_intro'] && $entry['entry_intro']) {
                            $content = $entry['entry_intro'];
                         }else{ 
                            $content = $entry['content'];
                         } 
                         if(strpos($content, '<p>') === false) {
							$content = '<p>' . $content . '</p>';
						 } ?>
                        
                        <p class="author-block">
                            <?php echo $text_entered; ?> 
                            <?php echo $text_on; ?>
                            <?php echo $entry['release_date']; ?>
                            <?php echo $text_by; ?>
                            <?php if ($entry['show_author_page']) { ?>
                                <a href="<?php echo $entry['author_link']; ?>"><?php echo $entry['author_name']; ?></a>
                            <?php } else { ?>
                                <?php echo $entry['author_name']; ?>
                            <?php } ?>
                        </p>
                        <?php if ($entry['social_5']) {
                            foreach($entry['social_5'] as $ss=>$val) { ?>
                                <div class="share-block">
                                    <?php echo $val; ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="entry-block clearfix">
                            <?php if($entry['image']) { ?>
                                <div class="entry-image" style="width: <?php echo $max_container_width; ?>;">
                                    <a href="<?php echo $entry['href']; ?>">
                                    <?php echo $entry['image']['main_html']; ?>
                          
                                    </a>
                                </div>
                            <?php } ?>
                            <div class="entry-content">
                             
                                <p><?php echo $content; ?><a class="more" href="<?php echo $entry['href']; ?>"><?php echo $text_read_more; ?></a>
                                
                                <?php if ($entry['allow_comment'] && $entry['comments_count']) { ?>
                                    <a class="comment-count" href="<?php echo $entry['comment-href']; ?>"><?php echo $entry['comments_count'] . ' ' . $text_comments; ?></a>
                                <?php } ?> 
                                <?php if ($blog_info['show_entry_view_count'] && $entry['view_count']) { ?>
                                    <span class="view-count"><?php echo $entry['view_count'] . ' ' . $text_views; ?></span>
                                <?php } ?>
                                </p> 
                            </div>
                        </div>
                        <?php if ($entry['social_6']) {
                            foreach($entry['social_6'] as $ss=>$val) { ?>
                                <div class="share-block">
                                    <?php echo $val; ?>
                                </div>
                            <?php } ?>
                        <?php } ?> 
                    </li>
                <?php } ?>
            </ul>
            <?php echo $this->getHookVar('p2_social_4'); ?>    
            <div class="pagination col-md-12 col-xs-12 mt20">
                <?php echo $pagination_bootstrap; ?>
            </div>
        </div>
        <?php echo $this->getHookVar('p2_social_2'); ?>
        <?php if($disclaimer) { ?>
            <div class="disclaimer col-md-12 col-xs-12 mt20">
                <?php echo $disclaimer; ?>
            </div>
        <?php } ?>   
    <?php } ?>
</div>

<?php echo $this->getHookVar('hk_social_js'); ?>
