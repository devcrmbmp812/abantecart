<style type="text/css">
.latest-text { font-style: italic; margin-left: 30px;}
.b_latest_more_but, .b_latest_less_but { font-size: 14px; margin-left: 30px;}
.b_latest_hide { display: none; }
.b_latest_less { display: none; }

</style>

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
            foreach($latest_links as $link){ 
                if ($count <= $max) { 
                    echo '<li class="blog_latest_item ' . ($hide == 'true' ? 'b_latest_hide' : "") .'"'  . ($hide == 'true' ? 'style="display:none;"' : "") .'><a href="' . $link['href'] .'">' . $link['title'] .'</a><span class="latest-text">' . $link['text'] . ' (' . $link['date_modified'] .')</span></li>';
                     $count++;
                    if($count == $limit +1) {
                        $limit_met = $limit;
                        $hide = 'true';
                    }
                }
            }
            if($limit == $limit_met && $limit < $max) { ?>
               	<span class="b_latest_more"><a class="b_latest_more_but" href="#"><?php echo $more; ?></a></span>
                <span class="b_latest_less"><a class="b_latest_less_but" href="#"><?php echo $less; ?></a></span>
           <?php } ?>
    </ul>
    <?php if ( $block_framed ) { ?>
		</div>
	<?php } ?>
</div>

<script type="text/javascript"><!--
$('document').ready(function () {
	$('.b_latest_less').hide();
	$('.b_latest_more_but').on('click', function(event) {
		event.preventDefault();
		$('.b_latest_hide').removeProp("style");
		$('.b_latest_more').hide();
		$('.b_latest_less').show();
	});
	$('.b_latest_less_but').on('click', function(event) {
		event.preventDefault();
		$('.b_latest_hide').hide();
		$('.b_latest_more').show();
		$('.b_latest_less').hide();
	});
	
});

//--></script>