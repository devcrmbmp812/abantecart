
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
            foreach($blog_category_list as $item){
            	$indent = '';
                if ($count <= $max || $item['level'] > 0) { 
                    $cname = $item['blog_category_id']==$selected_category_id ? '<b>'.$item['name'].'</b>' : $item['name'];
         			if($item['level'] > 0) {
                    	$indent = 'style="margin-left: ' . ($item['level'] * 10) .'px"';
                    }
                    echo '<li ' .($hide == 'true' ? 'class="b_category_hide"' : "") . ' ' . $indent .'><a href="'.$item['href'].'">'.$cname. '</a></li>';
                    
                    if($item['level'] == 0) {
                        $count++;
                        if($count == $limit +1) {
                            $limit_met = $limit;
                            $hide = 'true';
                        }
                    }
                } 
            }
            if($limit == $limit_met && $limit < $max) { ?>
                <span class="b_category_more"><a class="b_category_more_but" href="#"><?php echo $more; ?></a></span>
                <span class="b_category_less"><a class="b_category_less_but" href="#"><?php echo $less; ?></a></span>
          <?php } ?>

    </ul>
  	<?php if ( $block_framed ) { ?>
		</div>
	<?php } ?>
</div>

<script type="text/javascript"><!--
$('document').ready(function () {
	$('.b_category_hide').css("display","none");
	$('.b_category_less').css("display","none");
	$('.b_category_more_but').on('click', function(event) {
		event.preventDefault();
		$('.b_category_hide').css("display","block");
		$('.b_category_more').hide();
		$('.b_category_less').show();
	});
	$('.b_category_less_but').on('click', function(event) {
		event.preventDefault();
		$('.b_category_hide').css("display","none");
		$('.b_category_more').show();
		$('.b_category_less').hide();
	});
});

//--></script>