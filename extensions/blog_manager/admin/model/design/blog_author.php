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

class ModelDesignBlogAuthor extends Model {
	
	
	public function addAuthor($data) {
		if(!is_array($data) || !$data){ return false;}
		
		
		$sql = "INSERT INTO " . $this->db->table("blog_author") . " 
								SET blog_user_id = '" . (int)$data['blog_user_id'] . "',
									status = '" . (int)$data['status'] . "',
									firstname = '" . $this->db->escape($data['firstname']) . "',
									lastname = '" . $this->db->escape($data['lastname']) . "',
									role_id = '" . (int)$data['role_id'] . "', 
									contact_info = '" . $this->db->escape($data['contact_info']) . "',
									email = '" . $this->db->escape($data['email']) . "',
									site_url = '" . $this->db->escape($data['site_url']) . "',
									show_author_page = '" . (int)$data['show_author_page'] . "',
									show_details = '" . (int)$data['show_details'] . "',
									show_details_ap = '" . (int)$data['show_details_ap'] . "',
									show_author_link = '" . (int)$data['show_author_link'] . "',
									date_added = NOW()";
		$this->db->query($sql);
		$blog_author_id = $this->db->getLastId();
		if(isset($data['role_id'])) {
			$this->db->query("UPDATE " . $this->db->table("blog_user") . " SET role_id = '" . (int)$data['role_id'] . "' WHERE blog_user_id = '" . (int)$data['blog_user_id'] . "'");
		}
		
		if($this->getblog_config('blog_access') == "all" && !$data['blog_user_id']) {
			$this->db->query("UPDATE " . $this->db->table("blog_author") . " SET blog_user_id = " . $blog_author_id . " WHERE blog_author_id = '" . (int)$blog_author_id . "'");	
		}
		
		
		foreach($data['blog_author_descriptions'] as $language_id => $value){
			$this->language->replaceDescriptions('blog_author_description',
											 array('blog_author_id' => (int)$blog_author_id),
											 array(( int )$language_id => array(
											 				'author_description' => $value['author_description'],
															'author_title' => $value['author_title'],
												 			'meta_keywords' => $value['meta_keywords'],
												 			'meta_description' => $value['meta_description']							 						
			)));
		}
		
		if ($data['keyword']) {
			$seo_key = SEOEncode($data['keyword'],
				'blog_author_id',
				$blog_author_id);
		}
		if ($seo_key) {
			$this->language->replaceDescriptions('url_aliases',
				array('query' => "blog_author_id=" . (int)$blog_author_id),
				array((int)$this->language->getContentLanguageID() => array('keyword' => $seo_key)));
		} else {
			$this->db->query("DELETE
							FROM " . $this->db->table("url_aliases") . " 
							WHERE query = 'blog_author_id=" . (int)$blog_author_id . "'
								AND language_id = '" . (int)$this->language->getContentLanguageID() . "'");
		}
		
		return $blog_author_id;
		
	}

	/**
	 * @param int $blog_author_id
	 * @param array $data
	 * @return bool
	 */
	public function editAuthor($blog_author_id, $data) {
		if(!$blog_author_id){return false;}
		$language_id = (int)$this->language->getContentLanguageID();
		
		$fields = array( 
			'status',
			'firstname',
			'lastname',
			'role_id',
			'contact_info',
			'email',
			'site_url',
			'show_author_page',
			'show_details',
			'show_details_ap',
			'show_author_link',
		);
		
		foreach ( $fields as $f ) {
			if ( isset($data[$f]) ) {
				$update[] = $f." = '".(is_int($data[$f]) ? (int)$data[$f] : $this->db->escape($data[$f]))."'";
			}
		}
		
		if ( !empty($data['password']) ) {
			$update[] = "password = '". $this->db->escape(AEncryption::getHash($data['password'])) ."'";
		}
		
		if ( !empty($update) ) $this->db->query("UPDATE " . $this->db->table("blog_author") . " SET ". implode(',', $update) ." WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		if($this->getblog_config('blog_access') == "all") {
			$this->db->query("UPDATE " . $this->db->table("blog_author") . " SET blog_user_id = " . $blog_author_id . " WHERE blog_author_id = '" . (int)$blog_author_id . "'");	
		}
		
		if(isset($data['status']) && $this->getblog_config('blog_access') == "restrict") {
			$this->db->query("UPDATE " . $this->db->table("blog_user") . " SET status = ". $data['status'] ." WHERE blog_user_id = '" . (int)$data['blog_user_id'] . "'");
		}
		if(isset($data['role_id']) && $this->getblog_config('blog_access') == "restrict") {
			$this->db->query("UPDATE " . $this->db->table("blog_user") . " SET role_id = ". $data['role_id'] ." WHERE blog_user_id = '" . (int)$data['blog_user_id'] . "'");
		}
		
		if ( !empty($data['blog_author_descriptions']) ) {
			foreach ($data['blog_author_descriptions'] as $language_id => $value) {
				$update = array();
				if ( isset($value['author_title']) ){
					$update["author_title"] = $value['author_title'];
				}
				if ( isset($value['author_description']) ){
					$update["author_description"] = $value['author_description'];
				}
				if ( isset($value['meta_keyword']) ){
					$update["meta_keyword"] = $value['meta_keyword'];
				}
				if ( isset($value['meta_description']) ){
					$update["meta_description"] = $value['meta_description'];
				}
				if ( !empty($update) ){
					// insert or update
					$this->language->replaceDescriptions('blog_author_description',
														 array('blog_author_id' => (int)$blog_author_id),
														 array($language_id => $update) );
				}
			}
		}
		
		if ($data['keyword']) {
			$seo_key = SEOEncode($data['keyword'],'blog_author_id',$blog_author_id);
		}else {
			$name = $data['firstname'] . ' ' . $data['lastname'];
			$seo_key = SEOEncode( $name,
								'blog_author_id',
								$blog_author_id );
		}
		
		if($seo_key){
			$this->language->replaceDescriptions('url_aliases',
												array('query' => "blog_author_id=" . (int)$blog_author_id),
												array((int)$this->language->getContentLanguageID() => array('keyword'=>$seo_key)));
		}else{
			$this->db->query("DELETE
							FROM " . $this->db->table("url_aliases") . " 
							WHERE query = 'blog_author_id=" . (int)$blog_author_id . "'
								AND language_id = '".(int)$this->language->getContentLanguageID()."'");
		}
		
		$this->cache->delete('blogs');
		return true;
	}

	/**
	 * @param int $blog_author_id
	 */
	public function deleteAuthor($blog_author_id) {
		$lm = new ALayoutManager();
		$lm->deletePageLayout('pages/design/blog_author','blog_author_id',( int )$blog_author_id);
		if($this->getblog_config('blog_access') == "restrict") {
			$blog_user = $this->db->query("SELECT blog_user_id FROM " . $this->db->table("blog_author") . " WHERE blog_author_id = '" . ( int )$blog_author_id . "'");
			$blog_user_id = $blog_user->row['blog_user_id'];
			$this->db->query("UPDATE " . $this->db->table("blog_user") . " SET role_id = '5' WHERE blog_user_id = '" . ( int )$blog_user_id . "'");
		}
		$this->db->query("DELETE FROM " . $this->db->table("blog_author") . " WHERE blog_author_id = '" . ( int )$blog_author_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_author_description") . " WHERE blog_author_id = '" . ( int )$blog_author_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("url_aliases") . " WHERE query = 'blog_author_id=" . ( int )$blog_author_id . "'");
		$this->db->query("UPDATE " . $this->db->table("blog_entry") . " SET blog_author_id = '0' WHERE blog_author_id = '" . ( int )$blog_author_id . "'");

		$this->cache->delete('blogs');
	}
	
	public function getAuthorUserID($blog_author_id) {
			$sql = "SELECT blog_user_id
							 FROM " . $this->db->table("blog_author") . " 
							 WHERE blog_author_id = '" . ( int )$blog_author_id . "'";
		
			$query = $this->db->query($sql);
			
			return $query->row['blog_user_id'];		
		
	}

	/**
	 * @param int $blog_author_id
	 * @param int $language_id
	 * @return mixed
	 */
	public function getAuthor($blog_author_id, $language_id = null) {
		$output = array();
		$blog_author_id = (int)$blog_author_id;
		if ( !has_value($language_id) ) {
			$language_id = ( int )$this->language->getContentLanguageID();
		}
		
		if(!$blog_author_id){
			return false;
		}
		$sql = "SELECT DISTINCT ba.*, bad.*,
				(SELECT keyword FROM " . $this->db->table("url_aliases") . " 
						WHERE query = 'blog_author_id=" . (int)$blog_author_id . "'
							AND language_id='".(int)$this->language->getContentLanguageID()."' ) AS keyword
				FROM " . $this->db->table("blog_author") . " ba
				LEFT JOIN " . $this->db->table("blog_author_description") . " bad
					ON (ba.blog_author_id = bad.blog_author_id AND bad.language_id = '" .$language_id  . "')
				LEFT JOIN " . $this->db->table("blog_user") . " bu
					ON (ba.blog_user_id = bu.blog_user_id)
				WHERE ba.blog_author_id = '" . ( int )$blog_author_id . "'";
			
		$query = $this->db->query($sql);
		
		return $query->row;
	}
	
	public function getAuthorNames() {
		
		$query = $this->db->query("SELECT blog_author_id, firstname, lastname 
				FROM " . $this->db->table("blog_author") . "
				ORDER BY firstname ASC");

		return $query->rows;	
	}

	/**
	 * @param array $data
	 * @param string $mode
	 * @return array
	 */
	public function getAuthors($data = array(), $mode = 'default') {

		$filter = (isset($data['filter']) ? $data['filter'] : array());

		if ($mode == 'total_only') {
			$select_columns = 'count(*) as total';
		}
		else {
			$select_columns = "ba.*, bad.*, bur.role_description as role,
						( SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_entry") . " be
						WHERE ba.blog_author_id = be.blog_author_id ) as entries_count";
		}
	
		$sql = "SELECT ".$select_columns."
				FROM " . $this->db->table("blog_author") . " ba
				LEFT JOIN " . $this->db->table("blog_author_description") . " bad
					ON (ba.blog_author_id = bad.blog_author_id
						AND bad.language_id = '" . ( int )$this->language->getContentLanguageID() . "')
				LEFT JOIN " . $this->db->table("blog_user_role") . " bur
					ON (ba.role_id = bur.role_id)
				";
				
		$sql .= "WHERE 1=1 ";

		
		if (isset($filter['blog_author']) && $filter['blog_author'] != 0) {
			$sql .= " AND ba.blog_author_id = '" . $filter['blog_author'] . "'";
		}

		//if (!empty ($data ['subsql_filter'])) {
//			$sql .= " AND " . str_replace('`title`','ba.title',$data [ 'subsql_filter' ]);
//		}
		if (isset($filter['status']) && !is_null($filter['status'])) {
			$sql .= " AND ba.status = '" . (int)$filter['status'] . "'";
		}

		//If for total, we done bulding the query
		if ($mode == 'total_only') {
			$query = $this->db->query($sql);
			return $query->row['total'];
		}

		$sort_data = array(
			'blog_author_id '=> 'ba.blog_author_id',
			'author_title' => 'bad.author_title',
			'status' => 'ba.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data)) ) {
			$sql .= " ORDER BY " . $data ['sort'];
		} else {
			$sql .= " ORDER BY ba.blog_author_id";
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
		$blog_author_data = array();
		foreach ($query->rows as $result) {
			$blog_author_data[] = array(
				'blog_author_id' => $result['blog_author_id'],
				'name'    => $result['firstname'].' ' .$result['lastname'],
				'author_title' => $result['author_title'],
				'status'  	  => $result['status'],
				'entries_count'=>	$result['entries_count'],
				'role' => $result['role']
			);
		}		
		return $blog_author_data;

	}
	
	/**
	 * @param int $blog_author_id
	 * @param int $language_id
	 * @return array
	 */
	public function getAuthorDescriptions($blog_author_id, $language_id = 0) {
		$author_description_data = array();
		if ( !has_value($language_id) ) {
			$language_id = ( int )$this->language->getContentLanguageID();
		}

		$query = $this->db->query("SELECT *
									FROM " . $this->db->table("blog_author_description") . " 
									WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		foreach ($query->rows as $result) {
			$entry_description_data[$result['language_id']] = array(
				'author_title' => $result['author_title'],
				'author_description' => $result['author_description'],
				'meta_keyword' => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
			);
		}

		return $language_id ? $entry_description_data[$language_id] : $entry_description_data;
	}

	public function getTotalAuthors($data = array()) {
		return $this->getAuthors($data, 'total_only');
	}
	
	public function getUserData($blog_user_id) {
		if(!$blog_user_id){
			return false;
		}
		
		$query = $this->db->query("SELECT *
				FROM " . $this->db->table("blog_user") . "
				WHERE status = '1' AND approve = '1'
				AND blog_user_id = '" . $blog_user_id . "'"); 
		
		if($query->row['source'] == 'customer') {
			$cust_query = $this->db->query("SELECT firstname, lastname, loginname, email
					FROM " . $this->db->table("customers") . "
					WHERE status = '1' AND approved = '1'
					AND customer_id = '" . $query->row['customer_id'] . "'");
		}
		
		$data = array(
			'blog_user_id' => $blog_user_id,
			'firstname' => $query->row['firstname'] ? $query->row['firstname'] : (isset($cust_query->row['firstname']) ? $cust_query->row['firstname'] : ''), 
			'lastname' => $query->row['lastname'] ? $query->row['lastname'] : (isset($cust_query->row['lastname']) ? $cust_query->row['lastname'] : ''),
			'email' => $query->row['email'] ? $query->row['email'] : (isset($cust_query->row['email']) ? $cust_query->row['email'] : ''),
			'username' => $query->row['username'] ? $query->row['username'] : (isset($cust_query->row['loginname']) ? $cust_query->row['loginname'] : ''),
			'site_url' => $query->row['site_url'] ? $query->row['site_url'] : (isset($cust_query->row['site_url']) ? $cust_query->row['site_url'] : ''),
		);
		
		return $data;	
		
	}
	
	public function getActiveUsers() {
		
		$query = $this->db->query("SELECT *
				FROM " . $this->db->table("blog_user") . " bu
				WHERE status = '1' AND bu.blog_user_id NOT IN (SELECT blog_user_id FROM " . $this->db->table('blog_author') . ")");
					

		$blog_user_data = array();
		foreach ($query->rows as $result) {
			$name = $result['firstname'] .' ' .$result['lastname'];
			if($result['source'] == 'customer') {
				$cust_query = $this->db->query("SELECT firstname, lastname FROM " . $this->db->table("customers") . " WHERE customer_id = '" . $result['customer_id'] . "'");
				$name = $cust_query->row['firstname'] . ' '  . $cust_query->row['lastname'];
			}

			$blog_user_data[] = array(
				'blog_user_id' => $result['blog_user_id'],
				'name'     => $name,
				'username' => $result['username']
			);
		}		
		return $blog_user_data;
	}
	
	public function getblog_config($key) {
		
		$sql = "SELECT `value` 
						FROM " . $this->db->table("blog_settings") . "
						WHERE `key` = '" . $key . "'";
		
		$query = $this->db->query($sql);
		return $query->row['value'];
	}

}
