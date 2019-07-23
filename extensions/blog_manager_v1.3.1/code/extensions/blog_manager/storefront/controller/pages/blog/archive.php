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
class ControllerPagesBlogArchive extends AController {
	public $data = array();
	
	public function main() {
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadLanguage('blog/blog');
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
		
		$m = $this->request->get['m'];
		$month = $this->_monthName($m);
		$y = $this->request->get['y'];
		
		$this->registry->get('session')->data['blog_user_path'] = 'archive';
		$this->registry->get('session')->data['blog_query_string'] = $_SERVER[ 'QUERY_STRING' ];
	
		$blog_info = $this->model_blog_blog->getBlogSettings();
		$this->data['blog_info'] = $blog_info;
		$max_image_width = $this->data['blog_info']['blog_entry_image_width'];
		$max_image_height = $this->data['blog_info']['blog_entry_image_height'];
		$this->data['max_container_width'] = $max_image_width . 'px';
		
		$this->data['blog_info'] = $blog_info;
	
		if ($blog_info['title']) {

      		$this->document->addBreadcrumb( array ( 
        		'href'      => $this->blog_html->getBLOGHOME('&encode'),
        		'text'      => $blog_info['title'],
        		'separator' => $this->language->get('text_separator')
      		 ));
			 
			 $this->document->addBreadcrumb( array ( 
        		'href'      => $this->blog_html->getBlogArchiveURL($m, $y, '&encode'),
        		'text'      => $this->language->get('text_archive') . ': ' . $month . ' ' .$y,
        		'separator' => $this->language->get('text_separator')
      		 ));
				  		
			$this->document->setTitle($blog_info['title'] . ' ' . $this->language->get('text_archive'). ': ' . $month . ' ' .$y);

			$this->document->setKeywords($blog_info['meta_keywords']);
			$this->document->setDescription($blog_info['meta_description']);
			
			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$this->data['base'] = $this->config->get('https_blog_server');
			} else {
				$this->data['base'] = $this->config->get('http_blog_server');
			}
									
            $this->view->assign('heading_title', $this->language->get('text_archive') . ': ' . $month . ' ' .$y);
			$this->view->assign('disclaimer', html_entity_decode($blog_info['disclaimer'], ENT_QUOTES, 'UTF-8'));

			$this->view->assign('description', html_entity_decode($blog_info['description'], ENT_QUOTES, 'UTF-8') );
			$this->data['page_url'] = $this->blog_html->getBlogArchiveURL($m, $y);
			$this->document->addLink ( array(
				'rel'  => 'canonical',
				'href' => $this->blog_html->getBlogArchiveURL($m, $y)
			));
			
			$archive_total = $this->model_blog_blog->getTotalArchiveBlogEntries(0, 0, $m, $y); 

			$this->data['archive_total'] = $archive_total;
			if ($archive_total) {
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

							
				$entries = array();
        		
				$archive_info = $this->model_blog_blog->getArchiveBlogEntries(($page - 1) * $limit, $limit, $m, $y);
				
				$resource = new AResource('image');
				
        		foreach ($archive_info as $result) {
					
					$author_name = $result['author_name'];
					$author_link = $this->blog_html->getBLOGSEOURL('blog/author','&blog_author_id=' . $result['blog_author_id'], '&encode');
					if(!$result['author_name']) {
						$author_name = $blog_info['owner'];
					}
					$rp = $resource->getResources('blog_entry', $result['blog_entry_id']);
					$image_main = '';
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
						'author_link'		=> $author_link,
						'show_author_page' 	=> $result['show_author_page'],
						'author_name' 		=> $author_name,
						'comments_count' 	=> $result['comments_count'],
						'share' 			=> $blog_info['share'],
						'href'  			=> $entry_href,
						'comment-href'      => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $result['blog_entry_id'], '&encode').'#comments',
						'image'  			=> $image_main,
						'view_count'		=> $result['view'],
					);
				}
				
				$this->data['entries'] = $entries;

				$pagination_url = $this->blog_html->getBlogArchiveURL($m, $y, '&page={page}' . '&limit=' . $limit,'&encode');

				$this->view->assign('pagination_bootstrap', HtmlElementFactory::create( array (
											'type' => 'Pagination',
											'name' => 'pagination',
											'text'=> $this->language->get('text_pagination'),
											'text_limit' => $this->language->get('text_per_page'),
											'total'	=> $archive_total,
											'page'	=> $page,
											'limit'	=> $limit,
											'url' => $pagination_url,
											'style' => 'pagination')) 
									);

				
				$this->view->setTemplate( 'pages/blog/archive.tpl' );
      		} else {
				
				$this->view->assign('no_articles', $this->language->get('text_blog_no_archives'));
				
        		$this->document->setTitle( $blog_info['title'] );

                $this->view->setTemplate( 'pages/blog/archive.tpl' );
      		}
    	} else {
			$url = '';
				
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	
			$this->view->assign('heading_title', $this->language->get('text_blog_not_setup'));
			$this->document->setTitle($this->language->get('text_future_blog'));
            $this->view->setTemplate( 'pages/blog/archive.tpl' );
		}
		
		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);

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