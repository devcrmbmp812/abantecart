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
class ControllerPagesBlogCategory extends AController {
	public $data = array();
	
	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadLanguage('blog/blog');

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

		$this->loadModel('blog/blog');
		$this->loadModel('tool/seo_url');  
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		$this->view->assign('disclaimer', html_entity_decode($blog_info['disclaimer'], ENT_QUOTES, 'UTF-8'));
		
		$this->data['blog_info'] = $blog_info;
		$max_image_width = $this->data['blog_info']['blog_entry_image_width'];
		$max_image_height = $this->data['blog_info']['blog_entry_image_height'];
		$this->data['max_container_width'] = $max_image_width . 'px';
		
		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
	
		if(!isset($this->request->get['bcat']) && isset($this->request->get['blog_category_id']) ){
			$this->request->get['bcat'] = $this->request->get['blog_category_id'];
		}
		
		if (isset($this->request->get['bcat'])) {
			$this->registry->get('session')->data['blog_user_path'] = 'category';
			$this->registry->get('session')->data['blog_category'] = $this->request->get['bcat'];
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
		
		$blog_category_total = $this->model_blog_blog->getTotalBlogCategoriesByBlogCategoryId($blog_category_id);
		$this->data['show_sub_categories'] = $blog_info['show_sub_categories'];
		
		if($blog_category_total) {
			$sub_categories = array();
        		
				$results = $this->model_blog_blog->getBlogCategories($blog_category_id);
				$blog_category_ids = array();
				foreach($results as $result){
					$blog_category_ids[] = (int)$result['blog_category_id'];
				}
		        //get thumbnails by one pass
		        $resource = new AResource('image');
		        $thumbnails = $resource->getMainThumbList(
					'blog_category',
					$blog_category_ids,
					$this->data['blog_info']['blog_category_image_width'],
					$this->data['blog_info']['blog_category_image_width'],
					false
				);

        		foreach ($results as $result) {
			        $thumbnail = $thumbnails[ $result['blog_category_id'] ];

					$sub_categories[] = array(
	                    'name'  => $result['name'],
	                    'href'  => $this->blog_html->getBLOGSEOURL('blog/category', '&bcat='.$result['blog_category_id'], '&encode'),
	                    'thumb' => $thumbnail
					);
        		}
                $this->view->assign('sub_categories', $sub_categories );
		}
		
		if ($blog_category_info) {
	
			if ($blog_category_info['page_title']) {
				$this->document->setTitle($blog_info['title'] . ': '  . $blog_category_info['page_title']);
			}else {
	  			$this->document->setTitle($blog_info['title'] . ': ' . $blog_category_info['name']);
			}
			$this->document->setKeywords($blog_category_info['meta_keywords']);
			$this->document->setDescription($blog_category_info['meta_description']);
			$this->document->addLink ( array(
				'rel'  => 'canonical',
				'href' => $this->blog_html->getBLOGCONSEOURL('blog/category','&bcat=' . $bcat)    
			));
			$this->data['page_url'] = $this->blog_html->getBLOGCONSEOURL('blog/category','&bcat=' . $bcat);
			
			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$base = $this->config->get('https_blog_server');
			} else {
				$base = $this->config->get('http_blog_server');
			}
		
			$heading_title = $this->language->get('text_category_title') . ' ' . $blog_category_info['name'];
			
            $this->view->assign('heading_title', $heading_title);
			 
			$this->view->assign('description', html_entity_decode($blog_category_info['description'], ENT_QUOTES, 'UTF-8') );

			
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else { 
				$page = 1;
			}	
			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
				$limit = $limit>10 ? 10 : $limit;
			} else {
				$limit = $blog_info['entries_per_main_page'];
			}
			$category_total = $this->model_blog_blog->getTotalBlogEntriesInCategory($blog_category_id); 

			$this->data['category_total'] = $category_total;
			
			if ($category_total) {
																				 
				$entries = array();
        		
				$entry_info = $this->model_blog_blog->getBlogEntries(($page - 1) * $limit, $limit, $blog_category_id);
				
				$resource = new AResource('image');

        		foreach ($entry_info as $result) {
					$author_name = $result['author_name'];
					$author_link = $this->blog_html->getBLOGSEOURL('blog/author','&blog_author_id=' . $result['blog_author_id'], '&encode');
					if(!$result['author_name']) {
						$author_name = $blog_info['owner'];
					}
					$image_main = '';
					$rp = $resource->getResources('blog_entry', $result['blog_entry_id']);
					if($rp[0]['resource_path']) {
						$image_path = $this->data['base'] . 'resources/image/' . $rp[0]['resource_path']; 
						list($act_width, $act_height) = getimagesize($image_path);
						$image_width = $act_width < $max_image_width ? $act_width : $max_image_width;
						$image_height = $act_height < $max_image_height ? $act_height : $max_image_height;	
						$sizes = array('main'  => array('width'  => $image_width, 'height' => $image_height));
						$image_main = $resource->getResourceAllObjects('blog_entry', $result['blog_entry_id'], $sizes, 1, false);
						$image_main['main_html'] = '<img src="' . $image_main['main_url'] . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $result['entry_title'] . '" title="' . $result['entry_title'] . '" />';
					}
					
					// for blog author edit extension
					$this->session->data['blog_entry_id'] = $result['blog_entry_id'];
					$this->extensions->hk_ProcessData($this, __FUNCTION__);
					if($this->session->data['image']) {
						$image_main = $this->session->data['image'];
						unset($this->session->data['image']);
					}
					unset($this->session->data['blog_entry_id']);
					// end
							
					$entry_href = $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $result['blog_entry_id']);
					
          			$entries[] = array(
						'blog_entry_id' 	=> $result['blog_entry_id'],
						'entry_title'   	=> $result['entry_title'],
						'use_intro'			=> $result['use_intro'],
						'entry_intro'		=> html_entity_decode($result['entry_intro'], ENT_QUOTES, 'UTF-8'),
						'content'			=> $this->truncateHTML(html_entity_decode($result['content'], ENT_QUOTES, 'UTF-8'),$blog_info['word_count_main'],'...'),
						'release_date' 		=> dateISO2Display($result['release_date'], $this->language->get('date_format_release')),
						'allow_comment' 	=> $result['allow_comment'],
						'author_name' 		=> $author_name,
						'author_link'		=> $author_link,
						'show_author_page' 	=> $result['show_author_page'],
						'comments_count' 	=> $result['comments_count'],
						'share' 			=> $blog_info['share'],
						'href'  			=> $entry_href,
						'comment-href'      => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $result['blog_entry_id'], '&encode').'#comments',
						'image'  			=> $image_main,
						'view_count'		=> $result['view']
					);
				}
				
				$this->data['entries'] = $entries;
				
				$this->view->assign( 'url', $this->blog_html->getURL('blog/category','&bcat=' . $bcat . ''));
				$pagination_url = $this->blog_html->getBLOGSEOURL('blog/category','&bcat=' . $bcat . '&page={page}' . '&limit=' . $limit, '&encode');

				$this->view->assign('pagination_bootstrap', HtmlElementFactory::create( array (
											'type' => 'Pagination',
											'name' => 'pagination',
											'text'=> $this->language->get('text_pagination'),
											'text_limit' => $this->language->get('text_per_page'),
											'total'	=> $category_total,
											'page'	=> $page,
											'limit'	=> $limit,
											'url' => $pagination_url,
											'style' => 'pagination')) 
									);

				$this->view->batchAssign( $this->data );
				$this->view->setTemplate( 'pages/blog/category.tpl' );
      		} else {
				
				$this->view->assign('no_articles', $this->language->get('text_blog_no_entries'));
				
        		$this->document->setTitle( $blog_info['title'] );

                $this->view->setTemplate( 'pages/blog/category.tpl' );
                $this->view->assign('blog_categories', array());

				$this->view->batchAssign( $this->data );
			}
			
    	} else {
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	
			
			if (isset($this->request->get['path'])) {	
	       		$this->document->addBreadcrumb( array ( 
   	    			'href'      => $this->blog_html->getBLOGSEOURL('blog/category','&blog_category_id=' . $this->request->get['blog_category_id'] . $url, '&encode'),
    	   			'text'      => $this->language->get('text_error'),
        			'separator' => $this->language->get('text_separator')
        		 ));
			}
				
			$this->document->setTitle( $this->language->get('text_error') );

      		$this->view->assign('heading_title', $this->language->get('text_error') );
            $this->view->assign('text_error', $this->language->get('text_error') );
            $continue = HtmlElementFactory::create( array ('type' => 'button',
		                                               'name' => 'continue_button',
			                                           'text'=> $this->language->get('button_continue'),
			                                           'style' => 'button'));
			$this->view->assign('button_continue', $continue);
      		$this->view->assign('continue',  $this->blog_html->getBLOGHOME('&encode') );

            $this->view->setTemplate( 'pages/error/not_found.tpl' );
		}

        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}
	
	public function truncateHTML($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			
			// if images - remove
			$text = preg_replace("/<img[^>]+\>/i", "", $text);
			
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
}