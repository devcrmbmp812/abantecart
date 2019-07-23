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
            foreach($archive_links as $link){ 
                if ($count <= $max) { 
                    echo '<li class="blog_archive_item ' . ($hide == 'true' ? 'b_archive_hide' : "") .'"'  . ($hide == 'true' ? 'style="display:none;"' : "") .'> <a href="' . $link['href'] .'">' . $link['month'] . ' ' . $link['year'] . ' ' . $link['count'] .'</a></li>';
                     $count++;
                    if($count == $limit +1) {
                        $limit_met = $limit;
                        $hide = 'true';
                    }
                }
            }
            if($limit == $limit_met && $limit < $max) { ?>
                <span class="b_archive_more"><a class="b_archive_more_but" href="#"><?php echo $more; ?></a></span>
                <span class="b_archive_less"><a class="b_archive_less_but" href="#"><?php echo $less; ?></a></span>
         <?php  } ?>
    </ul>
    <?php if ( $block_framed ) { ?>
		</div>
	<?php } ?>
</div>

<script type="text/javascript"><!--
$('document').ready(function () {
	$('.b_archive_less').hide();
	$('.b_archive_more_but').on('click', function(event) {
		event.preventDefault();
		$('.b_archive_hide').removeProp("style");
		$('.b_archive_more').hide();
		$('.b_archive_less').show();
	});
	$('.b_archive_less_but').on('click', function(event) {
		event.preventDefault();
		$('.b_archive_hide').hide();
		$('.b_archive_more').show();
		$('.b_archive_less').hide();
	});
});

//--></script>