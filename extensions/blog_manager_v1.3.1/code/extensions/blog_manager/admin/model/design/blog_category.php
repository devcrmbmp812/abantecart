<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}


/**
 * Class ModelDesignBlogCategory
 */
class ModelDesignBlogCategory extends Model {
	/**
	 * @param $data
	 * @return int
	 */
	public function addBlogCategory($data) {
		$sql = "INSERT INTO " . $this->db->table("blog_category") . " 
						  SET parent_id = '" . (int)$data['parent_id'] . "',
						      sort_order = '" . (int)$data['sort_order'] . "',
						      status = '" . (int)$data['status'] . "',
						      date_added = NOW()";
		$this->db->query($sql);
		$blog_category_id = $this->db->getLastId();
		
		foreach ($data['blog_category_description'] as $language_id => $value) {
			$this->language->replaceDescriptions('blog_category_description',
											 array('blog_category_id' => (int)$blog_category_id),
											 array($language_id => array(
												 						'name' => $value['name'],
																		'page_title' => $value['page_title'],
												 						'meta_keyword' => $value['meta_keywords'],
												 						'meta_description' => $value['meta_description'],
												 						'description' => $value['description']
											 )) );
		}
		
		if ($data['keyword']) {
			$seo_key = SEOEncode($data['keyword'],'blog_category_id',$blog_category_id);
		}else {
			//Default behavior to save SEO URL keword from blog category name in default language
			/**
			 * @var ALanguageManager
			 */
			$seo_key = SEOEncode( $data['blog_category_description'][$this->language->getDefaultLanguageID()]['name'],
								'blog_category_id',
								$blog_category_id );
		}
		if($seo_key){
			$this->language->replaceDescriptions('url_aliases',
												array('query' => "blog_category_id=" . (int)$blog_category_id),
												array((int)$this->language->getContentLanguageID() => array('keyword'=>$seo_key)));
		}else{
			$this->db->query("DELETE
							FROM " . $this->db->table("url_aliases") . " 
							WHERE query = 'blog_category_id=" . (int)$blog_category_id . "'
								AND language_id = '".(int)$this->language->getContentLanguageID()."'");
		}

		$this->cache->delete('blog_category');

		return $blog_category_id;
	}

	/**
	 * @param int $blog_category_id
	 * @param array $data
	 */
	public function editBlogCategory($blog_category_id, $data) {

		$fields = array('parent_id', 'sort_order', 'status');
		$update = array('date_modified = NOW()');
		foreach ( $fields as $f ) {
			if ( isset($data[$f]) )
				$update[] = $f." = '".(is_int($data[$f]) ? (int)$data[$f] : $this->db->escape($data[$f]))."'";
		}
		if ( !empty($update) ) $this->db->query("UPDATE " . $this->db->table("blog_category") . " SET ". implode(',', $update) ." WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		if ( !empty($data['blog_category_description']) ) {
			foreach ($data['blog_category_description'] as $language_id => $value) {
				$update = array();
				if ( isset($value['name']) ){
					$update["name"] = $value['name'];
				}
				if ( isset($value['page_title']) ){
					$update["page_title"] = $value['page_title'];
				}
				if ( isset($value['description']) ){
					$update["description"] = $value['description'];
				}
				if ( isset($value['meta_keyword']) ){
					$update["meta_keyword"] = $value['meta_keyword'];
				}
				if ( isset($value['meta_description']) ){
					$update["meta_description"] = $value['meta_description'];
				}
				if ( !empty($update) ){
					// insert or update
					$this->language->replaceDescriptions('blog_category_description',
														 array('blog_category_id' => (int)$blog_category_id),
														 array($language_id => $update) );
				}
			}
		}

		if (isset($data['keyword'])) {
			$data['keyword'] =  SEOEncode($data['keyword']);
			if($data['keyword']){
			$this->language->replaceDescriptions('url_aliases',
												array('query' => "blog_category_id=" . (int)$blog_category_id),
												array((int)$this->language->getContentLanguageID() => array('keyword' => $data['keyword'])));
			}else{
				$this->db->query("DELETE
								FROM " . $this->db->table("url_aliases") . " 
								WHERE query = 'blog_category_id=" . (int)$blog_category_id . "'
									AND language_id = '".(int)$this->language->getContentLanguageID()."'");
			}

		}

		$this->cache->delete('blog_category');

	}

	/**
	 * @param int $blog_category_id
	 */
	public function deleteBlogCategory($blog_category_id) {

		$this->db->query("DELETE FROM " . $this->db->table("blog_category") . " WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_category_description") . " WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("url_aliases") . " WHERE query = 'blog_category_id=" . (int)$blog_category_id . "'");
		//delete resources
		$rm = new AResourceManager();
		$resources = $rm->getResourcesList(
				array(
						'object_name' => 'blog_category',
						'object_id'   => (int)$blog_category_id)
		);
		foreach($resources as $r){
			$rm->unmapResource(
					'blog_category',
					$blog_category_id,
					$r['resource_id']
			);
			//if resource become orphan - delete it
			if(!$rm->isMapped($r['resource_id'])){
				$rm->deleteResource($r['resource_id']);
			}
		}
		//move entries in category to uncategorized
		$this->db->query("UPDATE  " . $this->db->table("blog_entry_to_category") . " SET blog_category_id = '1' WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		
		//delete children categories
		$query = $this->db->query("SELECT blog_category_id FROM " . $this->db->table("blog_category") . " WHERE parent_id = '" . (int)$blog_category_id . "'");
		
		$lm = new ALayoutManager();
		foreach ($query->rows as $result) {
			$this->deleteBlogCategory($result['blog_category_id']);
			$lm->deletePageLayout('pages/design/blog_category','path',$result['blog_category_id']);
		}
		$lm->deletePageLayout('pages/blog/blog_category','blog_category_id',$blog_category_id);
		
		$this->cache->delete('blog_category');
	}

	/**
	 * @param int $blog_category_id
	 * @return array
	 */
	public function getBlogCategory($blog_category_id) {
		$query = $this->db->query("SELECT DISTINCT *,
										(SELECT keyword
										FROM " . $this->db->table("url_aliases") . " 
										WHERE query = 'blog_category_id=" . (int)$blog_category_id . "'
											AND language_id='".(int)$this->language->getContentLanguageID()."' ) AS keyword
									FROM " . $this->db->table("blog_category") . " 
									WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		
		return $query->row;
	}



	/**
	 * @param array $data
	 * @param string $mode
	 * @return array|int
	 */
	public function getBlogCategoriesData($data, $mode = 'default') {

		if ( $data['language_id'] ) {
			$language_id = (int)$data['language_id'];
		} else {
			$language_id = (int)$this->language->getContentLanguageID();
		}

		$filter = (isset($data['filter']) ? $data['filter'] : array());

		if ($mode == 'total_only') {
			$select_columns = 'count(*) as total';
		}
		else {
			$select_columns = "*,
						  bc.blog_category_id,
						  (SELECT count(*) as cnt
						  	FROM ".$this->db->table('blog_entry_to_category')." b2c
						  	WHERE bc.blog_category_id = b2c.blog_category_id) as entries_count ";
		}
        
		
		$sql = "SELECT ". $select_columns ."
				FROM " . $this->db->table('blog_category')." bc
				LEFT JOIN " . $this->db->table('blog_category_description')." bcd
					ON (bc.blog_category_id = bcd.blog_category_id AND bcd.language_id = '" . $language_id . "')
				";
				
		$sql .= "WHERE 1=1";
		
		if (isset($data['parent_id'])) { 
			$sql .= " AND bc.parent_id = '" . (int)$data['parent_id'] . "'";
		}
		

		if ( !empty($data['subsql_filter']) ) {
			$sql .= ($where ? " AND " : 'WHERE ').$data['subsql_filter'];
		}


		//If for total, we done bulding the query
		if ($mode == 'total_only') {
		    $query = $this->db->query($sql);
		    return $query->row['total'];
		}

		$sort_data = array(
		    'name' => 'bcd.name',
		    'status' => 'bc.status',
		    'sort_order' => 'bc.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data)) ) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY bc.sort_order, bcd.name ";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query = $this->db->query($sql);
		$blog_category_data = array();
		foreach ($query->rows as $result) {
			$blog_category_data[] = array(
				'blog_category_id' 	=> $result['blog_category_id'],
				'name'        		=> $this->getPath($result['blog_category_id'], $language_id),
				'basename'    		=> $result['name'],
				'status'  	  		=> $result['status'],
				'sort_order'  		=> $result['sort_order'],
				'article_count'		=> $result['entries_count']

			);
		}		
		return $blog_category_data;
	}
	
	public function getThisBlogCategories($parent_id, $current_category_id = null) {

		$language_id = $this->language->getContentLanguageID();
		$blog_category_data = array();
	
		$sql = "SELECT *
				FROM " . $this->db->table("blog_category") . " bc
				LEFT JOIN " . $this->db->table("blog_category_description") . " bcd
				ON (bc.blog_category_id = bcd.blog_category_id)
				WHERE bc.parent_id = '" . (int)$parent_id . "'
				AND bcd.language_id = '" . (int)$language_id . "'
				ORDER BY bc.sort_order, bcd.name ASC";

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			if($result['blog_category_id'] != $current_category_id) {
				
				$blog_category_data[] = array(
					'blog_category_id' 	=> $result['blog_category_id'],
					'name'        		=> $this->getPath($result['blog_category_id'], $language_id),
					'status' 			=> $result['status']
				);
				
				$blog_category_data = array_merge($blog_category_data, $this->getThisBlogCategories($result['blog_category_id'], $current_category_id));
			}	
		} 
			
		return $blog_category_data;	
		
	}
	
	/**
	 * @param int $category_id
	 * @return string
	 */
	public function getPath($blog_category_id) {
		$language_id = (int)$this->language->getContentLanguageID();
		$query = $this->db->query("SELECT name, parent_id
		                            FROM " . $this->db->table("blog_category") . " bc
		                            LEFT JOIN " . $this->db->table("blog_category_description") . " bcd
		                                ON (bc.blog_category_id = bcd.blog_category_id)
		                            WHERE bc.blog_category_id = '" . (int)$blog_category_id . "' AND bcd.language_id = '" . $language_id . "'
		                            ORDER BY bc.sort_order, bcd.name ASC");
		
		$blog_category_info = $query->row;
		
		if ($blog_category_info['parent_id']) {
			return $this->getPath($blog_category_info['parent_id'], $language_id) . $this->language->get('text_separator') . $blog_category_info['name'];
		} else {
			return $blog_category_info['name'];
		}
	}
	


	/**
	 * @param int $blog_category_id
	 * @return array
	 */
	public function getBlogCategoryDescriptions($blog_category_id) {
		$blog_category_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . $this->db->table("blog_category_description") . " WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		
		foreach ($query->rows as $result) {
			$blog_category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'page_title'       => $result['page_title'],
				'meta_keywords'    => $result['meta_keywords'],
				'meta_description' => $result['meta_description'],  
				'description'      => $result['description']
			);
		}
		
		return $blog_category_description_data;
	}


	/**
	 * @param array $data
	 * @return array
	 */
	public function getTotalBlogCategories($data = array()) {
		return $this->getBlogCategoriesData($data, 'total_only');
	}
	


	/**
	 * @return array
	 */
	public function getBlogLeafCategories() {
		$query = $this->db->query(
			"SELECT t1.blog_category_id as blog_category_id FROM " . $this->db->table("blog_category") . " AS t1 LEFT JOIN " . $this->db->table("blog_category") . " as t2
			 ON t1.blog_category_id = t2.parent_id WHERE t2.blog_category_id IS NULL");
		$result = array();
		foreach ( $query->rows as $r ) {
			$result[$r['blog_category_id']] = $r['blog_category_id'];
		}

		return $result;
	}
	
	/* added for multi-language functionality 
		* if language definition exists for uncategorized - use language defeinition
		* if not - use english definition
	*/
	
	public function getDefaultCategoryName() {
		$language_id = (int)$this->language->getContentLanguageID();
		$this->loadLanguage('blog_manager/blog_manager');
		
		$query = $this->db->query(
			"SELECT name FROM " . $this->db->table("blog_category_description") . "
			WHERE blog_category_id = '1'
			AND language_id = '" . $language_id . "'");	
		
		if($query->row['name']) {
			return $query->row['name'];
		}else{
			$query2 = $this->db->query(
				"SELECT name FROM " . $this->db->table("blog_category_description") . "
				WHERE blog_category_id = '1'");	
			
			return $query2->row['name'] . ' ' . $this->language->get('text_create_language_defintion');
		}
		
	}
}
