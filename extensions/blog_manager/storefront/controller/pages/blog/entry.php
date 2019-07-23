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
class ControllerPagesBlogEntry extends AController {
	public $data = array();
	public $error = array();
	
	public function main() {
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$base = $this->config->get('https_blog_server');
		} else {
			$base = $this->config->get('http_blog_server');
		}

		$this->loadLanguage('blog/blog');
		$this->loadLanguage('blocks/blog_login');
		$this->loadModel('blog/blog');
		
		$this->document->resetBreadcrumbs();
		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('index/home'),
        	'text'      => $this->language->get('text_store'),
        	'separator' => FALSE
      	 ));
		 
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		$page_id = 3;
	
		$blog_info = $this->model_blog_blog->getBlogSettings();		
		$this->data['blog_info'] = $blog_info;
		$max_image_width = $this->data['blog_info']['blog_entry_image_width'];
		$max_image_height = $this->data['blog_info']['blog_entry_image_height'];
		
		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
	
		if($this->registry->get('session')->data['blog_user_path'] == 'category') {
			$this->request->get['bcat'] = $this->registry->get('session')->data['blog_category'];	
		}
		
		if(!isset($this->request->get['bcat']) && isset($this->request->get['blog_category_id']) ){
			$this->request->get['bcat'] = $this->request->get['blog_category_id'];
		}

		if (isset($this->request->get['bcat']) && $this->request->get['bcat']) {
			$bcat = '';
		
			$parts = explode('_', $this->request->get['bcat']);
			if ( count($parts) == 1 ) {
				//see if this is a category ID to sub category, need to build full path
				$parts = explode('_', $this->model_blog_blog->buildPath($this->request->get['bcat']));
			}		
			foreach ($parts as $bcat_id) {
				$blog_category_info = $this->model_blog_blog->getBlogCategory($bcat_id);
				if ($blog_category_info) {
					if (!$bcat) {
						$bcat = $bcat_id;
					} else {
						$bcat .= '_' . $bcat_id;
					}
				}
				$this->document->addBreadcrumb( array ( 
					'href'      => $this->blog_html->getBLOGSEOURL('blog/category','&bcat=' . $bcat, '&encode'),
					'text'      => $this->language->get('text_category_title') . ' ' . $blog_category_info['name'],
					'separator' => $this->language->get('text_separator')
				 ));
			}		
		
			$blog_category_id = array_pop($parts);
		} else {
			$blog_category_id = 0;
		}
	
		if($this->registry->get('session')->data['blog_user_path'] == 'archive') {
			$blog_query_string = str_replace('&amp;', '&', $this->registry->get('session')->data['blog_query_string']);
			$parts = explode('&',$blog_query_string);
			foreach($parts as $part) {
				$var = explode('=', $part);
				if ($var[0] == 'm') {
					$m = $var[1];
				}elseif($var[0] == 'y') {
					$y = $var[1];
				}
			}
			$month = $this->_monthName($m);
			$this->document->addBreadcrumb( array ( 
        		'href'      => $this->blog_html->getBlogArchiveURL($m, $y, '&encode'),
        		'text'      => $this->language->get('text_archive') . ' - ' . $month . ' ' .$y,
        		'separator' => $this->language->get('text_separator')
      		 ));
		}

		$blog_entry_id = $this->request->get['blog_entry_id'];
		
		if (isset ($this->session->data['warning'])) {
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data['error_warning'] = '';
			$this->data['error'] = '';
		}
		if (isset ($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			$this->session->data['success'] = '';
		} else {
			$this->data ['success'] = '';
		}
		
		$this->data['error'] = $this->error;

		if ($blog_entry_id) {
			
			$blog_entry_info = $this->model_blog_blog->getBlogEntry($blog_entry_id);
			$this->data['blog_entry_info'] = $blog_entry_info;
			
			foreach ($blog_entry_info as $key => $data) {
				
				if ($key == 'content' || $key == 'entry_intro') {
					$this->data[$key] = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
				}elseif($key == 'release_date') {
					$this->data[$key] = dateISO2Display($data, $this->language->get('date_format_release'));
				}else{
					$this->data[$key] = $data;
				}
			}
			$this->data['author_link'] = $this->blog_html->getBLOGSEOURL('blog/author','&blog_author_id=' . $blog_entry_info['blog_author_id'], '&encode');
			if(!$this->data['name']) {
				$this->data['name'] = $blog_info['owner'];
			}
			$views = $this->model_blog_blog->recordView($blog_entry_id);
			
			if($blog_info['show_entry_view_count'] && $views) {
				$this->data['view_count'] = $views;
			}
			
      		$this->document->addBreadcrumb( array ( 
        		'href'      => $this->blog_html->getBLOGSEOURL('blog/entry', '&blog_entry_id=' . $blog_entry_id .'', false),
        		'text'      => $blog_entry_info['entry_title'],
        		'separator' => $this->language->get('text_separator')
      		 ));
				  		
			$this->document->setTitle( $blog_entry_info['entry_title'] );
			
			$this->document->addLink ( array(
				'rel'  => 'canonical',
				'href' => $this->blog_html->getBLOGCONSEOURL('blog/entry', '&blog_entry_id=' . $blog_entry_id .'')    
			));
			
			$resource = new AResource('image');
			
			$rp = $resource->getResources('blog_entry', $blog_entry_id);
			if($rp[0]['resource_path']) {
				$image_path = $this->data['base'] . 'resources/image/' . $rp[0]['resource_path']; 		
				list($act_width, $act_height) = getimagesize($image_path);
				$image_width = $act_width < $max_image_width ? $act_width : $max_image_width;
				$image_height = $act_height < $max_image_height ? $act_height : $max_image_height;	
				$sizes = array('main'  => array('width'  => $image_width, 'height' => $image_height));
				$image_main = $resource->getResourceAllObjects('blog_entry', $blog_entry_id, $sizes, 1, false);
				$image_main['main_html'] = '<img src="' . $image_main['main_url'] . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $result['entry_title'] . '" title="' . $result['entry_title'] . '" />';
			}
			
			// for blog author edit extension
			$this->session->data['blog_entry_id'] = $blog_entry_id;
			$this->extensions->hk_ProcessData($this, __FUNCTION__);
			if($this->session->data['image']) {
				$image_main = $this->session->data['image'];
				unset($this->session->data['image']);
			}
			unset($this->session->data['blog_entry_id']);
			// end
		
			$this->data['thumb_url'] = $image_main['main_url'];

			$this->document->setKeywords( $blog_entry_info['meta_keywords'] );
			$this->document->setDescription( $blog_entry_info['meta_description'] );
			$this->document->setTitle( $blog_entry_info['entry_title'] );
									
            $this->view->assign('heading_title', $blog_info['title'] );
			
			if(isset($blog_entry_info['use_image']) && $blog_entry_info['use_image']) {
				$this->data['show_image'] = 1;
				$this->data['thumb_html'] = $image_main['main_html'];
			}
			
			$this->data['entry_href'] = $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $blog_entry_id);
			$this->view->assign('disclaimer', html_entity_decode($blog_info['disclaimer'], ENT_QUOTES, 'UTF-8'));
			
			//get categories for article
			
			$posted_categories = $this->model_blog_blog->getPostedCategories($blog_entry_id);
			
			if ($posted_categories) {
				$this->data['posted_category_count'] = count($posted_categories);
				$this->data['posted_categories'] = $posted_categories;
			}
			
			
			//get related product categories - category name and link
			
			$related_category = $this->model_blog_blog->getRelatedCategories($blog_entry_id);
			
			if ($related_category) {
				$this->data['related_category_count'] = count($related_category);
				$this->data['related_category'] = $related_category;	
			}
			
			// get related products
			
			$related_products = $this->model_blog_blog->getRelatedProducts($blog_entry_id);
			
			if($related_products) {
				$this->loadModel('catalog/product');
				foreach($related_products as $p){
					$product_ids[] = (int)$p['product_id'];
				}
				
				$products_info = $this->model_catalog_product->getProductsAllInfo($product_ids);
	
				foreach ($related_products as $result) {

					$thumbnail = $resource->getMainThumb('products',
												 $result['product_id'],
												 (int)$this->config->get('config_image_product_width'),
												 (int)$this->config->get('config_image_product_height'),true);
					
					$rating = $products_info[$result['product_id']]['rating'];
					$special = FALSE;
					
					$discount = $products_info[$result['product_id']]['discount'];
					
					if ($discount) {
						$price = $this->currency->format($this->tax->calculate($discount, $result['tax_class_id'], $this->config->get('config_tax')));
					} else {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
					
						$special = $products_info[$result['product_id']]['special'];
					
						if ($special) {
							$special = $this->currency->format($this->tax->calculate($special, $result['tax_class_id'], $this->config->get('config_tax')));
						}					
					}
			
					$options = $products_info[$result['product_id']]['options'];
					
					if ($options) {
						$add = $this->html->getSEOURL('product/product','&product_id=' . $result['product_id'], '&encode');
					} else {
						if($this->config->get('config_cart_ajax')){
							$add = '#';
						}else{
							$add = $this->html->getSecureURL($cart_rt, '&product_id=' . $result['product_id'], '&encode');
						}
					}
					
					//check for stock status, availability and config
					$track_stock = false;
					$in_stock = false;
					$no_stock_text = $result['stock'];
					$total_quantity = 0;
					if ( $this->model_catalog_product->isStockTrackable($result['product_id']) ) {
						$track_stock = true;
						$total_quantity = $this->model_catalog_product->hasAnyStock($result['product_id']);
						//we have stock or out of stock checkout is allowed
						if ($total_quantity > 0 || $this->config->get('config_stock_checkout')) {
							$in_stock = true;
						}
					}
					
					$products[] = array(
						'product_id' 	=> $result['product_id'],
						'name'    	 	=> $result['name'],
						'blurb' 		=> $result['blurb'],
						'model'   	 	=> $result['model'],
						'rating'  	 	=> $rating,
						'stars'   	 	=> sprintf($this->language->get('text_stars'), $rating),
						'thumb'   	 	=> $thumbnail,
						'price'   	 	=> $price,
						'call_to_order'	=> $result['call_to_order'],
						'options' 	 	=> $options,
						'special' 	 	=> $special,
						'href'    	 	=> $this->html->getSEOURL('product/product','&path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'], '&encode'),
						'add'	  	 	=> $add,
						'description'	=> html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
						'track_stock' 	=> $track_stock,
						'in_stock'		=> $in_stock,
						'no_stock_text' => $no_stock_text,
						'total_quantity'=> $total_quantity,
					);
				}
				$this->data['related_products'] = $products;
	
				if ($this->config->get('config_customer_price')) {
					$display_price = TRUE;
				} elseif ($this->customer->isLogged()) {
					$display_price = TRUE;
				} else {
					$display_price = FALSE;
				}
				$this->view->assign('display_price', $display_price );
				
			}
			
			//get related articles  - article name and link (image?)
			$related_entry = $this->model_blog_blog->getRelatedEntries($blog_entry_id);
			
			if ($related_entry) {
				$this->data['related_entry'] = $related_entry;
			}
			
			//get author information
			
			$author_info = $this->model_blog_blog->getAuthorBio($blog_entry_id);
			$this->data['author_info'] = $author_info;	
			
			//previous / next buttons  blog, category, author or archive?
			if ($this->registry->get('session')->data['blog_user_path'] == 'archive') {
				$all_entries = $this->model_blog_blog->getArchiveBlogEntries(0,0, $m, $y,'id_only');
			}elseif($this->registry->get('session')->data['blog_user_path'] == 'author') {
				$all_entries = $this->model_blog_blog->getAuthorEntries($blog_entry_info['blog_author_id'], 0, 0,'id_only' );
			}else{ //then blog or category
				$all_entries = $this->model_blog_blog->getBlogEntries(0,0,$blog_category_id,'id_only');	
			}

			$i = array_search($blog_entry_id, $all_entries);
			$next = $i + 1;
			$prev = $i - 1;
			
			if ($prev >= 0) {
   				$prev_entry = $all_entries[$prev];
			}

			if ($next <= count($all_entries)) {
				$next_entry = $all_entries[$next]; 
			}

			if($prev_entry > 0) {
				$prev_entry_title = $this->model_blog_blog->getEntryName($prev_entry);
				$this->data['button_prev'] = HtmlElementFactory::create( array ('type' => 'button',
		                                               'name' => 'button_prev',
													   'href' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $prev_entry, '&encode'),
			                                           'text'=> $prev_entry_title,
			                                           'style' => 'button'));
			}
			if($next_entry > 0) {
				$next_entry_title = $this->model_blog_blog->getEntryName($next_entry);
				$this->data['button_next'] = HtmlElementFactory::create( array ('type' => 'button',
		                                               'name' => 'button_next',
													   'href' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' .$next_entry, '&encode'),
			                                           'text'=> $next_entry_title,
			                                           'style' => 'button'));
			}
			
			//check login 
			
			if($blog_info['blog_access'] == 'restrict') {
				$this->data['restrict'] = 'true';	
				if($blog_info['login_data'] == 'customer') {
					if ($this->customer->isLogged()) {
						$customer_id = $this->customer->getId();
						$user_data = $this->model_blog_blog->getCustBlogUserData($customer_id);
						if($user_data) {
							foreach($user_data as $key=>$value) {
								if($key=='role') {
									$this->session->data['blog_role'] = $value;
								}elseif($key=='users_tz') {
									$user_tz = $value;
								}else{
									$this->data[$key] = $value;
								}
							}	
						}
					}elseif(isset($this->session->data['blog_user_name'])){
						$user_data = $this->model_blog_blog->getUserData($this->session->data['blog_user_name']);
						if($user_data) {
							foreach($user_data as $key=>$value) {
								if($key=='role') {
									$this->data['blog_role'] = $value;
								}elseif($key=='users_tz') {
									$user_tz = $value;
								}else{
									$this->data[$key] = $value;
								}
							}	
						}
					}else{
						$this->data['blog_logged_in'] = 'false';	
					}
				}else{
					if(isset($this->session->data['blog_user_name'])){
						$user_data = $this->model_blog_blog->getUserData($this->session->data['blog_user_name']);
						if($user_data) {
							foreach($user_data as $key=>$value) {
								if($key=='role') {
									$this->data['blog_role'] = $value;
								}elseif($key=='users_tz') {
									$user_tz = $value;
								}else{
									$this->data[$key] = $value;
								}
							}	
						}
					}else{
						$this->data['blog_logged_in'] = 'false';	
					}
				}
				$this->data['inline_action'] = '';
				$form = new AForm();
				$form->setForm(array( 'form_name' => 'inlineloginFrm' ));
				$this->data['l_form']['id'] = 'inlineloginFrm';
				$this->data['l_form'][ 'form_open' ] = $form->getFieldHtml(array(
					   'type' => 'form',
					   'name' => 'inlineloginFrm',
					   'action' => $this->data['inline_action']
					   ));													   
				$this->data['l_form']['fields']['loginname'] = $form->getFieldHtml( array(
					   'type' => 'input',
					   'name' => 'loginname',
					   'placeholder' => $this->language->get('text_username'),
					   'style' => 'blog-input'
					   ));
				$this->data['l_form']['fields']['password'] = $form->getFieldHtml( array(
					   'type' => 'password',
					   'name' => 'password',
					   'placeholder' => $this->language->get('text_password'),
					   'style' => 'blog-input'
					   ));
				$this->data['l_form']['fields']['source'] = $form->getFieldHtml( array(
					   'type' => 'hidden',
					   'name' => 'source',
					   'value' => $this->data['source']
					   ));
																			   
				$this->data['login_submit'] = HtmlElementFactory::create( array (
								   'type' => 'button',
								   'text' => $this->language->get('button_login'),
								   'href' => '#',
								   'attr'=> 'go_inline_login',
								   'style' => 'button'));
								   
				$this->data['login_url'] = $this->blog_html->getURL('blog/proc_login');
				
			}
			
			//comments
			
			$comments = $this->model_blog_blog->getComments(0, $blog_entry_id, $user_tz, $blog_info['show_dt']);
			
			if($comments) {	
				$total_comments = '';
				
				for ($i=0; $i < count($comments); $i++) {
					$total_comments .= $this->prepareCommentOutput($comments[$i], $blog_category_id);		
				}
				$this->data['comments'] = $total_comments;
			}
			
			$this->data['comment_form_button'] = HtmlElementFactory::create( array ('type' => 'button',
												   'name' => 'comment_form_button',
												   'href' => '#',
												   'text'=> $this->language->get('button_make_comment'),
												   'attr'=> 'go_comment_form',
												   'style' => 'button'));
			
			
			
			
			$this->data['action'] = $this->blog_html->getSecureURL('blog/post','&bcat=' . $blog_category_id . '&blog_entry_id=' .$blog_entry_id, '&encode');
			//entry form
			$form = new AForm;
			$form->setForm(array(
				'form_name' => 'blogCommentFrm',
				'update' => $this->data['update'],
			));
	
			$this->data['form']['id'] = 'blogCommentFrm';
			$this->data['form']['form_open'] = $form->getFieldHtml(array(
					'type' => 'form',
					'name' => 'blogCommentFrm',
					'action' => $this->data['action'],
			));
			$this->data['form']['submit'] = $form->getFieldHtml(array(
					'type' => 'button',
					'name' => 'submit',
					'text' => $this->language->get('text_submit_comment'),
					'style' => 'btn btn-default',
			));
												   								   
			$this->data['form']['cancel'] = $form->getFieldHtml(array(
					'type' => 'button',
					'name' => 'cancel',
					'text' => $this->language->get('button_cancel'),
					'style' => 'btn btn-default',
			));
			$this->data['form']['fields']['username'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'username',
					'value' => $this->data['username'] ? $this->data['username'] : $this->request->post['author'],
					'style' => 'blog-input',
			));
			$this->data['form']['fields']['email'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'email',
					'value' => $this->data['email'] ? $this->data['email'] : $this->request->post['email'],
					'style' => 'blog-input',
			));
			if($blog_info['show_site_url']) {
				$this->data['form']['fields']['site_url'] = $form->getFieldHtml(array(
						'type' => 'input',
						'name' => 'site_url',
						'value' => $this->data['site_url'] ? $this->data['site_url'] : $this->request->post['site_url'],
						'style' => 'blog-input',
				));
			}
			$this->data['form']['fields']['comment_detail'] = $form->getFieldHtml(array(
					'type' => 'textarea',
					'name' => 'comment_detail',
					'value' => $this->request->post['comment_detail'] ? $this->request->post['comment_detail']: '',
					'attr' => 'class="blog-textarea"',
			));
			$this->data['form']['fields']['blog_entry_id'] = $form->getFieldHtml(array(
					'type' => 'hidden',
					'name' => 'blog_entry_id',
					'value' => $blog_entry_id
			));
			$this->data['form']['fields']['primary_comment_id'] = $form->getFieldHtml(array(
					'type' => 'hidden',
					'name' => 'primary_comment_id',
					'value' => $this->request->post['primary_comment_id'] ? $this->request->post['primary_comment_id'] : '',
			));
			$this->data['form']['fields']['parent_id'] = $form->getFieldHtml(array(
					'type' => 'hidden',
					'name' => 'parent_id',
					'value' => $this->request->post['parent_id']? $this->request->post['parent_id']: 0,
			));
			$this->data['form']['fields']['blog_user_id'] = $form->getFieldHtml(array(
					'type' => 'hidden',
					'name' => 'blog_user_id',
					'value' => $this->data['blog_user_id'],
			));
			
			
			$this->data['all_notify_on'] = $blog_info['notification_all'];
			$this->data['reply_notify_on'] = $blog_info['notification_on_reply'];
			
			$this->data['form']['bottom']['notification_all'] = $form->getFieldHtml( array(
                                                                    'type' => 'checkbox',
		                                                            'name' => 'notification_all',
																	'value' => 1,
		                                                            'checked' => ''));
			$this->data['form']['bottom']['notification_reply'] = $form->getFieldHtml( array(
                                                                    'type' => 'checkbox',
		                                                            'name' => 'notification_reply',
																	'value' => 1,
		                                                            'checked' => ''));
			
			
			$this->data['form_error_author'] = $this->language->get('error_username');
			$this->data['form_error_email'] = $this->language->get('error_email');
			$this->data['form_error_site_url'] = $this->language->get('error_site_url');
			$this->data['form_error_comment_detail'] = $this->language->get('error_comment_detail');
			
			if($blog_info['show_comment_policy']) { 
                $this->data['comment_policy'] =  html_entity_decode($blog_info['comment_policy'], ENT_QUOTES, 'UTF-8');
            } 	
			
			
			
			$this->view->setTemplate( 'pages/blog/entry.tpl' );
			
    	} else {
				
			$this->view->assign('heading_title', $blog_info['title']);
			$this->view->assign('sub_heading_title', $this->language->get('text_blog_entry_not_found'));
			$this->document->setTitle($blog_info['title']);
            $this->view->setTemplate( 'pages/blog/entry.tpl' );
		}
		
		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);

        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}
	
	public function prepareCommentOutput($array, $blog_category_id, $depth=1, $row = 'odd'){
	
			$output .= '<li id="comment-' . $array['blog_comment_id'] .'" class="comment ' . $row . ' thread-even depth-' . $array['depth']. ' parent">';
            $output .= '<div id="div-comment-' . $array['blog_comment_id'] .'" class="comment-body">';
            $output .= '<div class="comment-author vcard">';
            $output .= '<div class="fn">' . ($array['name_option'] == 1 ? $array['full_name'] : $array['username']) . ' ' . $this->language->get('text_says'). '</div>';
            if($array['site_url']) { $output .= '<div class="link"><a href="' . $array['site_url'] . '" target="_blank">' . $array['site_url'] . '</a></div>'; } 
            $output .= '<div class="comment-id">'. $this->language->get('text_comment_id'). ' ' . $array['blog_comment_id'] . '</div></div>';
			
			
            $output .= '<div class="comment-meta commentmetadata">' . $array['date_added'] . '</div>';
            $output .= '<p>' . html_entity_decode($array['comment'], ENT_QUOTES, 'UTF-8') .'</p>';	
			
			$reply_link = $this->blog_html->getBLOGSEOURL('blog/entry','&bcat=' . $blog_category_id . '&blog_entry_id=' .$array['blog_entry_id'], '&encode');
			$output .= "<div class='reply'><a class='comment-reply-link btn btn-default mr10' title='Reply to ".$array['username']."' href='".$reply_link."#respond' onclick='return addComment.moveForm(\"".$array['blog_comment_id']."\", \"respond\", \"".$array['blog_entry_id']."\", \"".$array['primary_comment_id']."\", \"".$this->language->get('text_form_reply_title')."\")' aria-label='Reply to ".$array['username']."'>";
			$output .= '<i class="fa fa-arrow-right"></i> Reply</a>';
            $output .= '</div></div>';	
	  	
			if (is_array($array['children'])){
				$children_array = $array['children'];
			
				$output .= '<ul class="children">';
				
			
				for ($i=0; $i < count($children_array); $i++) {
					if ( $row == 'odd') { $row = 'even'; }
					else { $row = 'odd'; }
					$output .= $this->prepareCommentOutput($children_array[$i], $blog_category_id, $array['depth'] + 1, $row); 
				}
				
				$output .= '</ul>';
			
		  	}
			
			$output .= '</li>';
			

		return $output;
	}
	
	public function truncateHTML($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			
			// if images - remove
			$text = preg_replace("/<img[^>]+\>/i", "(image) ", $text);
			
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
					// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
						unset($open_tags[$pos]);
						}
					// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) {
			// close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}
	
	private function _monthName($num) {
		if(!$num) { return false; }
		
		switch ($num) {
			case '1': 
				$month = 'January';
				break;
			case '2':
				$month = 'Febuary';
				break;
			case '3':
				$month = 'March';
				break;
			case '4':
				$month = 'April';
				break;
			case '5':
				$month = 'May';
				break;
			case '6':
				$month = 'June';
				break;
			case '7':
				$month = 'July';
				break;
			case '8':
				$month = 'August';
				break;
			case '9':
				$month = 'September';
				break;
			case '10':
				$month = 'October';
				break;
			case '11':
				$month = 'November';
				break;
			case '12':
				$month = 'December';
				break;
		}
		return $month;
	}
}
?>