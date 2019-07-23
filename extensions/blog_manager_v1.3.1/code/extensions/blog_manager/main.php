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


include_once('core/blog_manager_hooks.php');

$controllers = array(
    'storefront' => array('pages/blog/blog',
					  'pages/blog/entry',
					  'pages/blog/archive',
					  'pages/blog/author',
					  'pages/blog/category',
					  'pages/blog/account',
					  'pages/account/blog_settings',
					  'blocks/blog_archive',
					  'blocks/blog_author',
					  'blocks/blog_category',
					  'blocks/blog_popular',
					  'blocks/blog_latest',
					  'blocks/blog_active',
					  'blocks/blog_login',
					  'blocks/blog_feed',
					  'blocks/blog_top_menu',
					  'blocks/blog_search',
					  'responses/blog/proc_login',
					  'responses/blog/post',
					  'responses/blog/feed'),
    'admin' => array( 'pages/design/blog_author',
					  'pages/design/blog_author_tabs',
					  'pages/design/blog_category',
					  'pages/design/blog_category_tabs',
					  'pages/design/blog_comment',
					  'pages/design/blog_entry',
					  'pages/design/blog_entry_tabs',
					  'pages/design/blog_manager',
					  'pages/design/blog_manager_tabs',
					  'pages/design/blog_user',
					  'responses/listing_grid/blog_author',
					  'responses/listing_grid/blog_category',
					  'responses/listing_grid/blog_comment',
					  'responses/listing_grid/blog_entry',
					  'responses/listing_grid/blog_user',
					  'responses/help/blog_help',
					  'responses/tool/content')
					  );

$models = array(
    'storefront' => array('blog/blog',
					  'tool/blog_seo_url'),
    'admin' => array( 'design/blog_author',
					  'design/blog_category',
					  'design/blog_comment',
					  'design/blog_entry',
					  'design/blog_manager',
					  'design/blog_user')
	);

$languages = array(
    'storefront' => array('blog/blog',
	                   'blog/account',
					   'blog/author',
					   'blocks/blog_archive',
					   'blocks/blog_category',
					   'blocks/blog_popular',
					   'blocks/blog_latest',
					   'blocks/blog_active',
					   'blocks/blog_author',
					   'blocks/blog_feed',
					   'blocks/blog_login',
					   'blocks/blog_top_menu',
					   'blocks/blog_search'),
    'admin' => array( 'blog_manager/blog_author',
					  'blog_manager/blog_category',
					  'blog_manager/blog_comment',
					  'blog_manager/blog_entry',
					  'blog_manager/blog_manager',
					  'blog_manager/blog_help',
					  'blog_manager/blog_user')
		);

$templates = array(
    'storefront' => array('pages/blog/blog.tpl',
					   'pages/blog/entry.tpl',
					   'pages/blog/archive.tpl',
					   'pages/blog/author.tpl',
					   'pages/blog/category.tpl',
					   'pages/blog/account_register.tpl',
					   'pages/blog/account_login.tpl',
					   'pages/blog/blog_success.tpl',
					   'pages/blog/account_settings.tpl',
					   'pages/blog/account_author.tpl',
					   'pages/blog/account_forgot.tpl',
					   'pages/blog/account_password.tpl',
					   'pages/blog/entry_edit.tpl',
					   'pages/blog/blog_search.tpl',
					   'blocks/blog_search.tpl',
					   'blocks/blog_search_top.tpl',
					   'pages/account/blog_settings.tpl',
					   'blocks/blog_archive.tpl',
					   'blocks/blog_author.tpl',
					   'blocks/blog_feed.tpl',
					   'blocks/blog_category.tpl',
					   'blocks/blog_popular.tpl',
					   'blocks/blog_latest.tpl',
					   'blocks/blog_active.tpl',
					   'blocks/blog_login.tpl',
					   'blocks/blog_top_menu.tpl'),
    'admin' => array( 'pages/design/blog_author_form.tpl',
					  'pages/design/blog_author_layout.tpl',
					  'pages/design/blog_author_list.tpl',
					  'pages/design/blog_author_tabs.tpl',
					  'pages/design/blog_category_form.tpl',
					  'pages/design/blog_category_layout.tpl',
					  'pages/design/blog_category_list.tpl',
					  'pages/design/blog_category_tabs.tpl',
					  'pages/design/blog_comment_form.tpl',
					  'pages/design/blog_comment_list.tpl',
					  'pages/design/blog_entry_form.tpl',
					  'pages/design/blog_entry_layout.tpl',
					  'pages/design/blog_entry_list.tpl',
					  'pages/design/blog_entry_tabs.tpl',
					  'pages/design/blog_manager_form.tpl',
					  'pages/design/blog_manager_block_settings_form.tpl',
					  'pages/design/blog_manager_settings_form.tpl',
					  'pages/design/blog_manager_tabs.tpl',
					  'pages/design/blog_manager_user.tpl',
					  'pages/design/blog_user_form.tpl',
					  'pages/design/blog_user_list.tpl',
					  'responses/help/blog_help.tpl',
					  'responses/tool/content.tpl',
					  'form/text_editor.tpl')
		);