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
class ControllerResponsesBlogFeed extends AController {
	public $data = array();
	
	
	public function main() {
		$registry = Registry::getInstance();
		$this->loadModel('blog/blog');
		$this->loadLanguage('blog/blog');
		$blog_settings = $this->model_blog_blog->getBlogSettings();
		$more = $this->language->get('text_read_more');
		$resource = new AResource('image');
		$max_image_width = $blog_settings['blog_entry_image_width'];
		$max_image_height = $blog_settings['blog_entry_image_height'];
		
		$language_code = $this->model_blog_blog->getLanguageCode();
		$default_source = $blog_settings['feed_type'];
		$source = $this->request->get['source'];
		if(!$source) { $source = $default_source; }
		$output = 'article';	
		if(isset($this->request->get['e']) && $this->request->get['e']) {
			$output = 'comment';
		}
		
		if (defined('HTTPS') && HTTPS) {
			if($blog_settings['blog_ssl'] && $blog_settings['blog_ssl_url']) {
				$blog_server = $this->config->get('https_blog_server');
			}
		}else{
			$blog_server = $this->config->get('http_blog_server');
		}
		switch ($output) {
			case 'article':
				$limit = $blog_settings['entries_per_rss_feed'];
				$entries = $this->model_blog_blog->getBlogEntries(0, $limit);
				$build_date = $this->model_blog_blog->getLastEntryDate();
				$title = $blog_settings['title'];
				$blog_link =  $blog_server . 'blog';
				$feed_link = $blog_server . 'feed?source='.$source;
			break;
			case 'comment';
				$blog_entry_id = $this->request->get['e'];
				$user_tz = '';
				$blog_tz = 'dt_gmt';
				$this_comments = $this->model_blog_blog->getComments(0, $blog_entry_id, $user_tz, $blog_tz);
				$this_entry = $this->model_blog_blog->getBlogEntry($blog_entry_id);
				$build_date = $this->model_blog_blog->getLastCommentDate($blog_entry_id);
				$title = $this->language->get('text_comments') . ' ' . $this->language->get('text_on') . ': ' . $this_entry['entry_title'];
				$blog_link =  $blog_server . 'blog';
				$feed_link = $blog_server . 'feed?source='.$source.'&amp;e='.$blog_entry_id;
			break;
		}

		$response = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
		
		switch($source) {
			case 'rss2':
				$feed_content_type = 'application/rss+xml';
				$format = $this->language->get('date_format_feed');
				
				$last_build_date = $this->fixDates($build_date, $format);
				
				$rss_head = '<rss version="2.0"' . "\n";
				$rss_head .= 'xmlns:content="http://purl.org/rss/1.0/modules/content/"' . "\n";
				$rss_head .= 'xmlns:wfw="http://wellformedweb.org/CommentAPI/"' . "\n";
				$rss_head .= 'xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
				$rss_head .= 'xmlns:atom="http://www.w3.org/2005/Atom"' . "\n";
				$rss_head .= 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"' . "\n";
				$rss_head .= 'xmlns:slash="http://purl.org/rss/1.0/modules/slash/">' . "\n";
				
				$response .= $rss_head;
				$response .= '<channel>' . "\n";
				$response .= '<title>' . $title . '</title>' . "\n";
				$response .= '<atom:link href="' . $feed_link . '" rel="self" type="' . $feed_content_type . '" />' . "\n";
				$response .= '<link>' . $blog_link . '</link>' . "\n";
				$response .= '<description>' . $blog_settings['description'] . '</description>' . "\n";
				$response .= '<lastBuildDate>' . $last_build_date . '</lastBuildDate>' . "\n";
				$response .= '<language>'. $language_code . '</language>' . "\n";
				$response .= '<sy:updatePeriod>hourly</sy:updatePeriod>' . "\n";
				$response .= '<sy:updateFrequency>1</sy:updateFrequency>' . "\n";
				
				if($output == 'article') {
					foreach ($entries as $entry) {
						$blog_categories = $this->model_blog_blog->getPostedCategories($entry['blog_entry_id']);
						
						$author_name = $entry['author_name'];
						if(!$author_name) {
							$author_name = $blog_settings['owner'];
						}
						
						if($entry['release_date']) {
							$pub_date = $this->fixDates($entry['release_date'], $format);
						}
						$thumb_html = '';
						if($blog_settings['feed_show_thumb']) {
							
							$rp = $resource->getResources('blog_entry', $entry['blog_entry_id']);
							if($rp[0]['resource_path']) {
								$image_path = $blog_server . 'resources/image/' . $rp[0]['resource_path']; 
								list($act_width, $act_height) = getimagesize($image_path);
								$image_width = $act_width < $max_image_width ? $act_width : $max_image_width;
								$image_height = $act_height < $max_image_height ? $act_height : $max_image_height;	
								$sizes = array('main'  => array('width'  => $image_width, 'height' => $image_height));
								$image_main = $resource->getResourceAllObjects('blog_entry', $entry['blog_entry_id'], $sizes, 1, false);
								$image_main['main_html'] = '<img src="' . $image_main['main_url'] . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $entry['entry_title'] . '" title="' . $entry['entry_title'] . '" />';
							}
							
					 		//for blog author edit extension
							$this->session->data['blog_entry_id'] = $entry['blog_entry_id'];
							$this->extensions->hk_ProcessData($this, __FUNCTION__);
							
							if($this->session->data['image']) {
								$image_main = $this->session->data['image'];
								unset($this->session->data['image']);
							}
							unset($this->session->data['blog_entry_id']);
					 		//end
							
							$thumb_html = '<a href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '">' . $image_main['main_html'].'</a>';
						}
						
						$response .= '<item>' . "\n";
						$response .= '<title>' . $entry['entry_title'] . '</title>' . "\n";
						$response .= '<link>' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '</link>' . "\n";
						$response .= '<comments>' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']).'#comments</comments>' . "\n";
						$response .= '<pubDate>' . $pub_date . '</pubDate>' . "\n";
						$response .= '<dc:creator><![CDATA[' . $author_name . ']]></dc:creator>' . "\n";
						foreach ($blog_categories as $category) {
							$response .= '<category><![CDATA[' . $category['name'] . ']]></category>' . "\n";
						}
						
							
						if ($entry['use_intro'] && $entry['entry_intro']) {
							$description = html_entity_decode($entry['entry_intro'], ENT_QUOTES, 'UTF-8');
						}else{
							$description = $this->truncateHTML(html_entity_decode($entry['content'], ENT_QUOTES, 'UTF-8'),$blog_settings['word_count_feed'],'...');
						}
						if(strpos($description, '<p>') === false) {
							$description = '<p>' . $description . '</p>';
						}
						if($thumb_html) {
							$description = $thumb_html . ' ' .$description;
						}
						$description .= ' <a href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '">' . $more .'</a>';
						
						$response .= '<description><![CDATA[' . $description . ']]></description>' . "\n";

						$response .= '<content:encoded><![CDATA[' . html_entity_decode($entry['content'], ENT_QUOTES, 'UTF-8') . ']]></content:encoded>' . "\n";
						$response .= '<guid isPermaLink="false">' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '</guid>' . "\n";
						if($entry['allow_comment']) {
							$response .= '<wfw:commentRss>' . $feed_link . '&amp;e=' . $entry['blog_entry_id'] . '</wfw:commentRss>' . "\n";
							$response .= '<slash:comments>' . $entry['comments_count'] . '</slash:comments>' . "\n";
						}
						$response .= '</item>' . "\n";
					}
				}
				if($output == 'comment') {	
				
					$total_comments = '';
				
					for ($i=0; $i < count($this_comments); $i++) {
						$total_comments .= $this->prepareComments($this_comments[$i], $source, $blog_settings['word_count_feed']);		
					}
					$response .= $total_comments;
				}
			
				$response .= '</channel>' . "\n";
				$response .= '</rss>' . "\n";
				
			break;
			case 'atom':
				$feed_content_type = 'application/atom+xml' . "\n";
				$format = $this->language->get('date_format_atom');

				$last_build_date = $this->fixDates($build_date, $format);
				
				$atom_head = '<feed' . "\n";
  				$atom_head .= 'xmlns="http://www.w3.org/2005/Atom"' . "\n";
  				$atom_head .= 'xmlns:thr="http://purl.org/syndication/thread/1.0"' . "\n";
  				$atom_head .= 'xml:lang="'. $language_code . '"' . "\n";
 				$atom_head .= 'xml:base="' . $blog_link  . '">' .  "\n";
				
				$response .= $atom_head;
				
				$response .= '<title>' . $title . '</title>' . "\n";
				$response .= '<subtitle type="text">' . $blog_settings['description'] . '</subtitle>' . "\n";
				$response .= '<updated>' . $last_build_date . '</updated>' . "\n";

				$response .= '<link rel="alternate" type="text/html" href="' . $feed_link  . '" />' . "\n";
				$response .= '<id>' . $feed_link . '</id>' . "\n";
				$response .= '<link rel="self" type="application/atom+xml" href="' . $feed_link . '" />' . "\n";
				if($output == 'article') {
					foreach ($entries as $entry) {
						
						$blog_categories = $this->model_blog_blog->getPostedCategories($entry['blog_entry_id']);
						$author_info = $this->model_blog_blog->getAuthorBio($blog_entry_id);
						
						$author_name = $entry['author_name'];
						if(!$author_name) {
							$author_name = $blog_settings['owner'];
						}
						if($entry['release_date']) {
							$pub_date = $this->fixDates($entry['release_date'], $format);
						}
						if($entry['date_modified']) {
							$update_date = $this->fixDates($entry['date_modified'], $format);
						}
						$thumb_html = '';
						if($blog_settings['feed_show_thumb']) {
						
							$rp = $resource->getResources('blog_entry', $entry['blog_entry_id']);
							if($rp[0]['resource_path']) {
								$image_path = $blog_server . 'resources/image/' . $rp[0]['resource_path']; 
								list($act_width, $act_height) = getimagesize($image_path);
								$image_width = $act_width < $max_image_width ? $act_width : $max_image_width;
								$image_height = $act_height < $max_image_height ? $act_height : $max_image_height;	
								$sizes = array('main'  => array('width'  => $image_width, 'height' => $image_height));
								$image_main = $resource->getResourceAllObjects('blog_entry', $entry['blog_entry_id'], $sizes, 1, false);
								$image_main['main_html'] = '<img src="' . $image_main['main_url'] . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $entry['entry_title'] . '" title="' . $entry['entry_title'] . '" />';
							}
							
					 		//for blog author edit extension
							$this->session->data['blog_entry_id'] = $entry['blog_entry_id'];
							$this->extensions->hk_ProcessData($this, __FUNCTION__);
							
							if($this->session->data['image']) {
								$image_main = $this->session->data['image'];
								unset($this->session->data['image']);
							}
							unset($this->session->data['blog_entry_id']);
					 		//end
							
							$thumb_html = '<a href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '">' . $image_main['main_html'].'</a>';
						}
						
						$response .= '<entry>' . "\n";
						$response .= '<author>' . "\n";
						$response .= '<name>' . $author_name . '</name>' . "\n";
						if($author_info['site_url']) {
							$response .= '<uri>' . $author_info['site_url'] . '</uri>' . "\n";
						}
						$response .= '</author>' . "\n";
						$response .= '<title type="html"><![CDATA[' . $entry['entry_title'] . ']]></title>' . "\n";
						$response .= '<link rel="alternate" type="text/html" href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '" />' . "\n";
						$response .= '<id>' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '</id>' . "\n";
						$response .= '<published>' . $pub_date . '</published>' . "\n";
						
						if($entry['date_modified'] > $entry['release_date']) {
							$response .= '<updated>' . $update_date . '</updated>' . "\n";
						}else{
							$response .= '<updated>' . $pub_date . '</updated>' . "\n";
						}
						foreach ($blog_categories as $category) {
							$response .= '<category term="' . $category['name'] . '" />' . "\n";
						}
						if ($entry['use_intro'] && $entry['entry_intro']) {
							$description = html_entity_decode($entry['entry_intro'], ENT_QUOTES, 'UTF-8');
						}else{
							$description = $this->truncateHTML(html_entity_decode($entry['content'], ENT_QUOTES, 'UTF-8'),$blog_settings['word_count_feed'],'...');
						}
						if(strpos($description, '<p>') === false) {
							$description = '<p>' . $description . '</p>';
						}
						if($thumb_html) {
							$description = $thumb_html . ' ' .$description;
						}
						$description .= ' <a href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']) . '">' . $more .'</a>';
						
						$response .= '<summary type="html"><![CDATA[' . $description . ']]></summary>' . "\n";
						
						$response .= '<content type="html" xml:base="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']).'#comments"><![CDATA[' . html_entity_decode($entry['content'], ENT_QUOTES, 'UTF-8') . ']]></content>' . "\n";	
						if($entry['allow_comment']) {
							$response .= '<link rel="replies" type="text/html" href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $entry['blog_entry_id']).'#comments" thr:count="' . $entry['comments_count'] . '"/>' . "\n";
							$response .= '<link rel="replies" type="application/atom+xml" href="' . $feed_link . '&amp;e=' . $entry['blog_entry_id'] . '" thr:count="' . $entry['comments_count'] . '"/>' . "\n";
							$response .= '<thr:total>' . $entry['comments_count'] . '</thr:total>' . "\n";
						}
						$response .= '</entry>' . "\n";
					}
				}
				if($output == 'comment') {	
					$total_comments = '';
				
					for ($i=0; $i < count($this_comments); $i++) {
						$total_comments .= $this->prepareComments($this_comments[$i], $source, $blog_settings['word_count_feed']);		
					}
					$response .= $total_comments;
					
				}
				$response .= '</feed>' . "\n";
				
			break;
		}
		
		$this->response->addHeader('Content-Type:' .$feed_content_type);
		$this->response->setOutput($response, 0);
		
	}
	
	private function prepareComments($comment, $source, $word_count = 300) {
	
		if ($source == 'rss2') {
			$format = $this->language->get('date_format_feed');
			$response .= '<item>' . "\n";
			if($comment['parent_id'] == 0) {
				$response .= '<title>' . $this->language->get('text_by') . ': ' . $comment['username'] . '</title>' . "\n";
			}else{
				$response .= '<title>' . $this->language->get('text_by') . ': ' . $comment['username'] . ' (' . $this->language->get('text_in_response') . ' ' . $comment['parent_author'] . ')</title>' . "\n";
			}
			$response .= '<link>' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '#comment-' . $comment['blog_comment_id'] . '</link>' . "\n";
			$response .= '<pubDate>' . $this->fixDates($comment['date_added_raw'], $format) . '</pubDate>' . "\n";
			$response .= '<dc:creator><![CDATA[' . $comment['username'] . ']]></dc:creator>' . "\n";
			$response .= '<guid isPermaLink="false">' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '#comment-' . $comment['blog_comment_id'] . '</guid>' . "\n";
			$response .= '<description><![CDATA[' . $this->truncateHTML(html_entity_decode($comment['comment'], ENT_QUOTES, 'UTF-8'), $word_count ,'...') . ']]></description>' . "\n";
			$response .= '<content:encoded><![CDATA[' . html_entity_decode($comment['comment'], ENT_QUOTES, 'UTF-8') . ']]></content:encoded>' . "\n";
			$response .= '</item>' . "\n";
					
			if (is_array($comment['children'])){
				$children_array = $comment['children'];
				for ($i=0; $i < count($children_array); $i++) {
					$response .= $this->prepareComments($children_array[$i], $source); 
				}
		  	}
		}
		
		if ($source == 'atom') {
			$format = $this->language->get('date_format_atom');
			$response .= '<entry>' . "\n";
			if($comment['parent_id'] == 0) {
				$response .= '<title type="html"><![CDATA[' . $this->language->get('text_by') . ': ' . $comment['username'] . ']]></title>' . "\n";
			}else{
				$response .= '<title type="html"><![CDATA[' . $this->language->get('text_by') . ': ' . $comment['username'] . ' (' . $this->language->get('text_in_response') . ' ' . $comment['parent_author'] . ']]>)</title>' . "\n";
			}
			$response .= '<author>' . "\n";
			$response .= '<name>' . $comment['username'] . '</name>' . "\n";
			if($author_info['site_url']) {
				$response .= '<uri>' . $author_info['site_url'] . '</uri>' . "\n";
			}
			$response .= '</author>' . "\n";
			$response .= '<id>' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '#comment-' . $comment['blog_comment_id'] . '</id>' . "\n";
			if($comment['date_modified_raw'] >= $comment['date_added_raw']) {
				$response .= '<updated>' . $this->fixDates($comment['date_modified_raw'], $format) . '</updated>' . "\n";
			}else{
				$response .= '<updated>' . $this->fixDates($comment['date_added_raw'], $format) . '</updated>' . "\n";
			}
			$response .= '<published>' . $this->fixDates($comment['date_added_raw'], $format). '</published>' . "\n";
			
			
			$response .= '<content type="html" xml:base="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '#comment-' . $comment['blog_comment_id'] . '"><![CDATA[' . html_entity_decode($comment['comment'], ENT_QUOTES, 'UTF-8') . ']]></content>' . "\n";
			if($comment['parent_id'] == 0) { 
				$response .= '<thr:in-reply-to ref="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '" href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '" type="text/html" />' . "\n";
			}else{
				$response .= '<thr:in-reply-to ref="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '#comment-' . $comment['blog_comment_id'] . '" href="' . $this->blog_html->getBLOGSEOURL('blog/entry','&amp;blog_entry_id=' . $comment['blog_entry_id']) . '#comment-' . $comment['blog_comment_id'] . '" type="text/html" />' . "\n";
			}
			$response .= '</entry>' . "\n";
			
			if (is_array($comment['children'])){
				$children_array = $comment['children'];
				for ($i=0; $i < count($children_array); $i++) {
					$response .= $this->prepareComments($children_array[$i], $source); 
				}
		  	}
		}
		
		return $response;
		
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
	
	private function fixDates($input_date, $format) {
		$cur_tz = date_default_timezone_get();
		$new_date = new DateTime($input_date, new DateTimeZone($cur_tz));
		$new_date->setTimezone(new DateTimeZone('UTC'));
		return $new_date->format($format);
	
	}
	
}