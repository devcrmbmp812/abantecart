<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
        header ( 'Location: static_pages/' );
}
//delete menu item
$menu = new AMenu ( "admin" );
$menu->deleteMenuItem ("blog_manager");
$menu->deleteMenuItem ("blog_manage"); 
$menu->deleteMenuItem ("blog_details"); 
$menu->deleteMenuItem ("blog_settings"); 
$menu->deleteMenuItem ("blog_blocks"); 
$menu->deleteMenuItem ("blog_mng_comments"); 
$menu->deleteMenuItem ("blog_mng_users"); 
$menu->deleteMenuItem ("blog_entry");
$menu->deleteMenuItem ("blog_comment"); 
$menu->deleteMenuItem ("blog_author"); 
$menu->deleteMenuItem ("blog_category");
$menu->deleteMenuItem ("blog_user");

// delete layouts
$layout = new ALayoutManager();

$pages = $this->db->query("SELECT page_id FROM " . $this->db->table("pages") . " WHERE `controller` LIKE '%blog%' ");

foreach ($pages->rows as $page) {
	
	$query = $this->db->query("SELECT layout_id FROM " . $this->db->table("pages_layouts") . " WHERE page_id = '" . $page['page_id'] . "'");
	foreach ($query->rows as $layout_id) {
		$layout->deletePageLayoutById($page['page_id'], $layout_id['layout_id']);
	}
}

//delete blocks
$layout = new ALayoutManager();
$layout->deleteBlock('blog_active');
$layout->deleteBlock('blog_archive');
$layout->deleteBlock('blog_category');
$layout->deleteBlock('blog_feed');
$layout->deleteBlock('blog_latest');
$layout->deleteBlock('blog_login');
$layout->deleteBlock('blog_popular');
$layout->deleteBlock('blog_top_menu');
$layout->deleteBlock('blog_search');

$this->db->query("DELETE FROM " . $this->db->table("url_aliases") . " WHERE `query` LIKE 'blog%'");
$this->db->query("DELETE FROM " . $this->db->table("url_aliases") . " WHERE `query` = 'blog'");
$this->db->query("DELETE FROM " . $this->db->table("url_aliases") . " WHERE `query` = 'feed'");

