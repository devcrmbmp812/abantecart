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

// get installed store_id and place in blog_settings
$store_id = $this->config->get('config_store_id');
$this->db->query("INSERT INTO " . $this->db->table("blog_settings") . " 
						  SET `key` = 'blog_store_id',
							  `value` = '" . (int)$store_id . "',
							  date_added = NOW()");

// add new menu items
$rm = new AResourceManager();
$rm->setType('image');

$language_id = $this->language->getContentLanguageID();
$data = array();
$data['resource_code'] = '<i class="fa fa-plus-square"></i>&nbsp;';
$data['name'] = array($language_id => 'Menu Icon Blog Manager');
$data['title'] = array($language_id => '');
$data['description'] = array($language_id => '');
$bm_resource_id = $rm->addResource($data);


//create main menu entry
$menu = new AMenu ( "admin" );
$menu->insertMenuItem ( array (  "item_id" => "blog_manager",
								 "parent_id"=>"design",
								 "item_text" => "blog_manager_name",
								 "item_url" => "",
								 "item_icon_rl_id" => $bm_resource_id,
								 "item_type"=>"extension",
								 "sort_order"=>"40")
								);

$result = $this->db->query ( "SELECT MAX(row_id) as rownum FROM " . $this->db->table("dataset_values") ."");
$row_id = ( int ) $result->row ['rownum'] + 1;

// submenu 
$data = array();
	$data['resource_code'] = '<i class="fa fa-cogs"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Settings');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_manage_resource_id = $rm->addResource($data);
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_manage',".$row_id."),
						(11,'blog_manage_name',".$row_id."),
						(12,'design/blog_manager',".$row_id."),
						(13,'blog_manager',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_manage_resource_id.",".$row_id.");");
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,1,".$row_id.");");	
				
//settings submenu
$row_id++;	

$data = array();
	$data['resource_code'] = '<i class="fa fa-database"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Details');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_details_resource_id = $rm->addResource($data);
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_details',".$row_id."),
						(11,'tab_form',".$row_id."),
						(12,'design/blog_manager',".$row_id."),
						(13,'blog_manage',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_details_resource_id.",".$row_id.");");
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,1,".$row_id.");");	
				
$row_id++;	

$data = array();
	$data['resource_code'] = '<i class="fa fa-cog"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Micro Settings');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_settings_resource_id = $rm->addResource($data);
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_settings',".$row_id."),
						(11,'tab_settings',".$row_id."),
						(12,'design/blog_manager/settings',".$row_id."),
						(13,'blog_manage',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_settings_resource_id.",".$row_id.");");
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,2,".$row_id.");");	

$row_id++;	

$data = array();
	$data['resource_code'] = '<i class="fa fa-book"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Block Settings');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_blocks_resource_id = $rm->addResource($data);
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_blocks',".$row_id."),
						(11,'tab_blocks',".$row_id."),
						(12,'design/blog_manager/blocks',".$row_id."),
						(13,'blog_manage',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_blocks_resource_id.",".$row_id.");");
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,3,".$row_id.");");	
		
$row_id++;	

$data = array();
	$data['resource_code'] = '<i class="fa fa-book"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Comment Settings');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_mng_comments_resource_id = $rm->addResource($data);
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_mng_comments',".$row_id."),
						(11,'tab_comments',".$row_id."),
						(12,'design/blog_manager/comments',".$row_id."),
						(13,'blog_manage',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_mng_comments_resource_id.",".$row_id.");");
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,4,".$row_id.");");	

$row_id++;	

$data = array();
	$data['resource_code'] = '<i class="fa fa-user-plus"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog User Settings');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_mng_users_resource_id = $rm->addResource($data);
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_mng_users',".$row_id."),
						(11,'tab_users',".$row_id."),
						(12,'design/blog_manager/users',".$row_id."),
						(13,'blog_manage',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_mng_users_resource_id.",".$row_id.");");
	
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,5,".$row_id.");");


// submenu continued		
$row_id++;	
	
$data = array();
	$data['resource_code'] = '<i class="fa fa-folder"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Manage Entries');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_entries_resource_id = $rm->addResource($data);
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_entry',".$row_id."),
						(11,'blog_entry_name',".$row_id."),
						(12,'design/blog_entry',".$row_id."),
						(13,'blog_manager',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_entries_resource_id.",".$row_id.");");

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,2,".$row_id.");");
				

$row_id++;
	
$data = array();
	$data['resource_code'] = '<i class="fa fa-comments"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Comments');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_comment_resource_id = $rm->addResource($data);
	
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_comment',".$row_id."),
						(11,'blog_comment_name',".$row_id."),
						(12,'design/blog_comment',".$row_id."),
						(13,'blog_manager',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_comment_resource_id.",".$row_id.");");

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,3,".$row_id.");");

$row_id++;
	
$data = array();
	$data['resource_code'] = '<i class="fa fa-bars"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Categories');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_category_resource_id = $rm->addResource($data);

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_category',".$row_id."),
						(11,'blog_category_name',".$row_id."),
						(12,'design/blog_category',".$row_id."),
						(13,'blog_manager',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_category_resource_id.",".$row_id.");");

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,4,".$row_id.");");				
				
$row_id++;
	
$data = array();
	$data['resource_code'] = '<i class="fa fa-users"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Authors');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_authors_resource_id = $rm->addResource($data);

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_author',".$row_id."),
						(11,'blog_authors_name',".$row_id."),
						(12,'design/blog_author',".$row_id."),
						(13,'blog_manager',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_authors_resource_id.",".$row_id.");");

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,5,".$row_id.");");
				

				
$row_id++;

$data = array();
	$data['resource_code'] = '<i class="fa fa-user"></i>&nbsp;';
	$data['name'] = array($language_id => 'Menu Icon Blog Users');
	$data['title'] = array($language_id => '');
	$data['description'] = array($language_id => '');
	$blog_user_resource_id = $rm->addResource($data);
					
$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_varchar`,`row_id`) 
				VALUES  (10,'blog_user',".$row_id."),
						(11,'blog_user_name',".$row_id."),
						(12,'design/blog_user',".$row_id."),
						(13,'blog_manager',".$row_id."),
						(15,'extension_core',".$row_id."),
						(40,".$blog_user_resource_id.",".$row_id.");");

$this->db->query("INSERT INTO " . $this->db->table("dataset_values") . " (`dataset_column_id`, `value_integer`,`row_id`) 
				VALUES  (14,6,".$row_id.");");
				

$layout = new ALayoutManager();
//get store template
$tmpl_id = $this->config->get('config_storefront_template'); //default or template name
//get default language
$default_language_id = $this->language->getDefaultLanguageID();
//get default layout_id
$result = $this->db->query("SELECT layout_id FROM " . $this->db->table("layouts") . " WHERE template_id = '" . $tmpl_id . "' AND layout_type = 0");
$default_layout_id = $result->row['layout_id'];


$new_page_names = array(
	array ('layout_name' => 'Main Blog Page', 
		'controller' => 'pages/blog/blog',
		'template_id' => $tmpl_id,
		'layout_type' => 1,
		'parent_page_id' => 0,
	),
	array ('layout_name' => 'Blog Article: Default', 
		'controller' => 'pages/blog/entry',
		'template_id' => $tmpl_id,
		'layout_type' => 1,
		'parent_page_id' => 0
	),
	array ('layout_name' => 'Blog Category: Default', 
		'controller' => 'pages/blog/category',
		'template_id' => $tmpl_id,
		'layout_type' => 1,
		'parent_page_id' => 0
	),
	array ('layout_name' => 'Blog Author: Default', 
		'controller' => 'pages/blog/author',
		'template_id' => $tmpl_id,
		'layout_type' => 1,
		'parent_page_id' => 0
	),
	array ('layout_name' => 'Blog Archive', 
		'controller' => 'pages/blog/archive',
		'template_id' => $tmpl_id,
		'layout_type' => 1,
		'parent_page_id' => 0
	),
	array ('layout_name' => 'Blog Account', 
		'controller' => 'pages/blog/account',
		'template_id' => $tmpl_id,
		'layout_type' => 1,
		'parent_page_id' => 0
	)	
);	

foreach ($new_page_names as $data) {
	
	$query = $this->db->query("SELECT page_id FROM " . $this->db->table("pages") . " WHERE controller = '" . $data['controller'] . "'");
	if(!$query->row['page_id']) {
		
		$data['page_descriptions'][$default_language_id]['name'] = $data['layout_name'];
	
		$layout_id = $layout->saveLayout($data);	
		$page_id = $layout->savePage($data);
		
		$this->db->query("INSERT INTO " . $this->db->table("pages_layouts") . " (`layout_id`, `page_id`) VALUES
			($layout_id, $page_id)");
		
		$layout->cloneLayoutBlocks($default_layout_id, $layout_id);
	}
	
}

$block_names = array('blog_active','blog_archive', 'blog_category', 'blog_feed', 'blog_latest', 'blog_login', 'blog_popular', 'blog_search', 'blog_author');

foreach($block_names as $block) {
	$block_info['block_txt_id'] = $block;
	$block_info['controller'] = 'blocks/' . $block;

	$block_info['templates'] = array( array('parent_block_txt_id'=>'column_left','template'=>'blocks/' . $block . '.tpl'),
								  array('parent_block_txt_id'=>'column_right','template'=>'blocks/' . $block . '.tpl'));
	if($block == 'blog_search') {
		$block_info['templates'][] = array('parent_block_txt_id'=>'header_bottom','template'=>'blocks/blog_search_top.tpl');
	}	

	$block_info['descriptions'] = array( array('language_name' => 'english','name' => ucwords(str_replace('_', ' ' , $block))));

	$layout->saveBlock($block_info);
	
}

$block_info['block_txt_id'] = 'blog_top_menu';
$block_info['controller'] = 'blocks/blog_top_menu';

$block_info['templates'] = array( array('parent_block_txt_id'=>'header_bottom','template'=>'blocks/blog_top_menu.tpl'));

$block_info['descriptions'] = array( array('language_name' => 'english','name' => 'Blog Top Menu'));

$layout->saveBlock($block_info);

//some database entries (need variable values)
$this->db->query("INSERT INTO " . $this->db->table("blog_category") . " (`parent_id`, `sort_order`, `status`, `date_added`)
			VALUES (0,0,1, now())");

$blog_category_id = $this->db->getLastId();
$this->db->query("INSERT INTO " . $this->db->table("blog_category_description") . " (`blog_category_id`, `language_id`, `name`)
			VALUES ( " . $blog_category_id . ", " . $default_language_id . ", 'Uncategorized')");

$this->db->query("INSERT INTO " . $this->db->table("url_aliases") . " (`query`, `keyword`, `language_id`)
			VALUES ('blog_category=" . $blog_category_id . "', 'uncategorized', '" . $default_language_id . "'),
				   ('blog', 'blog', '" . $default_language_id . "'),
				   ('feed', 'feed', '" . $default_language_id . "')");
//end





