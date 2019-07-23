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

class ModelDesignBlogManager extends Model {


	public function addBlog($data) {
		if(!is_array($data) || !$data){ return false;}
		
		$store_id = $data['blog_store_id'];
		
		if(!$data['use_store_url']) {
		
			if(has_value($data['blog_url']) && substr($data['blog_url'],-1)!='/'){
				$data['blog_url'] .= '/';
			}
		
			if(has_value($data['blog_ssl_url']) && substr($data['blog_ssl_url'],-1)!='/'){
				$data['blog_ssl_url'] .= '/';
			}
		}
		$setting_fields = array('blog_url','blog_ssl_url','blog_ssl','use_store_url');
		
		foreach ($data as $key => $value) {

			$this->db->query("INSERT INTO " . $this->db->table("blog_settings") . " 
						  SET `key` = '" . $this->db->escape($key) . "',
							  `value` = '" . $this->db->escape($value) . "',
							  date_added = NOW()");
		    if(in_array($key,$setting_fields)) {
				$this->db->query("INSERT INTO " . $this->db->table("settings") . "
						 SET `key` = '" . $this->db->escape($key) . "',
							 `value` = '" . $this->db->escape($value) . "',
							 `group` = 'details',
							  store_id = '" . $store_id . "',
							  date_added = NOW()");
			}
		}
		
        return true;
	}

	/**
	 * @param int $blog_id
	 * @param array $data
	 * @return bool
	 */
	public function editBlog($data) {
		
		$store_id = $data['blog_store_id'];
		
		if($data['use_store_url'] == 1) {
			if(has_value($data['blog_url']) && substr($data['blog_url'],-1)!='/'){
				$data['blog_url'] .= '/';
			}
		
			if(has_value($data['blog_ssl_url']) && substr($data['blog_ssl_url'],-1)!='/'){
				$data['blog_ssl_url'] .= '/';
			}
		}
		if(is_array($data['customer_groups'])) {
			$data['customer_groups'] = implode(",",$data['customer_groups']);
		}
		
		$current_approve_state = $this->getblog_config('approve_comments');
		
		$setting_fields = array('blog_url','blog_ssl_url','blog_ssl','use_store_url');
		
		foreach ($data as $key => $value) {
			$query = $this->db->query("SELECT `key` FROM " . $this->db->table("blog_settings") . " WHERE `key` =  '" . $key . "'");
			if ($query->row) {  
				$this->db->query("UPDATE " . $this->db->table("blog_settings") . " SET `value` = '" . (is_int($value) ? (int)$value : $this->db->escape($value)) . "' WHERE `key` = '" . $key . "'");
			}else{
				$this->db->query("INSERT INTO " . $this->db->table("blog_settings") . " 
							  SET `key` = '" . $this->db->escape($key) . "',
								  `value` = '" . $this->db->escape($value) . "',
								  date_added = NOW()");
			}
			if(in_array($key,$setting_fields)) {
				$query_settings = $this->db->query("SELECT `key` FROM " . $this->db->table("settings") . " WHERE `key` =  '" . $key . "' AND store_id = '" . $store_id . "'");
				if ($query_settings->row) {
					$this->db->query("UPDATE " . $this->db->table("settings") . " SET `value` = '" . (is_int($value) ? (int)$value : $this->db->escape($value)) . "' WHERE `key` = '" . $key . "' AND store_id = '" . $store_id . "'");
				}else{
					$this->db->query("INSERT INTO " . $this->db->table("settings") . "
							 SET `key` = '" . $this->db->escape($key) . "',
								 `value` = '" . $this->db->escape($value) . "',
								  `group` = 'details',
								  store_id = '" . $store_id . "',
								  date_added = NOW()");
				}
			}
		}
		
		if($data['blog_access'] == 'all') {
			$query = $this->db->query("SELECT name FROM " . $this->db->table("customer_groups") . " WHERE name = 'Blog Users'");
			if(!$query->row) { 
				$this->db->query("DELETE FROM " . $this->db->table("customer_groups") . " WHERE `name` = 'Blog Users'");
			}
		
		}
		
		if($data['login_data'] == 'customer') {
			$query = $this->db->query("SELECT customer_id, loginname
								FROM " . $this->db->table("customers") . "
								WHERE status = '1' AND approved = '1' 
					  			AND store_id = '" . (int)$store_id . "' 
					  			AND customer_group_id IN('" . $this->getblog_config('customer_groups') . "')");		
			$customer_ids = array();
			foreach($query->rows as $row) {
				$this->db->query("INSERT INTO " . $this->db->table("blog_user") . " 
							  (customer_id, source, username, status, role_id, approve, user_approve_comments, date_added)
							  VALUES  ('" . (int)$row['customer_id'] . "', 'customer', '" . $this->db->escape($row['loginname']) . "', 1, 4, 1, 1, NOW())
							  ON DUPLICATE KEY UPDATE customer_id=customer_id");
			}
			
			$query = $this->db->query("SELECT name FROM " . $this->db->table("customer_groups") . " WHERE name = 'Blog Users'");
			if(!$query->row) { 
				$this->db->query("DELETE FROM " . $this->db->table("customer_groups") . " WHERE `name` = 'Blog Users'");
			}
			
		}elseif ($data['login_data'] == 'self') {
			$query = $this->db->query("SELECT name FROM " . $this->db->table("customer_groups") . " WHERE name = 'Blog Users'");
			if(!$query->row) { 
				$this->db->query("INSERT INTO " . $this->db->table("customer_groups") . " SET `name` = 'Blog Users'");
			}
			$this->db->query("DELETE FROM " . $this->db->table("blog_user") . " WHERE source = 'customer'");	
		}
		
		$ex_index_name = $this->getblog_config('search_index_name');
		
		if($data['search_type'] == 'simp_search') {
			if(isset($ex_index_name)) {
				$check_index = $this->db->query("SHOW INDEX FROM " . $this->db->table("blog_entry_description") . " WHERE KEY_NAME = '" . $ex_index_name . "'");
				if ($check_index->row) {	
					$this->db->query("ALTER TABLE " . $this->db->table("blog_entry_description") . " DROP INDEX " . $ex_index_name . "");
				}
			}
			$this->db->query("UPDATE " . $this->db->table("blog_settings") . " SET `value` = '' WHERE `key` = 'search_index_name'");
		}	
		
		if($data['search_type'] == 'full_search' || $data['search_type'] == 'extd_search') {
			
			$s_title = $this->getblog_config('search_article_title');
			$s_intro = $this->getblog_config('search_article_intro');
			$s_content = $this->getblog_config('search_article_content');
			$s_keywords = $this->getblog_config('search_meta_keywords');
			$s_description = $this->getblog_config('search_meta_desc');
			
			$field_names = array();
			$index_name = '';
			if($s_title) { 
				$index_name .= 't'; 
				array_push($field_names, 'entry_title'); 
			}  
			if($s_intro) { 
				$index_name .= 'i'; 
				array_push($field_names, 'entry_intro'); 
			}  
			if($s_content) { 
				$index_name .= 'c'; 
				array_push($field_names, 'content'); 
			}  
			if($s_keywords) { 
				$index_name .= 'k'; 
				array_push($field_names, 'meta_keywords'); 
			}  
			if($s_description) { 
				$index_name .= 'd'; 
				array_push($field_names, 'meta_description'); 
			}
			
			$ex_index_name = $this->getblog_config('search_index_name');
			
			if (isset($ex_index_name)) {
				if($ex_index_name != $index_name) {
					$check_index = $this->db->query("SHOW INDEX FROM " . $this->db->table("blog_entry_description") . " WHERE KEY_NAME = '" . $ex_index_name . "'");
					if ($check_index->row) {
						$this->db->query("ALTER TABLE " . $this->db->table("blog_entry_description") . " DROP INDEX " . $ex_index_name . "");	
					}
					$this->db->query("UPDATE " . $this->db->table("blog_settings") . " SET `value` = '" . $index_name . "' WHERE `key` = 'search_index_name'");
					$this->db->query("CREATE FULLTEXT INDEX " . $index_name . " ON " . $this->db->table("blog_entry_description") . "(" . implode(', ',$field_names ) . ")");
				}  // elseif $ex_index_name == $index_name : Do Nothing
			}else{
				$this->db->query("INSERT INTO " . $this->db->table("blog_settings") . " 
							  SET `key` = 'search_index_name',
								  `value` = '" . $this->db->escape($index_name) . "',
								  date_added = NOW()");
				$this->db->query("CREATE FULLTEXT INDEX " . $index_name . " ON " . $this->db->table("blog_entry_description") . "(" . implode(', ',$field_names ) . ")");
			}
		}

		return true;
	}

	/**
	 * @param int $blog_id
	 * @param int $language_id
	 * @return mixed
	 */
	public function getBlog() {
		$data = array();
		
		$sql = "SELECT `key`, `value`
				FROM " . $this->db->table("blog_settings") . "";			
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) {
			$data[$result['key']] = $result['value'];
		}
		
		$sql2 = "SELECT keyword
				FROM " . $this->db->table("url_aliases") . " 
				WHERE query = 'blog'";
		$query = $this->db->query($sql2);
		$data['keyword'] = $query->row['keyword'];
				
		return $data;
	}
	
	public function getblog_config($key) {
		
		$sql = "SELECT `value` 
						FROM " . $this->db->table("blog_settings") . "
						WHERE `key` = '" . $key . "'";
		
		$query = $this->db->query($sql);
		return $query->row['value'];
	}
	
	public function getCustomerGroups() {
		$query = $this->db->query("SELECT * 
				FROM " . $this->db->table("customer_groups") . "");
		
		return $query->rows;
	}
	
	
	
}
