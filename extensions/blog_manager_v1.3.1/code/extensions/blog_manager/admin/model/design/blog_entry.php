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

class ModelDesignBlogEntry extends Model {


	public function addEntry($data) {
		if(!is_array($data) || !$data){ return false;}
		if ($data['release_date']) {
			$registry = Registry::getInstance();
			$format = $registry->get('language')->get('date_format_short');
			$data['release_date'] = date('Y-m-d', dateFromFormat($data['release_date'], $format));
		}
		
		$sql = "INSERT INTO " . $this->db->table("blog_entry") . "
								SET blog_author_id = '" . (int)$data['blog_author_id'] . "',
									status = '" . (int)$data['status'] . "',
									use_intro = '" . (int)$data['use_intro'] . "',
									use_image = '" . (int)$data['use_image'] . "',
									allow_comment = '" . (int)$data['allow_comment'] . "',
									release_date = '" . $this->db->escape($data['release_date']) . "', 
									date_added = NOW()";
		$this->db->query($sql);
		$blog_entry_id = $this->db->getLastId();

		foreach($data['blog_entry_descriptions'] as $language_id => $value){
			$this->language->replaceDescriptions('blog_entry_description',
											 array('blog_entry_id' => (int)$blog_entry_id),
											 array(( int )$language_id => array(
											 				'entry_title' => $value[ 'entry_title' ],
															'entry_intro' => $value['entry_intro'],
															'content' => $value['content'],
															'reference' => $value['reference'],
															'entries_lead' => $value['entries_lead'],
															'category_lead' => $value['category_lead'],
															'product_lead' => $value['product_lead'],
												 			'meta_keywords' => $value['meta_keywords'],
												 			'meta_description' => $value['meta_description'],
															'copyright' => $value['copyright']								 						
			)));
		}
		if ($data['blog_category']) {
			foreach ($data['blog_category'] as $blog_category_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_entry_to_category") . " (blog_entry_id,blog_category_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$blog_category_id . "')";
					$this->db->query($sql);
			}
		}
		if ($data['related_category']) {
			foreach ($data['related_category'] as $category_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_entry_category_related") . " (blog_entry_id,category_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$category_id . "')";
					$this->db->query($sql);
			}
		}
		if ($data['related_products']) {
			foreach ($data['related_products'] as $product_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_entry_product_related") . " (blog_entry_id,product_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$product_id . "')";
					$this->db->query($sql);
			}
		}
		
		if ($data['related_entries']) {
			foreach ($data['related_entries'] as $blog_entry_related_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_related_entry") . " (blog_entry_id,blog_entry_related_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$blog_entry_related_id . "')";
					$this->db->query($sql);
			}
		}
		
		if ($data['keyword']) {
			$seo_key = SEOEncode($data['keyword'],
				'blog_entry_id',
				$blog_entry_id);
		}
		if ($seo_key) {
			$this->language->replaceDescriptions('url_aliases',
				array('query' => "blog_entry_id=" . (int)$blog_entry_id),
				array((int)$this->language->getContentLanguageID() => array('keyword' => $seo_key)));
		} else {
			$this->db->query("DELETE
							FROM " . $this->db->table("url_aliases") . " 
							WHERE query = 'blog_entry_id=" . (int)$blog_entry_id . "'
								AND language_id = '" . (int)$this->language->getContentLanguageID() . "'");
		}

		$this->cache->delete('blog');
        return $blog_entry_id;
	}

	/**
	 * @param int $blog_id
	 * @param array $data
	 * @return bool
	 */
	public function editEntry($blog_entry_id, $data) {
		if(!$blog_entry_id){return false;}
		$language_id = (int)$this->language->getContentLanguageID();
		
		$fields = array(
			'status', 
			'use_intro',
			'use_image',
			'blog_author_id', 
			'allow_comment', 
			'release_date'
		);
		

		foreach ( $fields as $f ) {
			if ( isset($data[$f]) ) {
				if ($f == 'release_date') {
					$registry = Registry::getInstance();
					$format = $registry->get('language')->get('date_format_short');
					$data['release_date'] = date('Y-m-d', dateFromFormat($data['release_date'], $format));
				}
				$update[] = $f." = '".(is_int($data[$f]) ? (int)$data[$f] : $this->db->escape($data[$f]))."'";
			}
		}
		if ( !empty($update) ) $this->db->query("UPDATE " . $this->db->table("blog_entry") . " SET ". implode(',', $update) ." WHERE blog_entry_id = '" . (int)$blog_entry_id . "'");
		
		if ( !empty($data['blog_entry_descriptions']) ) {
			foreach ($data['blog_entry_descriptions'] as $language_id => $value) {
				$update = array();
				if ( isset($value['entry_title']) ){
					$update["entry_title"] = $value['entry_title'];
				}
				if ( isset($value['entry_intro']) ){
					$update["entry_intro"] = $value['entry_intro'];
				}
				if ( isset($value['content']) ){
					$update["content"] = $value['content'];
				}
				if ( isset($value['reference']) ){
					$update["reference"] = $value['reference'];
				}
				if ( isset($value['entries_lead']) ){
					$update["entries_lead"] = $value['entries_lead'];
				}
				if ( isset($value['category_lead']) ){
					$update["category_lead"] = $value['category_lead'];
				}
				if ( isset($value['product_lead']) ){
					$update["product_lead"] = $value['product_lead'];
				}
				if ( isset($value['meta_keywords']) ){
					$update["meta_keywords"] = $value['meta_keywords'];
				}
				if ( isset($value['meta_description']) ){
					$update["meta_description"] = $value['meta_description'];
				}
				if ( isset($value['copyright']) ){
					$update["copyright"] = $value['copyright'];
				}
				if ( !empty($update) ){
					// insert or update
					$this->language->replaceDescriptions('blog_entry_description',
														 array('blog_entry_id' => (int)$blog_entry_id),
														 array($language_id => $update) );
				}
			}
		}
		
		if (isset($data['blog_category'])) {
			$sql = "DELETE FROM " . $this->db->table("blog_entry_to_category") . " WHERE blog_entry_id='" . $blog_entry_id . "'";
			$this->db->query($sql);
			
			foreach ($data['blog_category'] as $blog_category_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_entry_to_category") . " (blog_entry_id,blog_category_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$blog_category_id . "')";
					$this->db->query($sql);
			}
		}
		if (isset($data['related_category'])) {
			$sql = "DELETE FROM " . $this->db->table("blog_entry_category_related") . " WHERE blog_entry_id='" . $blog_entry_id . "'";
			$this->db->query($sql);
			
			foreach ($data['related_category'] as $category_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_entry_category_related") . " (blog_entry_id,category_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$category_id . "')";
					$this->db->query($sql);
			}
		}
		if (isset($data['related_products'])) {
			$sql = "DELETE FROM " . $this->db->table("blog_entry_product_related") . " WHERE blog_entry_id='" . $blog_entry_id . "'";
			$this->db->query($sql);
			
			foreach ($data['related_products'] as $product_id) {
					$sql = "INSERT INTO " . $this->db->table("blog_entry_product_related") . " (blog_entry_id,product_id)
								VALUES ('" . $blog_entry_id . "','" . (int)$product_id . "')";
					$this->db->query($sql);
			}
		}
		if (isset($data['related_entries'])) {
			$sql = "DELETE FROM " . $this->db->table("blog_related_entry") . " WHERE blog_entry_id='" . $blog_entry_id . "'";
			$this->db->query($sql);
			
			foreach ($data['related_entries'] as $blog_entry_related_id) {	
				$this->db->query("INSERT INTO " . $this->db->table("blog_related_entry") . " SET blog_entry_id = '" . (int)$blog_entry_id . "', blog_entry_related_id = '" . (int)$blog_entry_related_id . "'");
				$this->db->query("DELETE FROM " . $this->db->table("blog_related_entry") . " WHERE blog_entry_id = '" . (int)$blog_entry_related_id . "' AND blog_entry_related_id = '" . (int)$blog_entry_id . "'");
				$this->db->query("INSERT INTO " . $this->db->table("blog_related_entry") . " SET blog_entry_id = '" . (int)$blog_entry_related_id . "', blog_entry_related_id = '" . (int)$blog_entry_id . "'");
			}
		}
		
		if (isset($data['keyword'])) {
			$data['keyword'] = SEOEncode($data['keyword'], 'blog_entry_id', $blog_entry_id);
			if ($data['keyword']) {
				$this->language->replaceDescriptions('url_aliases',
					array('query' => "blog_entry_id=" . (int)$blog_entry_id),
					array((int)$this->language->getContentLanguageID() => array('keyword' => $data['keyword'])));
			} else {
				$this->db->query("DELETE
								FROM " . $this->db->table("url_aliases") . " 
								WHERE query = 'blog_entry_id=" . (int)$blog_entry_id . "'
									AND language_id = '" . (int)$this->language->getContentLanguageID() . "'");
			}
		}
		
		$this->cache->delete('blogs');
		return true;
	}

	/**
	 * @param int $blog_id
	 */
	public function deleteEntry($blog_entry_id) {
		$lm = new ALayoutManager();
		$lm->deletePageLayout('pages/design/blog_entry','blog_entry_id',(int)$blog_entry_id);

		$this->db->query("DELETE FROM " . $this->db->table("blog_entry") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_entry_description") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_comment") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_entry_to_category") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_entry_category_related") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_entry_product_related") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_related_entry") . " WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_notifications") . " WHERE blog_entry_id = '" . (int)$blog_entry_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_view") . " WHERE blog_entry_id = '" . (int)$blog_entry_id . "'");
		//delete resources
		$rm = new AResourceManager();
		$resources = $rm->getResourcesList(
				array(
						'object_name' => 'blog_entry',
						'object_id'   => (int)$blog_entry_id)
		);
		foreach($resources as $r){
			$rm->unmapResource(
					'blog_entry',
					$blog_entry_id,
					$r['resource_id']
			);
			//if resource become orphan - delete it
			if(!$rm->isMapped($r['resource_id'])){
				$rm->deleteResource($r['resource_id']);
			}
		}

		$this->cache->delete('blogs');
	}

	/**
	 * @param int $blog_id
	 * @param int $language_id
	 * @return mixed
	 */
	public function getEntry($blog_entry_id, $language_id = null) {
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if ( !has_value($language_id) ) {
			$language_id = ( int )$this->language->getContentLanguageID();
		}
		
		if(!$blog_entry_id){
			return false;
		}
		$sql = "SELECT be.*, bed.*,
					(SELECT keyword
					 FROM " . $this->db->table("url_aliases") . " 
					 WHERE query = 'blog_entry_id=" . (int)$blog_entry_id . "'
						AND language_id='" . $language_id . "' ) AS keyword
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (be.blog_entry_id = bed.blog_entry_id AND bed.language_id = '" .$language_id  . "')
				WHERE be.blog_entry_id = '" . ( int )$blog_entry_id . "'";
				
		$query = $this->db->query($sql);
		
		return $query->row;
	}
	
	/**
	 * @param int $blog_entry_id
	 * @param int $language_id
	 * @return array
	 */
	public function getEntryDescriptions($blog_entry_id, $language_id = 0) {
		$entry_description_data = array();
		if ( !has_value($language_id) ) {
			$language_id = ( int )$this->language->getContentLanguageID();
		}

		$query = $this->db->query("SELECT *
									FROM " . $this->db->table("blog_entry_description") . " 
									WHERE blog_entry_id = '" . (int)$blog_entry_id . "'");

		foreach ($query->rows as $result) {
			$entry_description_data[$result['language_id']] = array(
				'entry_title' => $result['entry_title'],
				'entry_intro' => $result['entry_intro'],
				'content' => $result['content'],
				'reference' => $result['reference'],
				'entries_lead' => $result['entries_lead'],
				'category_lead' => $result['category_lead'],
				'product_lead' => $result['product_lead'],
				'meta_keywords' => $result['meta_keywords'],
				'meta_description' => $result['meta_description'],
				'copyright' => $result['copyright'],
			);
		}

		return $language_id ? $entry_description_data[$language_id] : $entry_description_data;
	}
	
	public function getBlogCategories($blog_entry_id) {
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sql = "SELECT *
				FROM " . $this->db->table("blog_entry_to_category") . "
				WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'";
				
		$query = $this->db->query($sql);

		$data = array();
		foreach($query->rows as $result) {
			$data[] = $result['blog_category_id'];
		}
		return $data;
		
	}
	
	public function getRelatedCategories($blog_entry_id) {
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sql = "SELECT *
				FROM " . $this->db->table("blog_entry_category_related") . "
				WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'";
				
		$query = $this->db->query($sql);

		$data = array();
		foreach($query->rows as $result) {
			$data[] = $result['category_id'];
		}
		return $data;
		
	}
	
	public function getRelatedProducts($blog_entry_id) {
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sql = "SELECT *
				FROM " . $this->db->table("blog_entry_product_related") . "
				WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'";
				
		$query = $this->db->query($sql);

		$data = array();
		foreach($query->rows as $result) {
			$data[] = $result['product_id'];
		}
		return $data;
	}
	
	public function getRelatedEntries($blog_entry_id) {	
		
		$output = array();
		$blog_entry_id = (int)$blog_entry_id;
		if(!$blog_entry_id){
			return false;
		}
		
		$sql = "SELECT *
				FROM " . $this->db->table("blog_related_entry") . "
				WHERE blog_entry_id = '" . ( int )$blog_entry_id . "'";
				
		$query = $this->db->query($sql);

		$data = array();
		foreach($query->rows as $result) {
			$data[] = $result['blog_entry_related_id'];
		}
		return $data;
		
	}
	
	public function getCategories($parent_id) {
		$language_id = $this->language->getContentLanguageID();

		if (!$category_data) {
			$category_data = array();

			$sql = "SELECT *
					FROM " . $this->db->table("categories") . " c
					LEFT JOIN " . $this->db->table("category_descriptions") . " cd
					ON (c.category_id = cd.category_id) ";

			$sql .=	"WHERE c.parent_id = '" . (int)$parent_id . "'
						AND cd.language_id = '" . (int)$language_id . "'
						AND c.status = '1' 
					ORDER BY c.sort_order, cd.name ASC";

			$query = $this->db->query($sql);

			foreach ($query->rows as $result) {
				$category_data[] = array(
					'category_id' => $result['category_id'],
					'parent_id'   => $result['parent_id'],
					'name'        => $this->getPath($result['category_id'], $language_id),
					'status'  	  => $result['status'],
					'sort_order'  => $result['sort_order']
				);

				$category_data = array_merge($category_data, $this->getCategories($result['category_id']));
			}

		}

		return $category_data;
	}

	public function getPath($category_id) {
		$language_id = (int)$this->language->getContentLanguageID();
		$query = $this->db->query("SELECT name, parent_id
		                            FROM " . $this->db->table("categories") . " c
		                            LEFT JOIN " . $this->db->table("category_descriptions") . " cd
		                                ON (c.category_id = cd.category_id)
		                            WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . $language_id . "'
		                            ORDER BY c.sort_order, cd.name ASC");
		
		$category_info = $query->row;
		
		if ($category_info['parent_id']) {
			return $this->getPath($category_info['parent_id'], $language_id) . $this->language->get('text_separator') . $category_info['name'];
		} else {
			return $category_info['name'];
		}
	}


	/**
	 * @return array
	 */
	public function getBlogsEntriesForSelect() {
		
		
		$sql = "SELECT be.blog_entry_id, bed.entry_title 
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table('blog_entry_description')." bed
					ON (be.blog_entry_id = bed.blog_entry_id)
				WHERE be.status = 1";	
						
		$query = $this->db->query($sql);
		
		$data = array();
		foreach($query->rows as $result) {
			$data[] = array(
				'blog_entry_id' => $result['blog_entry_id'],
				'entry_name' => $result['entry_title'],
			);
		}
		return $data;	
	}
	
	

	/**
	 * @param array $data
	 * @param string $mode
	 * @param bool $parent_only
	 * @return array
	 */
	public function getEntries($data = array(), $mode = 'default') {
		if($data[ "subsql_filter" ]){
			$data[ "subsql_filter" ] .= ' AND ';
		}

		$filter = (isset($data['filter']) ? $data['filter'] : array());
		if ($mode == 'total_only') {
			$select_columns = 'count(*) as total';
		}
		else {
			$select_columns = "*, be.status, be.blog_entry_id,
						( SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_comment") . " bc
						WHERE be.blog_entry_id = bc.blog_entry_id ) as comments_count";
		}
	
		$sql = "SELECT ".$select_columns."
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
					ON (be.blog_entry_id = bed.blog_entry_id
						AND bed.language_id = '" . ( int )$this->language->getContentLanguageID() . "')
				LEFT JOIN " . $this->db->table('blog_author')." ba				
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table('blog_view')." bv				
					ON (be.blog_entry_id = bv.blog_entry_id)
				";
		if (isset($filter['blog_category']) && $filter['blog_category'] > 0) {
				$sql .= " LEFT JOIN " . $this->db->table("blog_entry_to_category") . " b2c ON (be.blog_entry_id = b2c.blog_entry_id) ";
			}

		$sql .= "WHERE 1=1";

		if (isset($filter['blog_author']) && $filter['blog_author'] != 0) {
			$sql .= " AND ba.blog_author_id = '" . $filter['blog_author'] . "'";
		}
		
		if (isset($filter['blog_category']) && $filter['blog_category'] != 0) {
			$sql .= " AND b2c.blog_category_id = '" . $filter['blog_category'] . "'";
		}

		if (!empty ($data [ 'subsql_filter' ])) {
			$sql .= " AND " . str_replace('`title`','bed.entry_title',$data [ 'subsql_filter' ]);
		}


		if (isset($filter['status']) && !is_null($filter['status'])) {
			$sql .= " AND be.status = '" . (int)$filter['status'] . "'";
		}

		//If for total, we done bulding the query
		if ($mode == 'total_only') {
			$query = $this->db->query($sql);
			return $query->row['total'];
		}

		$sort_data = array(
			'blog_entry_id '=> 'be.blog_entry_id',
			'title' => 'bed.entry_title',
			'status' => 'be.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data)) ) {
			$sql .= " ORDER BY " . $data ['sort'];
		} else {
			$sql .= " ORDER BY be.blog_entry_id";
		}

		if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset ($data [ 'start' ]) || isset ($data [ 'limit' ])) {
			if ($data [ 'start' ] < 0) {
				$data [ 'start' ] = 0;
			}

			if ($data [ 'limit' ] < 1) {
				$data [ 'limit' ] = 20;
			}

			$sql .= " LIMIT " . ( int )$data [ 'start' ] . "," . ( int )$data [ 'limit' ];
		}

		$query = $this->db->query($sql);
		$blog_entry_data = array();
		foreach ($query->rows as $result) {
			$blog_entry_data[] = array(
				'blog_entry_id' => $result['blog_entry_id'],
				'entry_title' => $result['entry_title'],
				'author'	  => $result['firstname'].' '.$result['lastname'],
				'status'  	  => $result['status'],
				'views'	      => $result['view'],
				'release_date' => $result['release_date'],
				'comments_count'=>$result['comments_count']

			);
		}		
		return $blog_entry_data;

	}

	public function getTotalEntries($data = array()) {
		return $this->getEntries($data, 'total_only');
	}
	
	public function getLatestArticles($start, $limit) {
		
		$this->loadloadLanguage('blog_manager/blog_entry');

		$query = $this->db->query("SELECT be.blog_entry_id, bed.entry_title, be.date_modified
					FROM " . $this->db->table("blog_entry") . " be
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
							ON (be.blog_entry_id = bed.blog_entry_id)
					WHERE be.status = '1' 
					ORDER BY be.date_modified DESC
					LIMIT " . $start . ", " . $limit . "");
					
		$result = array();
		foreach($query->rows as $row) {
			$result[] = array(
				'blog_entry_id' => $row['blog_entry_id'],
				'entry_title' => $row['entry_title'],
				'date_modified' => dateISO2Display($row['date_modified'], $this->language->get('date_format_short'))
			);
		}
		return $result;	
	}
	
	public function getEntriesCount($status) {
		$query = $this->db->query("SELECT count(*) as total
			FROM " . $this->db->table("blog_entry") . "
			WHERE status = '" . (int)$status . "'");
			
		return $query->row['total'];
	}
	
	public function toggleAllowComment($blog_entry_id) {
		
		$sql = "SELECT allow_comment
				FROM " . $this->db->table("blog_entry") . "	
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'";
		
		$query = $this->db->query($sql);

		if($query->row['allow_comment'] == 1) {
			$allow_commment = 0;
		} else{
			$allow_commment = 1;
		}
		$sql2 = "UPDATE " . $this->db->table("blog_entry") . "
				SET `allow_comment` = '" . (int)$allow_commment . "'
				WHERE blog_entry_id = '" . (int)$blog_entry_id . "'";
		
		$query = $this->db->query($sql2);
		return true;
				 
	}
	
	public function getblog_config($key) {
		
		$sql = "SELECT `value` 
						FROM " . $this->db->table("blog_settings") . "
						WHERE `key` = '" . $key . "'";
		
		$query = $this->db->query($sql);
		return $query->row['value'];
	}

}
