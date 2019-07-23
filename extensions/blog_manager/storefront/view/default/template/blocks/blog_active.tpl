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
            foreach($active_links as $link){ 
                if ($count <= $max) { 
                    echo '<li class="blog_active_item ' . ($hide == 'true' ? 'b_active_hide' : "") .'"'  . ($hide == 'true' ? 'style="display:none;"' : "") .'> <a href="' . $link['href'] .'">' . $link['title'] .'</a></li>';
                     $count++;
                    if($count == $limit +1) {
                        $limit_met = $limit;
                        $hide = 'true';
                    }
                }
            }
            if($limit == $limit_met && $limit < $max) { ?>
                <span class="b_active_more"><a class="b_active_more_but" href="#"><?php echo $more; ?></a></span>
                <span class="b_active_less"><a class="b_active_less_but" href="#"><?php echo $less; ?></a></span>
           <?php } ?>
    </ul>
   	<?php if ( $block_framed ) { ?>
		</div>
	<?php } ?>
</div>
<script type="text/javascript"><!--
$('document').ready(function () {
	$('.b_active_less').hide();
	$('.b_active_more_but').on('click', function(event) {
		event.preventDefault();
		$('.b_active_hide').removeProp("style");
		$('.b_active_more').hide();
		$('.b_active_less').show();
	});
	$('.b_active_less_but').on('click', function(event) {
		event.preventDefault();
		$('.b_active_hide').hide();
		$('.b_active_more').show();
		$('.b_active_less').hide();
	});
});

//--></script>