<?php 
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
class ControllerBlocksBlogTopMenu extends AController {
	public $data = array();
	protected $blog_category_id = 0;
	protected $bcat = array();
	protected $selected_root_id = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$btm = array();
		$this->loadModel('blog/blog');
		$this->loadLanguage('blocks/blog_top_menu');

		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		if($blog_info['top_menu_block_status']) {
			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$blog_home = $this->config->get('blog_ssl_url');
				$store_home = $this->config->get('config_ssl_url');
			} else {
				$blog_home = $this->config->get('blog_url');
				$store_home = $this->config->get('config_url');
			}
			if($blog_info['store_top_menu']) {
				$btm['store'] = array(
					'id' => 'store_top_link',
					'name' => $this->language->get('text_store'),
					'url' => $store_home 
				);
			}
			$btm['blog'] = array(
				'id' => 'blog_top_link',
				'name' => $this->language->get('blog_home'),
				'url' => $blog_home
			);
			//start author_list
			if($blog_info['author_list_top_menu'] && $blog_info['author_list_block_status']) {
				$this->loadLanguage('blocks/blog_author');
				$min = $blog_info['author_list_block_min'];
				$max = $blog_info['author_list_block_max'];
		
				$author = $this->model_blog_blog->getAuthors($max);
				if(count($author) >= $min) {
					$author_links = array();
					foreach ($author as $row) {
						$author_links[] = array(
							'name' => $row['author_name'],
							'url' => $this->blog_html->getBLOGSEOURL('blog/author','&blog_author_id=' . $row['blog_author_id'], '&encode')
						);
					}
				
					$btm['author'] = array(
							'id' => 'author_list_top_link',
							'name' => $this->language->get('blog_author_title'),
							'url' => '#',
							'list' => $author_links
					);
				}
			}//end author
			
			//start categories	
			if($blog_info['category_top_menu'] && $blog_info['category_block_status']) {
				$this->loadLanguage('blocks/blog_category');
				$all_categories = $this->model_blog_blog->getAllBlogCategories();
				$this->_buildCategoryTree($all_categories);
				$categories = $this->_buildNestedCategoryList();

				foreach($categories as $cat) {
					$child = '';
					if($cat['children']) {
						foreach($cat['children'] as $cat_child) { 
							$child[] = array( 
								'name' => $cat_child['name'], 
								'url' => $cat_child['href'] 
							); 
						} 
					}
					$list[] = array(
						'name' => $cat['name'],
						'url' => $cat['href'],
						'children' => $child
					);
				}
				$btm['categories'] = array(
					'id' => 'categories_top_link',
					'name' => $this->language->get('blog_category_title'),
					'url' => '#',
					'list' => $list
				);		
			}//end categories
			
			//start archive
			if($blog_info['archive_top_menu'] && $blog_info['archive_block_status']) {
				$this->loadLanguage('blocks/blog_archive');
				$min = $blog_info['archive_block_min'];
				$max = $blog_info['archive_block_max'];
		
				$archive_months = $this->model_blog_blog->getArchiveMonths($max);
				if(count($archive_months) >= $min) {
					$archive_links = array();
					foreach ($archive_months as $month) {
						$archive_links[] = array(
							'name' => $month['month'] . ' ' . $month['year'] . ' (' . $month['count'] . ')',
							'url' => $this->blog_html->getBlogArchiveURL($month['month_num'],$month['year'], '', $encode),
						);
					}
					$btm['archive'] = array(
							'id' => 'archive_top_link',
							'name' => $this->language->get('blog_archive_title'),
							'url' => '#',
							'list' => $archive_links
					);	
				}
			}//end archive
			
			//start popular
			if($blog_info['popular_top_menu'] && $blog_info['popular_block_status']) {
				$this->loadLanguage('blocks/blog_popular');
				$min = $blog_info['popular_block_min'];
				$max = $blog_info['popular_block_max'];
		
				$popular = $this->model_blog_blog->getPopularEntries($max);
				if(count($popular) >= $min) {
					$popular_links = array();
					foreach ($popular as $row) {
						$popular_links[] = array(
							'name' => $row['entry_title'],
							'url' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode')
						);
					}
				
					$btm['popular'] = array(
							'id' => 'popular_top_link',
							'name' => $this->language->get('blog_popular_title'),
							'url' => '#',
							'list' => $popular_links
					);
				}
			}//end popular
			
			//start active
			if($blog_info['active_top_menu'] && $blog_info['active_block_status']) {
				$this->loadLanguage('blocks/blog_active');
				$min = $blog_info['active_block_min'];
				$limit = $blog_info['active_block_limit'];
				$max = $blog_info['active_block_max'];

				$active = $this->model_blog_blog->getActiveEntries($max);
				if( count($active) >= $min) {
					$active_links = array();
					foreach ($active as $row) {
						$active_links[] = array(
							'name' => $row['entry_title'],
							'url' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode')
						);
					}
				
					$btm['active'] = array(
							'id' => 'active_top_link',
							'name' => $this->language->get('blog_active_title'),
							'url' => '#',
							'list' => $active_links
					);
				}
			}//end active
			
			//start feed
			if($blog_info['feed_top_menu']) {
				$this->loadLanguage('blocks/blog_feed');
				//rss2
				$feed_links[] = array(
						'name' => $this->language->get('text_rss2'),
						'url' => $this->blog_html->getBlogFeedURL('rss2')
				);
				//atom
				$feed_links[] = array(
						'name' => $this->language->get('text_atom'),
						'url' => $this->blog_html->getBlogFeedURL('atom')
				);
				
				
				$btm['feed'] = array(
						'id' => 'feed_top_link',
						'name' => $this->language->get('text_subscribe'),
						'url' => '#',
						'list' => $feed_links
				);
				
			}//end feed
			
			//start login
			if($blog_info['login_top_menu'] && $blog_info['blog_access'] == "restrict") {
				$this->loadLanguage('blocks/blog_login');
				if($blog_info['login_data'] == 'customer') {
					if ($this->customer->isLogged()) {
						$this->data['customer_logged'] = $this->customer->isLogged();
						$customer_id = $this->customer->getId();
						$user_data = $this->model_blog_blog->getCustBlogUserData($customer_id);
						
						$settings_link = $this->blog_html->getSecureURL('account/blog_settings');
						$notification_link = $this->blog_html->getSecureURL('account/blog_settings').'#notifications'; 
						if($blog_info['use_store_url'] == '1') {
							$logout_link = $this->blog_html->getSecureURL('blog/account/logout');
						}
					}elseif(isset($this->session->data['blog_user_logged'])) {
						$this->data['customer_logged'] = 'true'; 
						$settings_link = $this->blog_html->getSecureURL('blog/account/settings');
						$notification_link = $this->blog_html->getSecureURL('blog/account/settings').'#notifications';
						$logout_link = $this->blog_html->getSecureURL('blog/account/logout');
					}
					
					$setting_links[] = array(
						'name' => $this->language->get('text_edit_settings'),
						'url' => $settings_link
					);
					$setting_links[] = array(
						'name' => $this->language->get('text_notifications'),
						'url' => $notification_link
					);
					$setting_links[] = array(
						'name' => $this->language->get('text_logoff'),
						'url' => $logout_link ? $logout_link : ''
					);
					
					$btm['settings'] = array(
						'id' => 'settings_top_link',
						'name' => $this->language->get('text_settings'),
						'url' => '#',
						'list' => $setting_links
					);

				}elseif (isset($this->session->data['blog_user_logged'])) {
					$this->data['customer_logged'] = 'true';
					$settings_link = $this->blog_html->getSecureURL('blog/account/settings');
					$notification_link = $this->blog_html->getSecureURL('blog/account/settings').'#notifications';
					$logout_link = $this->blog_html->getSecureURL('blog/account/logout');
					
					$setting_links[] = array(
						'name' => $this->language->get('text_edit_settings'),
						'url' => $settings_link
					);
					$setting_links[] = array(
						'name' => $this->language->get('text_notifications'),
						'url' => $notification_link
					);
					$setting_links[] = array(
						'name' => $this->language->get('text_logoff'),
						'url' => $logout_link ? $logout_link : ''
					);
					
					$btm['settings'] = array(
						'id' => 'settings_top_link',
						'name' => $this->language->get('text_settings'),
						'url' => '#',
						'list' => $setting_links
					);

				}
				
				if(!$this->data['customer_logged']) {
					
					$btm['settings'] = array(
						'id' => 'blog_login_top_link',
						'name' => $this->language->get('text_blog_login'),
						'url' => $this->blog_html->getSecureURL('blog/account/login'),
					);
				}
			}//end login
			//start search
			if($blog_info['search_top_menu']) {
				$btm['search'] = array(
						'id' => 'search_top_link',
						'name' => $this->language->get('text_search'),
						'url' => $this->blog_html->getSecureURL('blog/blog/search')
				);
				
			}//end feed
		
			$this->data['btm'] = $btm;
			$this->view->batchAssign($this->language->getASet());
			$this->view->batchAssign($this->data);
			$this->processTemplate();
				
		}

		//init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);	
		
	}
	
	
	
	private function _buildCategoryTree($all_categories = array(), $parent_id=0, $bcat=''){
	
		$output = array();
		foreach($all_categories as $category){
			if($parent_id!=$category['parent_id']){ continue; }
			$category['bcat'] = $bcat ? $bcat.'_'.$category['blog_category_id'] : $category['blog_category_id'];
			$category['parents'] = explode("_",$category['bcat']);
			$category['level'] = sizeof($category['parents'])-1; //digin' level
			if($category['blog_category_id']==$this->blog_category_id){ //mark root
				$this->selected_root_id = $category['parents'][0];
			}
			$output[] = $category;
			$output = array_merge($output,$this->_buildCategoryTree($all_categories,$category['blog_category_id'], $category['bcat']));
		}
		if($parent_id==0){
			$this->data['all_blog_categories'] = $output; 
			$cutted_tree = array();
			foreach($output as $category){
				if($category['parent_id']!=0 && !in_array($this->selected_root_id,$category['parents'])){ continue; }
				$category['href'] = $this->blog_html->getBLOGSEOURL('blog/category', '&bcat='.$category['bcat'], '&encode');
				$cutted_tree[] = $category;
			}
			return $cutted_tree;
		}else{
			return $output;
		}
	}
	
	private function _buildNestedCategoryList($parent_id=0){
		/**
		 * @var $resource AResource
		 */
		$this->loadModel('blog/blog');
		 
		$output = array();
		foreach($this->data['all_blog_categories'] as $category){
			if( $category['parent_id'] != $parent_id ){ continue; }
			$category['children'] = $this->_buildNestedCategoryList($category['blog_category_id']);

			$category['href'] = $this->blog_html->getBLOGSEOURL('blog/category', '&bcat=' . $category['bcat'], '&encode');
			//mark current category
			if(in_array($category['blog_category_id'], $this->bcat)) {
				$category['current'] = true;
			}
			$output[] = $category;
		}
		return $output;
	}
}
