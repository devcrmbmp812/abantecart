<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}

class ModelBlogBlog extends Model {

	public function getBlogSettings() {
		$data = array();
		
		$sql = "SELECT `key`, `value`
				FROM " . $this->db->table("blog_settings") . "";			
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $row) {
			$data[$row['key']] = $row['value'];
		}
		return $data;
	}
	
	public function getblog_config($key) {
		
		$sql = "SELECT `value` 
						FROM " . $this->db->table("blog_settings") . "
						WHERE `key` = '" . $key . "'";
		
		$query = $this->db->query($sql);
		return $query->row['value'];
	}
	
	public function getBlogEntry($blog_entry_id) {
		
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		
		if(!$blog_entry_id){
			return false;
		}
		$sql = "SELECT be.*, bed.*, CONCAT(ba.firstname, ' ',ba.lastname) as name,
						( SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_comment") . " bc
						WHERE be.blog_entry_id = bc.blog_entry_id AND bc.approved = '1') as comments_count
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (be.blog_entry_id = bed.blog_entry_id AND bed.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				LEFT JOIN " . $this->db->table('blog_author')." ba							
					ON (ba.blog_author_id = be.blog_author_id)
				WHERE be.blog_entry_id = '" . (int)$blog_entry_id . "'";
				
		$query = $this->db->query($sql);
		
		return $query->row;

	}
	
	public function getBlogEntries($start = 0, $limit = 0, $blog_category_id = 0, $mode = 'default') {
		
		$sort_order = $this->getblog_config('entry_display_order');
		$page_limit = $this->getblog_config('entries_per_main_page');
		$months = $this->getblog_config('show_month');

		$sql = "SELECT be.*, bed.entry_title, bed.entry_intro, bed.content, CONCAT(ba.firstname, ' ',ba.lastname) as author_name, bv.view, ba.show_author_page,
						( SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_comment") . " bc
						WHERE be.blog_entry_id = bc.blog_entry_id and bc.approved = '1') as comments_count
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (be.blog_entry_id = bed.blog_entry_id
						AND bed.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				LEFT JOIN " . $this->db->table('blog_author')." ba				
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table('blog_view')." bv				
					ON (be.blog_entry_id = bv.blog_entry_id)";
					
		$where = " WHERE be.status = '1' AND be.release_date > 0 AND be.release_date <= NOW()";
		
		if(isset($blog_category_id) && $blog_category_id != 0) {
			
			$sql .= " LEFT JOIN	" . $this->db->table("blog_entry_to_category") . " bec
				ON (bec.blog_entry_id = be.blog_entry_id)";
			$where .= " AND bec.blog_category_id = '" . $blog_category_id . "'";
		}
		
		$sql .= $where;
		
		if (isset($months) && $months != 0) {
			$m = $months;
			if($m == 1) {
				$sql .= " AND YEAR(be.release_date) = YEAR(NOW()) AND MONTH(be.release_date) = MONTH(NOW())";
			}elseif($m > 1) {
				$m = $months - 1;
				$sql .= " AND be.release_date >= DATE_FORMAT(CURRENT_DATE - INTERVAL $m MONTH, '%Y-%m-01')";
			}	
		}		
				
		$sql .= " ORDER BY be.release_date ".$sort_order."";
		
		if($mode == 'id_only') {
			//get all entries without limit
			$query = $this->db->query($sql);
			$id_only = array();
			foreach ($query->rows as $result) {
				$id_only[] = $result['blog_entry_id'];		
			}
			return $id_only;
		}
		if (isset($start) || isset($limit)) {
			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = $page_limit;
			}

			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
		}
		
		$query = $this->db->query($sql);
		$blog_entry_data = array();
		foreach ($query->rows as $result) {
			$blog_entry_data[] = array(
				'blog_entry_id' 	=> $result['blog_entry_id'],
				'blog_author_id' 	=> $result['blog_author_id'],
				'entry_title'	 	=> $result['entry_title'],
				'use_intro'			=> $result['use_intro'],
				'entry_intro'		=> $result['entry_intro'],
				'content'			=> $result['content'],
				'release_date' 		=> $result['release_date'],
				'date_modified' 	=> $result['date_modified'],
				'allow_comment' 	=> $result['allow_comment'],
				'author_name' 		=> $result['author_name'],
				'show_author_page'	=> $result['show_author_page'],
				'comments_count' 	=> $result['comments_count'],
				'view'				=> $result['view']
			);
		}		
		return $blog_entry_data;

	}
	
	public function getTotalBlogEntries() {
		
		$sql = "SELECT COUNT(DISTINCT blog_entry_id) as total
				FROM " . $this->db->table("blog_entry") . "
				WHERE status = '1' AND release_date > 0 AND release_date <= NOW()";
				
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function getArchiveBlogEntries($start = 0, $limit = 0, $m, $y, $mode = 'default') {
		
		$sort_order = $this->getblog_config('entry_display_order');
		$page_limit = $this->getblog_config('entries_per_main_page');
		$months = $this->getblog_config('show_month');
		
		if ($mode == 'total_only') {
			$select_columns = 'count(*) as total';
		}
		else {
			$select_columns = "be.*, bed.entry_title, bed.entry_intro, bed.content, CONCAT(ba.firstname, ' ',ba.lastname) as author_name, bv.view, ba.show_author_page,
						( SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_comment") . " bc
						WHERE be.blog_entry_id = bc.blog_entry_id AND bc.approved = '1') as comments_count";
		}
			$sql = "SELECT " . $select_columns . "				
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (be.blog_entry_id = bed.blog_entry_id
						AND bed.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				LEFT JOIN " . $this->db->table('blog_author')." ba				
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table('blog_view')." bv				
					ON (be.blog_entry_id = bv.blog_entry_id)
				WHERE be.status = '1' AND be.release_date > 0 AND MONTH(be.release_date) = '" . $m . "' AND YEAR(be.release_date) =  '" . $y . "'		
				ORDER BY be.release_date ".$sort_order."";
		
		//If for total, we done bulding the query
		if ($mode == 'total_only') {
			$query = $this->db->query($sql);
			return $query->row['total'];
		}
		
		if($mode == 'id_only') {
			//get all entries without limit
			$query = $this->db->query($sql);
			$id_only = array();
			foreach ($query->rows as $result) {
				$id_only[] = $result['blog_entry_id'];		
			}
			return $id_only;
		}
		
		if (isset($start) || isset($limit)) {
			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = $page_limit;
			}

			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
		}
		
		$query = $this->db->query($sql);
		$blog_entry_data = array();
		foreach ($query->rows as $result) {
			$blog_entry_data[] = array(
				'blog_entry_id' 	=> $result['blog_entry_id'],
				'blog_author_id' 	=> $result['blog_author_id'],
				'entry_title'	 	=> $result['entry_title'],
				'use_intro'			=> $result['use_intro'],
				'entry_intro'		=> $result['entry_intro'],
				'content'			=> $result['content'],
				'release_date' 		=> $result['release_date'],
				'date_modified' 	=> $result['date_modified'],
				'allow_comment' 	=> $result['allow_comment'],
				'author_name' 		=> $result['author_name'],
				'show_author_page'	=> $result['show_author_page'],
				'comments_count' 	=> $result['comments_count'],
				'view'				=> $result['view']
			);
		}		
		return $blog_entry_data;

	}
	
	public function getTotalArchiveBlogEntries($start, $limit, $m, $y) {
		return $this->getArchiveBlogEntries($start, $limit, $m, $y, 'total_only');
	}
	
	public function getArchiveMonths($limit=0) {
		$sort_order = $this->getblog_config('entry_display_order');
		$months = $this->getblog_config('show_month');
		$date_range = array();
	
		$sql = "SELECT DISTINCT MONTH(`release_date`) as mth, MONTHNAME(`release_date`) as month, YEAR(`release_date`) as year
				FROM " . $this->db->table("blog_entry") . "
				WHERE status = '1' AND release_date > 0";
		
		if ($months > 0) {		
			$sql .= " AND release_date < DATE_FORMAT(CURRENT_DATE - INTERVAL $months MONTH, '%Y-%m-01')";
		}
		$sql .= " ORDER BY release_date DESC";
		
		if($limit != 0) {
			$sql .= " LIMIT 0," . (int)$limit . "";
		}
		$query = $this->db->query($sql);
				
		foreach($query->rows as $row) {	
			$query_count = $this->db->query("SELECT COUNT(blog_entry_id) as count
				FROM " . $this->db->table("blog_entry") . "
				WHERE status = '1' AND
				MONTH(release_date) = '" . $row['mth'] . "' AND YEAR(release_date) =  '" . $row['year'] . "'");	
			$date_range[] = array(
				'month_num' => $row['mth'],
				'month' => $row['month'],
				'year' => $row['year'],
				'count' => $query_count->row['count']
			);	
		}
		
		return $date_range;
				
	}
	
	public function getBlogEntriesByKeyword($keyword, $start = 0, $limit = 0, $mode = 'default') {
		if(!$keyword) {
			return false;
		}
		$blog_entry_data = array();
		
		$page_limit = $this->getblog_config('entries_per_main_page');
		$search_type = $this->getblog_config('search_type');
		$s_title = $this->getblog_config('search_article_title');
		$s_intro = $this->getblog_config('search_article_intro');
		$s_content = $this->getblog_config('search_article_content');
		$s_keywords = $this->getblog_config('search_meta_keywords');
		$s_description = $this->getblog_config('search_meta_desc');
		
		$field_names = array();
		if($s_title) { array_push($field_names, 'entry_title'); }  
		if($s_intro) { array_push($field_names, 'entry_intro'); }
		if($s_content) { array_push($field_names, 'content'); }
		if($s_keywords) { array_push($field_names, 'meta_keywords'); }
		if($s_description) { array_push($field_names, 'meta_description'); }
		
		$tags = explode(' ', trim($keyword));
		
		$sql1 = "SELECT blog_entry_id FROM " . $this->db->table("blog_entry_description") . "";
		
		if ($search_type == 'simp_search') {
			$count = 0;
			foreach ($field_names as $field) {
				foreach ($tags as $tag) {
					if ($count == 0) {
						$sql1 .= " WHERE";
						$count++;
					}else{
						$sql1 .= " OR ";
					}
					$sql1 .= " ". $field . " LIKE '%" . $this->db->escape(mb_strtolower($tag)) . "%'";
				}
			} 
		}elseif ($search_type == 'full_search') {
				$sql1 .= " WHERE MATCH (" . implode(', ',$field_names ) . ") AGAINST ('" . $keyword . "' IN NATURAL LANGUAGE MODE)";
		}elseif ($search_type == 'extd_search') {
				$sql1 .= " WHERE MATCH (" . implode(', ',$field_names ) . ") AGAINST ('" . $keyword . "' IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION)";
		}
	
		$query1 = $this->db->query($sql1);
		foreach ($query1->rows as $entry) {
			$entries[] = $entry['blog_entry_id'];
		}
		
		if(count($entries)) {
		
			if($mode == 'total') {
				$query_cols = " COUNT(*) as total";
			}else{
			
				$query_cols = " be.*, bed.entry_title, bed.entry_intro, bed.content, CONCAT(ba.firstname, ' ',ba.lastname) as author_name, bv.view, ba.show_author_page,
							( SELECT COUNT(*) as cnt 
							FROM " . $this->db->table("blog_comment") . " bc
							WHERE be.blog_entry_id = bc.blog_entry_id AND bc.approved = '1') as comments_count";
			}
		
			$sql = "SELECT " . $query_cols . "
					FROM " . $this->db->table("blog_entry") . " be
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
						ON (be.blog_entry_id = bed.blog_entry_id
							AND bed.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
					LEFT JOIN " . $this->db->table('blog_author')." ba				
						ON (be.blog_author_id = ba.blog_author_id)
					LEFT JOIN " . $this->db->table('blog_view')." bv				
						ON (be.blog_entry_id = bv.blog_entry_id)
					WHERE be.status = '1' AND be.release_date > 0 AND be.release_date <= NOW()
						AND be.blog_entry_id IN (" . implode(',', $entries) . ")";
						
			if($mode == 'total') {
				$query = $this->db->query($sql);
				return $query->row['total'];
			}
				
			if (isset($start) || isset($limit)) {
				if ($start < 0) {
					$start = 0;
				}
	
				if ($limit < 1) {
					$limit = $page_limit;
				}
	
				$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
			}
			
			$query = $this->db->query($sql);
			
			foreach ($query->rows as $result) {
				$blog_entry_data[] = array(
					'blog_entry_id' 	=> $result['blog_entry_id'],
					'blog_author_id' 	=> $result['blog_author_id'],
					'entry_title'	 	=> $result['entry_title'],
					'use_intro'			=> $result['use_intro'],
					'entry_intro'		=> $result['entry_intro'],
					'content'			=> $result['content'],
					'release_date' 		=> $result['release_date'],
					'date_modified' 	=> $result['date_modified'],
					'allow_comment' 	=> $result['allow_comment'],
					'author_name' 		=> $result['author_name'],
					'show_author_page'	=> $result['show_author_page'],
					'comments_count' 	=> $result['comments_count'],
					'view'				=> $result['view']
				);
			}
		}
		return $blog_entry_data;

	}
	
	public function getTotalBlogEntriesByKeyword($keyword) {
		return $this->getBlogEntriesByKeyword($keyword, 0, 0, 'total');
	}
	
	public function getEntryTitle($blog_entry_id) {
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$query = $this->db->query("SELECT entry_title
						FROM " . $this->db->table("blog_entry_description") . "
						WHERE blog_entry_id = '".$blog_entry_id."'");
			
		return $query->row['entry_title'];
	}
	
	public function getParentAuthor($blog_comment_id) {
		$blog_comment_id = (int)$blog_comment_id;
		if(!$blog_comment_id){
			return false;
		}
		
		$query = $this->db->query("SELECT username
						FROM " . $this->db->table("blog_comment") . "
						WHERE blog_comment_id = '".$blog_comment_id."'");
			
		return $query->row['username'];
		
	}
	
	public function getAuthorDetails($blog_author_id) {
		
		$blog_author_id = (int)$blog_author_id;
		if(!$blog_author_id){
			return false;
		}

		$language_id = $this->config->get('storefront_language_id');
		
		$sql = "SELECT CONCAT(ba.firstname, ' ', ba.lastname) as name, ba.site_url, ba.show_details, bad.author_description, bad.author_title, ba.show_details, ba.show_details_ap, ba.show_author_link, ba.show_author_page, bad.meta_keywords, bad.meta_description
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_author") . " ba
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table("blog_author_description") . " bad
					ON (ba.blog_author_id = bad.blog_author_id AND bad.language_id = '" . (int)$language_id . "')
				WHERE ba.blog_author_id = '" . (int)$blog_author_id . "'";
				
		$query = $this->db->query($sql);
		
		return $query->row;
			
	}	
	public function getAuthorBio($blog_entry_id) {
		
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}

		$language_id = $this->config->get('storefront_language_id');
		
		$sql = "SELECT CONCAT(ba.firstname, ' ', ba.lastname) as name, ba.site_url, ba.show_details, bad.author_description, bad.author_title, ba.show_details, ba.show_details_ap, ba.show_author_link, ba.show_author_page, bad.meta_keywords, bad.meta_description
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_author") . " ba
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table("blog_author_description") . " bad
					ON (ba.blog_author_id = bad.blog_author_id AND bad.language_id = '" . (int)$language_id . "')
				WHERE be.blog_entry_id = '" . (int)$blog_entry_id . "'";
				
		$query = $this->db->query($sql);
		
		return $query->row;
			
	}

	public function getBlogCategory($blog_category_id) {
		$language_id = (int)$this->config->get('storefront_language_id');
		$query = $this->db->query("SELECT bc.*, bcd.*
									FROM " . $this->db->table("blog_category") . " bc
									LEFT JOIN " . $this->db->table("blog_category_description") . " bcd ON (bc.blog_category_id = bcd.blog_category_id AND bcd.language_id = '" . $language_id . "')
									WHERE bc.blog_category_id = '" . (int)$blog_category_id . "'
										AND bc.status = '1'");
			
		return $query->row;
										
	}
	
	public function getBlogCategories($parent_id = 0, $limit=0) {
		$language_id = (int)$this->config->get('storefront_language_id');
		
		$omit_uncatergorized = $this->getblog_config('omit_uncatergorized');
		if($limit) {
			$cache = '';
		}else{
			$cache_name = 'blog.category.list.'. $parent_id.'.'.$limit;
			$cache = $this->cache->get($cache_name, $language_id, (int)$this->getblog_config('blog_store_id'));
		}
		if(is_null($cache)){
			$sql = "SELECT *
					FROM " . $this->db->table("blog_category") . " bc
					LEFT JOIN " . $this->db->table("blog_category_description") . " bcd 
						ON (bc.blog_category_id = bcd.blog_category_id AND bcd.language_id = '" . $language_id . "')
					WHERE bc.status = '1'
					".($parent_id<0 ? "" : " AND bc.parent_id = '" . (int)$parent_id . "'")."";
					
			if(isset($omit_uncatergorized) && $omit_uncatergorized == 1) {
				$sql .= " AND bc.blog_category_id <> '1'";	
			}
					
			$sql .=	 " ORDER BY bc.sort_order, LCASE(bcd.name) ".((int)$limit ? "LIMIT ".(int)$limit : '')."";				
			$query = $this->db->query($sql);
			$cache =  $query->rows;
			if(!$limit) {
				$this->cache->set($cache_name, $cache, $language_id, (int)$this->getblog_config('blog_store_id'));
			}
		}
		return $cache;
	}

	/**
	 * @return array
	 */
	public function getAllBlogCategories(){
		return $this->getBlogCategories(-1);
	}
	
	public function buildPath($blog_category_id) {
		$query = $this->db->query("SELECT bc.blog_category_id, bc.parent_id
		                            FROM " . $this->db->table("blog_category") . " bc
		                            WHERE bc.blog_category_id = '" . (int)$blog_category_id . "'
		                            ORDER BY bc.sort_order");
		
		$blog_category_info = $query->row;
		if ($blog_category_info['parent_id']) {
			return $this->buildPath($blog_category_info['parent_id']) . "_" . $blog_category_info['blog_category_id'];
		} else {
			return $blog_category_info['blog_category_id'];
		}
	}
	
	public function getTotalBlogEntriesInCategory($blog_category_id) {
		
		$sql = "SELECT COUNT(DISTINCT bec.blog_entry_id) as total
				FROM " . $this->db->table("blog_entry_to_category") . " bec
				LEFT JOIN " . $this->db->table("blog_entry") . " be
					ON (bec.blog_entry_id = be.blog_entry_id)
				WHERE be.status = '1' AND be.release_date > 0 AND be.release_date <= NOW()";
				
		$query = $this->db->query($sql);
		
		return $query->row['total'];
		
	
	}


	public function getPostedCategories($blog_entry_id) {
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		$category_data = array();
		$language_id = $this->config->get('storefront_language_id');
		$omit_uncat =  $this->getblog_config('omit_uncatergorized');
		
		$sql = "SELECT bec.blog_category_id, bcd.name
				FROM " . $this->db->table("blog_entry_to_category") . " bec
				LEFT JOIN " . $this->db->table("blog_category") . " bc
					ON (bec.blog_category_id = bc.blog_category_id)
				LEFT JOIN " . $this->db->table("blog_category_description") . " bcd
					ON (bec.blog_category_id = bcd.blog_category_id AND bcd.language_id = '" . (int)$language_id . "')
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'
				AND bc.status = '1'
				ORDER BY bcd.blog_category_id ASC";
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) {
				$category_data[] = array(
					'blog_category_id' => $result['blog_category_id'],
					'name'        => $result['name'],
					'link' => $this->blog_html->getBLOGSEOURL('blog/category','&blog_category_id=' . $result['blog_category_id'], '&encode'), 
				);
		}
		
		if($omit_uncat) {
			foreach ($category_data as $row) {
				if($row['blog_category_id'] == 1) {
					unset($category_data[0]);
					continue;
				}
			}
		}

		return $category_data;
		
	}

	
	public function getRelatedCategories($blog_entry_id) {
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		$store_id = $this->getblog_config('blog_store_id');
		$language_id = $this->config->get('storefront_language_id');
		
		$sql = "SELECT c.category_id, cd.name
				FROM " . $this->db->table("blog_entry_category_related") . " bcr
				LEFT JOIN " . $this->db->table("categories") . " c
					ON (bcr.category_id = c.category_id)
				LEFT JOIN " . $this->db->table("category_descriptions") . " cd
					ON (bcr.category_id = cd.category_id AND cd.language_id = '" . (int)$language_id . "')
				RIGHT JOIN " . $this->db->table("categories_to_stores") . " c2s ON (c.category_id = c2s.category_id AND c2s.store_id = '".(int)$store_id."')
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'
				AND c.status = '1'
				ORDER BY cd.name ASC";
				
		$query = $this->db->query($sql);		
		foreach ($query->rows as $result) {
				$category_data[] = array(
					'category_id' => $result['category_id'],
					'name'        => $result['name'],
					'link' => $this->html->getSEOURL('product/category','&path=' . $result['category_id'], '&encode'), 
				);
			}

		return $category_data;
		
	}

	public function getRelatedProducts($blog_entry_id) {
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}

		$sql = "SELECT *, bpr.product_id,
						" . $this->_sql_final_price_string() . ",
						pd.name AS name, 
						pd.blurb,
						m.name AS manufacturer,
						ss.name AS stock,
						" . $this->_sql_avg_rating_string() . ",
						" . $this->_sql_review_count_string() . "
		    	FROM " . $this->db->table("blog_entry_product_related") . " bpr
			 	LEFT JOIN " . $this->db->table("products") . " p
			 		ON (bpr.product_id = p.product_id)
				LEFT JOIN " . $this->db->table("product_descriptions") . " pd
					ON (p.product_id = pd.product_id
							AND pd.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				LEFT JOIN " . $this->db->table("products_to_stores") . " p2s ON (p.product_id = p2s.product_id)
				LEFT JOIN " . $this->db->table("manufacturers") . " m ON (p.manufacturer_id = m.manufacturer_id)
				LEFT JOIN " . $this->db->table("stock_statuses") . " ss
						ON (p.stock_status_id = ss.stock_status_id
								AND ss.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				WHERE p.status = '1' AND p.date_available <= NOW()
				AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND bpr.blog_entry_id = '" . (int)$blog_entry_id . "'";
				
				
		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getRelatedEntries($blog_entry_id) {	
		
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sql = "SELECT bre.blog_entry_related_id, bed.entry_title
				FROM " . $this->db->table("blog_related_entry") . " bre
				LEFT JOIN " . $this->db->table("blog_entry") . " be
					ON (bre.blog_entry_related_id = be.blog_entry_id)
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (bre.blog_entry_related_id = bed.blog_entry_id
						AND bed.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				WHERE bre.blog_entry_id = '" . (int)$blog_entry_id . "' AND be.status = '1' AND be.release_date > 0 AND be.release_date <= NOW()";
				
		$query = $this->db->query($sql);

		foreach($query->rows as $result) {
			$output[] = array(
				'blog_entry_related_id' => $result['blog_entry_related_id'],
				'link' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $result['blog_entry_related_id'], '&encode'),
				'entry_title' => $result['entry_title']
			);
		}
		return $output;
		
	}
	
	public function getEntryName($blog_entry_id) {
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sql = "SELECT entry_title
				FROM " . $this->db->table("blog_entry_description") . "
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'";
		
		$query = $this->db->query($sql);
		return $query->row['entry_title'];
		
	}
	
	public function postComment($data) {
		if(!$data){
			return false;
		}
		$status = 1;
		$approve_comments = $this->getblog_config('approve_comments');
		
		$approved = ($approve_comments == 1 ? 0 : 1);

		if($data['blog_user_id']) {
			$query = $this->db->query("SELECT user_approve_comments, user_require_approval
								FROM " . $this->db->table("blog_user") . "
								WHERE blog_user_id = '" . $data['blog_user_id'] . "'");
			$user_auto_approved = $query->row['user_approve_comments'];	
			$user_approval_required = $query->row['user_require_approval'];
			
			if($approve_comments == 1 && $user_auto_approved == 1) {
				$approved = 1;
			}
			if($approve_comments == 0 && $user_approval_required == 1) {
				$approved = 0;
			}
		}
	
		$sql = "INSERT INTO " . $this->db->table("blog_comment") . "
								SET primary_comment_id = '" . (int)$data['primary_comment_id'] . "',
									blog_entry_id = '" . (int)$data['blog_entry_id'] . "',
									parent_id = '" . (int)$data['parent_id'] . "',
									blog_user_id = '" . (int)$data['blog_user_id'] . "',
									username = '" . $this->db->escape($data['username']) . "',
									email = '" . $this->db->escape($data['email']) . "',
									site_url = '" . $this->db->escape($data['site_url']) . "',
									comment = '" . $this->db->escape($data['comment_detail']) . "',
									status = '" . (int)$status . "',
									approved = '" . (int)$approved . "',
									date_added = NOW()";
		$this->db->query($sql);
		
		$blog_comment_id = $this->db->getLastId();
		
		if(!$data['primary_comment_id']) { 
			 $this->db->query("UPDATE " . $this->db->table("blog_comment") . " SET primary_comment_id = '" .(int)$blog_comment_id . "'  WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");	
			 $data['primary_comment_id'] = $blog_comment_id;
		}
		
		$result = array();
		$result['approved'] = $approved;
		$result['blog_comment_id'] = $blog_comment_id;
		$result['primary_comment_id'] = $data['primary_comment_id'];

		return $result;
	}
	
	public function getComments($parent_id, $blog_entry_id, $user_tz = '', $blog_tz, $primary_comment_id = 0, $depth = 1) {
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sort_order = $this->getblog_config('comment_display_order');
		
		$sql = "SELECT bc.*, bu.name_option, CONCAT(bu.firstname, ' ', bu.lastname) as full_name, CONCAT(c.firstname, ' ', c.lastname) as cust_full_name
				FROM " . $this->db->table("blog_comment") . " bc
				LEFT JOIN " . $this->db->table("blog_user") . " bu
					ON (bc.blog_user_id = bu.blog_user_id)
				LEFT JOIN " . $this->db->table("customers") . " c
					ON (bu.customer_id = c.customer_id)
				WHERE bc.blog_entry_id = '" . (int)$blog_entry_id . "'
				AND bc.parent_id = '" . (int)$parent_id . "'
				AND bc.status = '1' AND bc.approved = '1'";
				
		if(isset($primary_comment_id) && $primary_comment_id != 0) {
			$sql .= "AND bc.primary_comment_id = '" . (int)$primary_comment_id . "'";
		}
		
		if($depth > 1) {
			$sort_order = "ASC";
		}
		
		$sql .= "ORDER BY bc.date_added " . $sort_order . "";
		
		$query = $this->db->query($sql);
		
		$cur_tz = date_default_timezone_get();
		$registry = Registry::getInstance();
		$format = $registry->get('language')->get('date_format_comment');
				
		foreach ($query->rows as $comment) {
			if(isset($user_tz) && $user_tz != 0) {
				$new_date = new DateTime($comment['date_added'], new DateTimeZone($cur_tz));
				$new_date->setTimeZone(new DateTimeZone($user_tz));
				$comment_date = $new_date->format($format);			
			}else{
				if($blog_tz == 'dt_system') {
					$comment_date = dateISO2Display($comment['date_added'], $format);	
				}else{
					$new_date = new DateTime($comment['date_added'], new DateTimeZone($cur_tz));
					$new_date->setTimezone(new DateTimeZone('UTC'));
					$comment_date = $new_date->format($format);
				}
			}
			if($comment['parent_id'] > 0) {
				$query_parent_author = $this->db->query("SELECT username FROM " . $this->db->table("blog_comment") . " WHERE blog_comment_id = '" . $comment['parent_id'] ."'");
				$parent_author = $query_parent_author->row['username'];
			}else{
				$parent_author = '';
			}

			$comments[] = array(
				'blog_comment_id' => $comment['blog_comment_id'],
				'blog_entry_id' => $comment['blog_entry_id'],
				'primary_comment_id' => $comment['primary_comment_id'],
				'parent_id' => $comment['parent_id'],
				'username' => $comment['username'],
				'name_option' => $comment['name_option'],
				'full_name' => $comment['full_name'] ? $comment['full_name'] : $comment['cust_full_name'],
				'comment' => $comment['comment'],
				'site_url' => $comment['site_url'],
				'email' => $comment['email'],
				'date_added' => $comment_date,
				'date_added_raw' => $comment['date_added'],
				'date_modified_raw' => $comment['date_modified'],
				'parent_author' => $parent_author,
				'depth' => $depth,
				'children' => $this->getComments($comment['blog_comment_id'], $blog_entry_id, $user_tz, $blog_tz, $comment['primary_comment_id'], $depth + 1),
			);
			
		}
		
		return $comments;
	}
	
	public function getCustBlogUserData($customer_id) {
		if(!$customer_id){
			return false;
		}
		$store_id = $this->getblog_config('blog_store_id');
		
		$sql = "SELECT DISTINCT c.loginname as username, c.firstname, c.email, bur.role_description as role, bu.site_url, bu.blog_user_id, bu.users_tz,
					c.firstname, c.lastname
					FROM " . $this->db->table("blog_user") . " bu
					LEFT JOIN " . $this->db->table("blog_user_role") . " bur
						ON (bu.role_id = bur.role_id)
					LEFT JOIN " . $this->db->table("customers") . " c
						ON (bu.customer_id = c.customer_id AND c.store_id = '" . (int)$store_id . "')
					WHERE bu.customer_id = '" . (int)$customer_id . "'";	
		
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getUserData($username) {
		
		$query = $this->db->query("SELECT bu.*, bur.role_description as role
									FROM " . $this->db->table("blog_user") . " bu
									LEFT JOIN " . $this->db->table("blog_user_role") . " bur
										ON (bu.role_id = bur.role_id)
									WHERE LOWER(`username`) = LOWER('" . $this->db->escape($username) . "')");							
		return $query->row;
	}
	public function getUserDataFromEmail($email) {
		
		$query = $this->db->query("SELECT bu.*, bur.role_description as role
									FROM " . $this->db->table("blog_user") . " bu
									LEFT JOIN " . $this->db->table("blog_user_role") . " bur
										ON (bu.role_id = bur.role_id)
									WHERE LOWER(`email`) = LOWER('" . $this->db->escape($email) . "')");
									
		return $query->row;
	}
	
	public function getCustomerData($username, $password) {
		
		$query = $this->db->query("SELECT *	
				FROM " . $this->db->table("customers") . "
				WHERE LOWER(`loginname`) = LOWER('" . $this->db->escape($username) . "')
				AND password = '" . $this->db->escape(AEncryption::getHash($password)) . "'");
		
		$data = $query->row;
		if($data && $data['status'] && $data['approved']) {
			$sql = "INSERT INTO " . $this->db->table("blog_user") . " 
								SET customer_id = '" . (int)$data['customer_id'] . "',
									status = '" . (int)$data['status'] . "',
									source = 'customer',
									role_id = '4', 
									username = '" . $this->db->escape($data['loginname']) . "',
									approve = '" . (int)$data['approve'] . "',
									user_approve_comments = '1',
									date_added = '" . $this->db->escape($data['date_added']) . "'";
			
			$this->db->query($sql);
			
			$customerData = $this->getUserData($data['loginname']);
			return $customerData;
			
		}else{
			return false;
		}
	}
	
	public function customerToBlogUser($customer_id) {
		
		if($customer_id) {	
			$query = $this->db->query("SELECT *	
				FROM " . $this->db->table("customers") . "
				WHERE customer_id = '" . $customer_id . "'");
		
			$data = $query->row;
	
			$sql = "INSERT INTO " . $this->db->table("blog_user") . " 
					SET customer_id = '" . (int)$customer_id . "',
						status = '" . (int)$data['status'] . "',
						source = 'customer',
						role_id = '4', 
						username = '" . $this->db->escape($data['loginname']) . "',
						approve = '" . (int)$data['approved'] . "',
						user_approve_comments = '0',
						date_added = '" . $this->db->escape($data['date_added']) . "'";
			
			$this->db->query($sql);
			return TRUE;
		}else{
			return false;
		}
	}
	
	public function activateBlogUser($customer_id) {
		
		if(!$customer_id) { return false; }
		
		$sql = "UPDATE " . $this->db->table("blog_user") . " 
					SET status = '1'
					WHERE customer_id = '" . (int)$customer_id ."'";
		$this->db->query($sql);
		return true;
	}
	
	public function recordView($blog_entry_id) {
		
		$query = $this->db->query("SELECT view FROM " . $this->db->table("blog_view") . " WHERE blog_entry_id = '" . (int)$blog_entry_id . "'");
		if ($query->row['view']) {
			$view = $query->row['view'] + 1;
			$this->db->query("UPDATE " . $this->db->table("blog_view") . " SET view = '" . (int)$view . "' WHERE blog_entry_id = '" . (int)$blog_entry_id . "'");
		}else{
			$view = '1';
			$this->db->query("INSERT INTO " . $this->db->table("blog_view") . " 
								  SET blog_entry_id = '" . (int)$blog_entry_id . "',
									  view = '" . $view . "',
									  date_added = NOW()");
		}
		
		return $view;
	}
	
	public function getPopularEntries($limit) {
		$cache = $this->cache->get('blog.popular.entries.' . $limit, $this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
		if (is_null($cache)){
			$query = $this->db->query("SELECT bv.view, bv.blog_entry_id, bed.entry_title
					FROM " . $this->db->table("blog_view") . " bv
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
							ON (bv.blog_entry_id = bed.blog_entry_id)
					LEFT JOIN " . $this->db->table("blog_entry") . " be
							ON (bv.blog_entry_id = be.blog_entry_id)
					WHERE be.status = '1' 
					AND bed.language_id = '" .(int)$this->config->get('storefront_language_id'). "'
					ORDER BY view ASC
					LIMIT 0," . (int)$limit . "");
		
			$cache = $query->rows;
			$this->cache->set('blog.popular.entries.' . $limit, $cache, $this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
		}

		return $cache;
		
	}
	
	public function getActiveEntries($limit) {
		$cache = $this->cache->get('blog.active.entries.' . $limit, $this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
		if (is_null($cache)){
			$query = $this->db->query("SELECT be.blog_entry_id, bed.entry_title, 
										( SELECT COUNT(*) as cnt 
										FROM " . $this->db->table("blog_comment") . " bc
										WHERE be.blog_entry_id = bc.blog_entry_id AND bc.approved = '1') as comments_count
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
						ON (be.blog_entry_id = bed.blog_entry_id)
				WHERE be.status = '1' AND allow_comment = '1'
				AND bed.language_id = '" .(int)$this->config->get('storefront_language_id'). "'
				ORDER BY comments_count ASC
				LIMIT 0, " . $limit . "");

			$cache = $query->rows;
			$this->cache->set('blog.active.entries.' . $limit, $cache, $this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
		}

		return $cache;
	}
	
	public function getLatestActivity($limit) {
		$cache = $this->cache->get('blog.latest.activity.' . $limit, $this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
		if (is_null($cache)){
			$latest = array();
			$query1 = $this->db->query("SELECT be.blog_entry_id, bed.entry_title, be.date_modified
					FROM " . $this->db->table("blog_entry") . " be
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
							ON (be.blog_entry_id = bed.blog_entry_id)
					WHERE be.status = '1' 
					AND bed.language_id = '" .(int)$this->config->get('storefront_language_id'). "'
					ORDER BY be.date_modified DESC
					LIMIT 0, " . $limit . "");
			
			foreach ($query1->rows as $row) {
				$latest[] = array(
					'blog_entry_id' => $row['blog_entry_id'],
					'blog_comment_id' => '',	
					'entry_title' => $row['entry_title'],
					'type' => 'post',
					'date_modified' => $row['date_modified']
				);
			}
			
			$query2 = $this->db->query("SELECT bc.blog_entry_id, bed.entry_title, bc.date_modified, bc.blog_comment_id, bc.parent_id
					FROM " . $this->db->table("blog_comment") . " bc
					LEFT JOIN " . $this->db->table("blog_entry") . " be
							ON (be.blog_entry_id = bc.blog_entry_id)
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
							ON (be.blog_entry_id = bed.blog_entry_id)
					WHERE be.status = '1' AND be.allow_comment = '1'
					AND bed.language_id = '" .(int)$this->config->get('storefront_language_id'). "'
					AND bc.status = '1' AND bc.approved = '1'
					ORDER BY bc.date_modified DESC
					LIMIT 0, " . $limit . "");
			
			foreach ($query2->rows as $row) {
				$type = ($row['parent_id'] == 0 ? 'comment' : 'reply'); 
				$latest[] = array(
					'blog_entry_id' => $row['blog_entry_id'],
					'blog_comment_id' => $row['blog_comment_id'],
					'entry_title' => $row['entry_title'],
					'type' => $type,
					'date_modified' => $row['date_modified']
				);
			}
	
			$this->sortBy('date_modified', $latest, 'desc');	
			$cache = $latest;
			$this->cache->set('blog.latest.activity.' . $limit, $cache, $this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
		}

		return $cache;
	}
	
	private function sortBy($field, &$array, $direction = 'asc'){
		usort($array, create_function('$a, $b', '
			$a = $a["' . $field . '"];
			$b = $b["' . $field . '"];
	
			if ($a == $b)
			{
				return 0;
			}
	
			return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
		'));
	
		return true;
	}
	
	public function getUserNotifications($blog_user_id) {
		
		$results = array();
		$query = $this->db->query("SELECT bn.*, bed.entry_title  
					FROM " . $this->db->table("blog_notifications") . " bn
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
						ON (bn.blog_entry_id = bed.blog_entry_id)
					WHERE blog_user_id = '" . (int)$blog_user_id . "'
					AND status = '1'");
		
		foreach ($query->rows as $row) {
			if($row['all_comments']) {
				$results[] = array(
					'notification_id' => $row['notification_id'],
					'entry_title' => mb_substr($row['entry_title'], 0, 25) . '..',
					'type' => '1',
					'remove_url' => $this->blog_html->getSecureURL('blog/account/settings', '&id='. (int)$blog_user_id . '&remove='.$row['notification_id'].'&type=1'),
					'cust_remove_url' => $this->blog_html->getSecureURL('account/blog_settings', '&id='. (int)$blog_user_id . '&remove='.$row['notification_id'].'&type=1')
				);
			}
			if($row['on_reply']) {
				$results[] = array(
					'notification_id' => $row['notification_id'],
					'entry_title' => mb_substr($row['entry_title'], 0, 25) . '..',
					'type' => '2',
					'remove_url' => $this->blog_html->getSecureURL('blog/account/settings', '&id='. (int)$blog_user_id . '&remove='.$row['notification_id'].'&type=2'),
					'cust_remove_url' => $this->blog_html->getSecureURL('account/blog_settings', '&id='. (int)$blog_user_id . '&remove='.$row['notification_id'].'&type=2')
				);
			}
		}
		
		return $results;
	}
	
	public function editUserNotifications($notification_id, $type) {
		
		$query = $this->db->query("SELECT * FROM " . $this->db->table("blog_notifications") . " WHERE notification_id = '".$notification_id."'");
		
		if($query->row['all_comments'] && $query->row['on_reply']) {
			if($type == '1') {
				$type_field = 'all_comments';
			}else{
				$type_field = 'on_reply';
			}
			$this->db->query("UPDATE " . $this->db->table("blog_notifications") . " SET ".$type_field." = '(int)0' WHERE notification_id = '".(int)$notification_id."'");
		}else{
			$this->db->query("DELETE FROM " . $this->db->table("blog_notifications") . " WHERE notification_id = '".(int)$notification_id."'");
		}
	}
	
	public function getAllNotifications($blog_entry_id) {
		$query = $this->db->query("SELECT notification_id, user_name, email
				FROM " . $this->db->table("blog_notifications") . "
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'
				AND all_comments = '1'
				AND status = '1'");
				
		return $query->rows;
		
	}
	
	public function getReplyNotifications($blog_entry_id, $primary_comment_id) {
		$query = $this->db->query("SELECT notification_id, user_name, email
				FROM " . $this->db->table("blog_notifications") . "
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'
				AND blog_comment_id = '" . (int)$primary_comment_id . "'
				AND on_reply = '1'
				AND parent_id > 0
				AND status = '1'");
				
		return $query->rows;
		
	}
	
	public function addNotification($data) {
		if(!$data){
			return false;
		}
		
		$sql = "INSERT INTO " . $this->db->table("blog_notifications") . "
					SET blog_entry_id = '" . (int)$data['blog_entry_id'] . "',
						blog_comment_id = '" . (int)$data['blog_comment_id'] . "',
					 	primary_comment_id = '" . (int)$data['primary_comment_id'] . "',
						parent_id = '" . (int)$data['parent_id'] . "',
						blog_user_id = '" . (int)$data['blog_user_id'] . "',
						user_name = '" . $this->db->escape($data['username']) . "',
						email = '" . $this->db->escape($data['email']) . "',
						all_comments = '" . (int)$data['notification_all'] . "',
						on_reply = '" . (int)$data['notification_reply'] . "',
						status = '1',
						date_added = NOW()";
						
		$this->db->query($sql);
		
		return true;
			
	}
	
	public function addUser($data) {
		if(!is_array($data) || !$data){ return false;}
		
		$approve_user = $this->getblog_config('approve_user');
		$email_activation = $this->getblog_config('user_email_activation');
		$approve = '0';
		$status = '1';
		
		if($approve_user == 0 && $email_activation == 0) {
			$approve = '1';
		}

		$sql = "INSERT INTO " . $this->db->table("blog_user") . " 
								SET firstname = '" . $this->db->escape($data['first_name']) . "',
									lastname = '" . $this->db->escape($data['last_name']) . "',
									status = '" . (int)$status . "',
									source = 'self',
									role_id = '5', 
									username = '" . $this->db->escape($data['user_name']) . "',
									password = '" . $this->db->escape(AEncryption::getHash($data['password'])) . "',
									name_option = '" . (int)$data['name_option'] . "',
									email = '" . $this->db->escape($data['email']) . "',
									site_url = '" . $this->db->escape($data['site_url']) . "',
									users_tz = '" . $this->db->escape($data['users_tz']) . "',
									approve = '" . (int)$approve . "',
									date_added = NOW()";
		$this->db->query($sql);
		
		$blog_user_id = $this->db->getLastId();
		
		$this->db->query("UPDATE " . $this->db->table("blog_user") . " SET customer_id = '-" .(int)$blog_user_id . "'  WHERE blog_user_id = '" . (int)$blog_user_id . "'");	
		
        return $blog_user_id;
	}
	
	public function editUser($blog_user_id,$data) {
		if(!$blog_user_id){
			return false;
		}
		$this->db->query( "UPDATE " . $this->db->table("blog_user") . "
						   SET firstname = '" . $this->db->escape($data['first_name']) . "',
						   	   lastname = '" . $this->db->escape($data['last_name']) . "',
							   name_option = '" . (int)$data['name_option'] . "',
						       email = '" . $this->db->escape($data['email']) . "',
						       site_url = '" . $this->db->escape($data['site_url']) . "',
							   users_tz = '" . $this->db->escape($data['users_tz']) . "'
						   WHERE blog_user_id = '" . (int)$blog_user_id . "'");
		
		return true;	
	}
	
	public function getBlogUser($blog_user_id) {

		$query = $this->db->query("SELECT bu.*, bur.role_description as role
						FROM " . $this->db->table("blog_user") . " bu
						LEFT JOIN " . $this->db->table("blog_user_role") . " bur
							ON (bu.role_id = bur.role_id)
						WHERE blog_user_id = '" . (int)$blog_user_id . "'");
		
		return $query->row;
	}
	
	public function verifyUser($username, $password) {
		$query = $this->db->query("SELECT *
				FROM " . $this->db->table("blog_user") . "
				WHERE LOWER(`username`)  = LOWER('" . $this->db->escape($username) . "')
				AND password = '" . $this->db->escape(AEncryption::getHash($password)) . "'
				AND status = '1'
				AND approve = '1'");
		if(	$query->row) {
			return true;
    	} else {
      		return false;
    	}		
	}
	
	public function editUserApproval($blog_user_id, $approve) {
		
		$blog_user_id = (int)$blog_user_id;
		$approve = (int)$approve;
		if(!$blog_user_id){ return false; }
		$this->db->query( "UPDATE " . $this->db->table("blog_user") . "
						   SET approve = '" . (int)$approve . "'
						   WHERE blog_user_id = '" . $blog_user_id . "'" );
		return true;	
	}
	
	public function isUniqueBlogUser($username) {
		if( empty($username) ) {
			return false;
		}
      	$query = $this->db->query("SELECT COUNT(*) AS total
      	                           FROM " . $this->db->table("blog_user") . "
      	                           WHERE LOWER(`username`) = LOWER('" . $username . "')");
      	if ($query->row['total'] > 0) {
      		return false;
      	} else {
      		return true;
      	}                           
	}
	
	public function getBlogUserByEmailUsername($email, $username) {
		if( empty($username) ) {
			return false;
		}
		$query = $this->db->query("SELECT blog_user_id
					FROM " . $this->db->table("blog_user") . "
					WHERE email = '" . $email . "'
					AND LOWER(`username`) = LOWER('" . $username . "')");

		return $query->row['blog_user_id'];	
		
	}
	
	public function verifyUserPassword($blog_user_id, $password) {
		if( empty($blog_user_id) ) {
			return false;
		}
		$query = $this->db->query("SELECT *
				FROM " . $this->db->table("blog_user") . "
				WHERE blog_user_id = '" . (int)$blog_user_id . "'
				AND password = '" . $this->db->escape(AEncryption::getHash($password)) . "'");
				
		if($query->row) {
			return true;
    	} else {
      		return false;
    	}		
	}
	
	public function editUserPassword($blog_user_id, $password) {
		if( empty($blog_user_id) ) {
			return false;
		}	
		$this->db->query( "UPDATE " . $this->db->table("blog_user") . "
						   SET password = '" . $this->db->escape(AEncryption::getHash($password)) . "'
						   WHERE blog_user_id = '" . (int)$blog_user_id . "'");
		return true;
	}
	
	public function getBlogUserByEmail($email) {
		$sql = "SELECT COUNT(*) AS total
				FROM " . $this->db->table("blog_user") . "
				WHERE LOWER(`email`) = LOWER('" . $this->db->escape($email) . "')";

		$query = $this->db->query($sql);
		
		if ($query->row['total'] > 0) {
      		return false;
      	} else {
      		return true;
      	}
	}
	
	public function getAuthorEntries($blog_author_id, $start = 0, $limit = 0, $mode = 'default'){
		$sort_order = $this->getblog_config('entry_display_order');
		$page_limit = $this->getblog_config('entries_per_main_page');
		
		if ($mode == 'total_only') {
			$select_columns = 'count(*) as total';
		}
		else {
			$select_columns = "be.*, bed.entry_title, bed.entry_intro, bed.content, CONCAT(ba.firstname, ' ',ba.lastname) as author_name, bv.view, ba.show_author_page,
						( SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_comment") . " bc
						WHERE be.blog_entry_id = bc.blog_entry_id ) as comments_count";	
		}
		
		$sql = "SELECT " . $select_columns . " 
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (be.blog_entry_id = bed.blog_entry_id
						AND bed.language_id = '" . (int)$this->config->get('storefront_language_id') . "')
				LEFT JOIN " . $this->db->table('blog_author')." ba				
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table('blog_view')." bv				
					ON (be.blog_entry_id = bv.blog_entry_id)
				WHERE be.status = '1' AND ba.blog_author_id = '" . $blog_author_id . "' AND be.release_date > 0 AND be.release_date <= NOW()";	
				
		$sql .= " ORDER BY be.release_date ".$sort_order."";
		
		if ($mode == 'total_only') {
			$query = $this->db->query($sql);
			return $query->row['total'];
		}
		
		if($mode == 'id_only') {
			//get all entries without limit
			$query = $this->db->query($sql);
			$id_only = array();
			foreach ($query->rows as $result) {
				$id_only[] = $result['blog_entry_id'];		
			}
			return $id_only;
		}
				
		if (isset($start) || isset($limit)) {
			$start = $start ? $start : 0;
			$limit = $limit ? $limit : $page_limit;

			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
		}

		$query = $this->db->query($sql);
		$blog_entry_data = array();
		foreach ($query->rows as $result) {
			$blog_entry_data[] = array(
				'blog_entry_id' 	=> $result['blog_entry_id'],
				'blog_author_id' 	=> $result['blog_author_id'],
				'entry_title'	 	=> $result['entry_title'],
				'use_intro'			=> $result['use_intro'],
				'entry_intro'		=> $result['entry_intro'],
				'content'			=> $result['content'],
				'release_date' 		=> $result['release_date'],
				'date_modified' 	=> $result['date_modified'],
				'allow_comment' 	=> $result['allow_comment'],
				'author_name' 		=> $result['author_name'],
				'show_author_page'	=> $result['show_author_page'],
				'comments_count' 	=> $result['comments_count'],
				'view'				=> $result['view'],
				'status'			=> $result['status']
			);
		}		
		return $blog_entry_data;
	}
	
	public function getTotalAuthorEntries($blog_author_id, $start = 0, $limit = 0) {
		return $this->getAuthorEntries($blog_author_id, $start, $limit, 'total_only');
	}
	
	public function getAuthors($limit) {
		$query = $this->db->query("SELECT ba.blog_author_id, CONCAT(ba.firstname, ' ',ba.lastname) as author_name, 
										( SELECT COUNT(*) as cnt 
										FROM " . $this->db->table("blog_entry") . " be
										WHERE be.blog_author_id = ba.blog_author_id AND be.status = '1') as entry_count
				FROM " . $this->db->table("blog_author") . " ba
				WHERE ba.status = '1' AND ba.show_author_page = '1'
				ORDER BY author_name ASC
				LIMIT 0, " . $limit . "");
		
		return $query->rows;
	}

	public function getLanguageCode() {
		
		$query = $this->db->query("SELECT code
						FROM " . $this->db->table("languages") . "
						WHERE language_id = '" .(int)$this->config->get('storefront_language_id'). "'");
						
		return $query->row['code'];	
	}
	
	public function get_tz_list() {
	  	$zones_array = array();
	  	$timestamp = time();
	  	foreach(timezone_identifiers_list() as $key => $zone) {
			date_default_timezone_set($zone);
			$zones_array[$key]['zone'] = $zone;
			$zones_array[$key]['GMT_diff'] = 'UTC/GMT ' . date('P', $timestamp);
	  	}
	  return $zones_array;
	}
	
	public function getLastEntryDate() {
			
		$query_mod = $this->db->query("SELECT MAX(date_modified) as mm_date FROM " . $this->db->table("blog_entry") . " WHERE status = '1'");
		$query_rel = $this->db->query("SELECT MAX(release_date) as mr_date FROM " . $this->db->table("blog_entry") . " WHERE status = '1' AND release_date > 0 AND release_date <= NOW()");
		
		$max_modified_date = $query_mod->row['mm_date'];
		$max_release_date = $query_mod->row['mr_date'];
		
		$lastEntryDate = ($max_modified_date > $max_release_date ? $max_modified_date : $max_release_date);

		return $lastEntryDate;

	}
	
	public function getLastCommentDate($blog_entry_id) {
			
		$query_mod = $this->db->query("SELECT MAX(date_modified) as mm_date 
			FROM " . $this->db->table("blog_comment") . " 
			WHERE blog_entry_id = '" .$blog_entry_id . "' AND status = '1'");
		
		$max_modified_date = $query_mod->row['mm_date'];

		return $max_modified_date;

	}
	
	private function _sql_final_price_string(){
		//special prices
		if ($this->customer->isLogged()){
			$customer_group_id = (int)$this->customer->getCustomerGroupId();
		} else{
			$customer_group_id = (int)$this->config->get('config_customer_group_id');
		}

		$sql = " ( SELECT p2sp.price
					FROM " . $this->db->table("product_specials") . " p2sp
					WHERE p2sp.product_id = p.product_id
							AND p2sp.customer_group_id = '" . $customer_group_id . "'
							AND ((p2sp.date_start = '0000-00-00' OR p2sp.date_start < NOW())
							AND (p2sp.date_end = '0000-00-00' OR p2sp.date_end > NOW()))
					ORDER BY p2sp.priority ASC, p2sp.price ASC LIMIT 1
				 ) ";
		$sql = "COALESCE( " . $sql . ", p.price) as final_price";

		return $sql;
	}
	
	private function _sql_avg_rating_string(){
		$sql = " ( SELECT AVG(r.rating)
						 FROM " . $this->db->table("reviews") . " r
						 WHERE p.product_id = r.product_id
						 GROUP BY r.product_id 
				 ) AS rating ";
		return $sql;
	}
	
	private function _sql_review_count_string(){
		$sql = " ( SELECT COUNT(rw.review_id)
						 FROM " . $this->db->table("reviews") . " rw
						 WHERE p.product_id = rw.product_id
						 GROUP BY rw.product_id
				 ) AS review ";
		return $sql;
	}
	
	public function getTotalBlogCategoriesByBlogCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total
					FROM " . $this->db->table("blog_category") . " bc
					WHERE bc.parent_id = '" . (int)$parent_id . "'
					AND bc.status = '1'");
		
		return $query->row['total'];
	}
	
}
