<ul class="nav nav-tabs nav-justified nav-profile">
	<li <?php echo ( $active == 'general' ? 'class="active"' : '' ) ?>><a href="<?php echo $link_general; ?>"><strong><?php echo $tab_form; ?></strong></a></li>
	<li <?php echo ( $active == 'layout' ? 'class="active"' : '' ) ?>><?php if($link_layout) { ?><a href="<?php echo $link_layout; ?>"><span><?php echo $tab_layout; ?></span></a><?php } ?></li>
	<?php echo $this->getHookVar('extension_tabs'); ?>
</ul>
