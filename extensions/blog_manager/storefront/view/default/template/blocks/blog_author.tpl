<div class="sidewidt">
    <?php if ( $block_framed ) { ?>
        <div class="block_frame block_frame_<?php echo $block_details['block_txt_id']; ?>" id="block_frame_<?php echo $block_details['block_txt_id'] . '_' . $block_details['instance_id'] ?>">
        <h2 class="heading2"><span><?php echo $heading_title; ?></span></h2>
    <?php } ?>
            
    <ul class="nav list-group side_list">
        <?php 
            $count = 1;
            $limit_met = 0;
            $hide = 'false';
            foreach($author_links as $link){ 
                if ($count <= $max) { 
                    echo '<li class="author_name_list ' . ($hide == 'true' ? 'b_author_hide' : "") .'"'  . ($hide == 'true' ? 'style="display:none;"' : "") .'> <a href="' . $link['href'] .'">' . $link['author_name'];
                    
                    	echo ' (' . $link['entry_count'] . ')';
                    
                     echo '</a></li>';
                     $count++;
                    if($count == $limit +1) {
                        $limit_met = $limit;
                        $hide = 'true';
                    }
                }
            }
            if($limit == $limit_met && $limit < $max) { ?>
                <span class="b_author_more"><a class="b_author_more_but" href="#"><?php echo $more; ?></a></span>
                <span class="b_author_less"><a class="b_author_less_but" href="#"><?php echo $less; ?></a></span>
           <?php } ?>
    </ul>
    <?php if ( $block_framed ) { ?>
		</div>
	<?php } ?>
</div>


<script type="text/javascript"><!--
$('document').ready(function () {
	$('.b_author_less').hide();
	$('.b_author_more_but').on('click', function(event) {
		event.preventDefault();
		$('.b_author_hide').removeProp("style");
		$('.b_author_more').hide();
		$('.b_author_less').show();
	});
	$('.b_author_less_but').on('click', function(event) {
		event.preventDefault();
		$('.b_author_hide').hide();
		$('.b_author_more').show();
		$('.b_author_less').hide();
	});
	
});

//--></script>