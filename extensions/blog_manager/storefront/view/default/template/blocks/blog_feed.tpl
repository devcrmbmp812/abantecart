
<div class="sidewidt">
    <?php if ( $block_framed ) { ?>
        <div class="block_frame block_frame_<?php echo $block_details['block_txt_id']; ?>" id="block_frame_<?php echo $block_details['block_txt_id'] . '_' . $block_details['instance_id'] ?>">
        <h2 class="heading2"><span><?php echo $heading_title; ?></span></h2>
    <?php } ?>
    <div class="side_rss_feed">
         <a href="<?php echo $default_feed_href; ?>"><?php echo $feed_image; ?></a> <a href="<?php echo $default_feed_href; ?>"><?php echo $text_subscribe; ?></a>
         <br /><span class="alt_feed"><a href="<?php echo $rss_feed_href; ?>"><?php echo $rss_feed; ?></a></span> / <span class="alt_feed"><a href="<?php echo $atom_feed_href; ?>"><?php echo $atom_feed; ?></a></span>
    </div>
    <?php if ( $block_framed ) { ?>
        </div>
    <?php } ?>
</div>

