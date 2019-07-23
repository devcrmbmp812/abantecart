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
class ModelToolBlogSeoUrl extends Model {
	public function rewrite($link) {
		if ($this->config->get('enable_seo_url')) {
			$url_data = parse_url(str_replace('&amp;', '&', $link));
			$url = '';
			$data = array();
	
			parse_str($url_data['query'], $data);
			
			foreach ($data as $key => $value) {
				if($key == 'blog') {
					$url = '/blog';
					unset($data[$key]);
				}elseif ($key == 'blog_entry_id' || $key == 'blog_author_id') {
						$query = $this->db->query("SELECT *
											   FROM " . DB_PREFIX . "url_aliases
											   WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'
											   	AND language_id='".(int)$this->config->get('storefront_language_id')."'");
				
					if ($query->num_rows) {
						$url .= '/' . $query->row['keyword'];
						unset($data[$key]);
					}
				
				} elseif ($key == 'bcat' || $key == 'blog_category_id') {
						if($key == 'bcat'){
							$value = explode('_',$value);
							end($value);
							$value = current($value);
						}

						$sql = "SELECT *
								FROM " . DB_PREFIX . "url_aliases
								WHERE `query` = 'blog_category_id=" . $this->db->escape($value) . "'
									AND language_id='".(int)$this->config->get('storefront_language_id')."'";
						
						$query = $this->db->query($sql);
						if ($query->num_rows) {
							$url .= '/' . $query->row['keyword'];
						}					

					
					unset($data[$key]);
				}
			}
		
			if ($url) {
				unset($data['rt']);
			
				$query = '';
			
				if ($data) {
					foreach ($data as $key => $value) {
						$query .= '&' . $key . '=' . $value;
					}
					
					if ($query) {
						$query = '?' . trim($query, '&');
					}
				}

				return $url_data['scheme'] . '://' . $url_data['host'] . (isset($url_data['port']) ? ':' . $url_data['port'] : '') . str_replace('/index.php', '', $url_data['path']) . $url . $query;
			} else {
				return $link;
			}
		} else {
			return $link;
		}		
	}
}
?>