<ul class="nav nav-tabs nav-justified nav-profile">
	<li <?php echo ( $active == 'details' ? 'class="active"' : '' ) ?>><a href="<?php echo $link_details; ?>"><strong><?php echo $tab_form; ?></strong></a></li>
    <li <?php echo ( $active == 'settings' ? 'class="active"' : '' ) ?>><a href="<?php echo $link_settings; ?>"><strong><?php echo $tab_settings; ?></strong></a></li>
    <li <?php echo ( $active == 'blocks' ? 'class="active"' : '' ) ?>><a href="<?php echo $link_blocks; ?>"><strong><?php echo $tab_blocks; ?></strong></a></li>
    <li <?php echo ( $active == 'comments' ? 'class="active"' : '' ) ?>><a href="<?php echo $link_comments; ?>"><strong><?php echo $tab_comments; ?></strong></a></li>
    <li <?php echo ( $active == 'users' ? 'class="active"' : '' ) ?>><a href="<?php echo $link_users; ?>"><strong><?php echo $tab_users; ?></strong></a></li>

	<?php echo $this->getHookVar('extension_tabs'); ?>
</ul>
