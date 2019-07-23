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

class ModelDesignBlogUser extends Model {


	public function addUser($data) {
		if(!is_array($data) || !$data){ return false;}
		
		
		$sql = "INSERT INTO " . $this->db->table("blog_user") . " 
								SET firstname = '" . $this->db->escape($data['firstname']) . "',
									lastname = '" . $this->db->escape($data['lastname']) . "',
									status = '" . (int)$data['status'] . "',
									source = '" . $this->db->escape($data['source']) . "',
									role_id = '" . (int)$data['role_id'] . "', 
									username = '" . $this->db->escape($data['username']) . "',
									name_option = '" . (int)$data['name_option'] . "',
									password = '" . $this->db->escape(AEncryption::getHash($data['password'])) . "',
									email = '" . $this->db->escape($data['email']) . "',
									users_tz = '" . $this->db->escape($data['users_tz']) . "',
									site_url = '" . $this->db->escape($data['site_url']) . "',
									admin_comment = '" . $this->db->escape($data['admin_comment']) . "', 
									approve = '" . (int)$data['approve'] . "',
									user_approve_comments = '" . (int)$data['user_approve_comments'] . "',
									user_require_approval = '" . (int)$data['user_require_approval'] . "',
									date_added = NOW()";
		$this->db->query($sql);
		
		$blog_user_id = $this->db->getLastId();
		
		if($data['role_id'] == 2) { //author
			$sql = "INSERT INTO " . $this->db->table("blog_author") . " 
								SET blog_user_id = '" . (int)$blog_user_id . "',
									site_url = '" . $this->db->escape($data['site_url']) . "',
									date_added = NOW()";
			$this->db->query($sql);
		}
		if($data['source'] == 'self') {
			$this->db->query("UPDATE " . $this->db->table("blog_user") . " SET customer_id = '-" .(int)$blog_user_id . "'  WHERE blog_user_id = '" . (int)$blog_user_id . "'");	
		}
		
		$this->cache->delete('blog');
        return $blog_user_id;
	}

	/**
	 * @param int $blog_user_id
	 * @param array $data
	 * @return bool
	 */
	public function editUser($blog_user_id, $data) {
		if(!$blog_user_id){return false;}
		
		$fields = array(
			'status',
			'role_id', 
			'firstname',
			'lastname',
			'name_option',
			'email',
			'users_tz',
			'site_url', 
			'approve',
			'username',
			'admin_comments',
			'user_approve_comments',
			'user_require_approval'
		);
		
		foreach ( $fields as $f ) {
			if ( isset($data[$f]) )
				$update[] = $f." = '".(is_int($data[$f]) ? (int)$data[$f] : $this->db->escape($data[$f]))."'";
		}
		
		if ( !empty($data['password']) ) {
				$update[] = "password = '". $this->db->escape(AEncryption::getHash($data['password'])) ."'";
		}
				
		if ( !empty($update) ) $this->db->query("UPDATE " . $this->db->table("blog_user") . " SET ". implode(',', $update) ." WHERE blog_user_id = '" . (int)$blog_user_id . "'");

		unset($fields);
		
		$this->cache->delete('blogs');
		return true;
	}

	/**
	 * @param int $blog_user_id
	 */
	public function deleteUser($blog_user_id) {
		$author = $this->db->query("SELECT blog_user_id FROM " . $this->db->table("blog_author") . " WHERE blog_user_id = '" . ( int )$blog_user_id . "'");
		if($author->row['blog_user_id']) {
			$details = $this->db->query("SELECT username, password FROM " . $this->db->table("blog_user") . " WHERE blog_user_id = '" . ( int )$blog_user_id . "'");
			$this->db->query("UPDATE " . $this->db->table("blog_author") . " SET blog_user_id = '0', username = '" .$details->row['username'] ."', password = '" . $details->row['password'] ."' WHERE blog_user_id = '" . (int)$blog_user_id . "'");
		}

		$this->db->query("DELETE FROM " . $this->db->table("blog_user") . " WHERE blog_user_id = '" . ( int )$blog_user_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_notifications") . " WHERE  blog_user_id = '" . (int)$blog_user_id . "'");
		$this->db->query("UPDATE " . $this->db->table("blog_comment") . " SET blog_user_id = '0' WHERE blog_user_id = '" . ( int )$blog_user_id . "'");
		
		$this->cache->delete('blogs');
	}

	/**
	 * @param int $blog_user_id
	 * @return array
	 */
	public function getUser($blog_user_id) {
		$output = array();
		$blog_user_id = (int)$blog_user_id;
		if(!$blog_user_id){
			return false;
		}
		$store_id = (int)$this->getblog_config('blog_store_id');
		
		$query = $this->db->query("SELECT *
					FROM " . $this->db->table("blog_user") . " bu
					WHERE bu.blog_user_id = '" . ( int )$blog_user_id . "'");
		
		$user_data = $query->row;
		
		if($query->row['source'] == 'customer') {
			$cust_query = $this->db->query("SELECT firstname, lastname, loginname as username, email
					FROM " . $this->db->table("customers") . "
					WHERE customer_id = '" . (int)$query->row['customer_id'] . "' AND store_id = '" . $store_id . "'");
			$cust_data = $cust_query->row;
		}
		
		$data = ($cust_data ? array_merge($user_data, $cust_data) : $user_data);
		
		return $data;
	}

	/**
	 * @param array $data
	 * @param string $mode
	 * @return array
	 */
	public function getUsers($data = array(), $non_author = 'default', $mode = 'default') {

		
		$source = $this->getblog_config('login_data');
		
		$filter = (isset($data['filter']) ? $data['filter'] : array());

		if ($mode == 'total_only') {
			$select_columns = 'count(*) as total';
		}else{
		
			$select_columns = "bu.status, bu.source, bu.blog_user_id, bu.customer_id, bu.approve, bu.firstname, bu.lastname, bu.username, br.role_description,
				( SELECT COUNT(*) as cnt 
							FROM " . $this->db->table("blog_comment") . " bc
							WHERE bu.blog_user_id = bc.blog_user_id ) as comments_count";
		}
		$sql = "SELECT ".$select_columns."
				FROM " . $this->db->table("blog_user") . " bu
				LEFT JOIN " . $this->db->table("blog_user_role") . " br
					ON (bu.role_id = br.role_id)
				";
				 
		$sql .= "WHERE 1=1 ";

		if (isset($filter['role_id']) && $filter['role_id'] != 0) { 
			$sql .= " AND bu.role_id = '" . (int)$filter['role_id'] . "'";
		}

		if ( !empty($data['subsql_filter']) ) {
			$sql .= ($where ? " AND " : 'WHERE ').$data['subsql_filter'];
		}
		
		if($non_author == 'non_author') {
			$sql .= " AND bu.blog_user_id NOT IN (SELECT blog_user_id FROM " . $this->db->table('blog_author') . ")";
			
		}
		
		//If for total, we're done bulding the query
		if ($mode == 'total_only') {
			$query = $this->db->query($sql);
			return $query->row['total'];
		}

		$sort_data = array(
			'title' => 'username',
			'status' => 'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data)) ) {
			$sql .= " ORDER BY " . $data ['sort'];
		} else {
			$sql .= " ORDER BY username";
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
				'username' => $result['username'],
				'role_description' => $result['role_description'],
				'status'   => $result['status'],
				'approve'  => $result['approve'],
				'comments_count' => $result['comments_count'],
				'source' => $result['source']
			);
		}		
		return $blog_user_data;

	}

	public function getTotalUsers($data = array()) {
		return $this->getUsers($data, '', 'total_only');
	}
	
	public function getUserCount($approved_status) {
		$query = $this->db->query("SELECT count(*) as total
			FROM " . $this->db->table("blog_user") . "
			WHERE status = '1' AND approve = '" . (int)$approved_status . "'");
			
		return $query->row['total'];
	}
	
	public function getUserRoles() {
		$sql = "SELECT *	
				FROM " . $this->db->table("blog_user_role") . "
				ORDER BY role_description ASC";
		
		$query = $this->db->query($sql);
		$roles = array();
		foreach ($query->rows as $result) {
			
			$roles[] = array(
				'role_id' => $result['role_id'],
				'role_description' => $result['role_description'],
			);
		}		
		return $roles;
	}
	
	public function getBlogAdmins() {
		$query = $this->db->query("SELECT bu.blog_user_id, bu.firstname, bu.lastname, bur.role_description
				FROM " . $this->db->table("blog_user") . " 	bu
				LEFT JOIN " . $this->db->table("blog_user_role") . " bur
					ON (bu.role_id = bur.role_id)
				WHERE bu.role_id = '1' ");
				
		return $query->rows;
			
	}
	
	public function getblog_config($key) {
		
		$sql = "SELECT `value` 
						FROM " . $this->db->table("blog_settings") . "
						WHERE `key` = '" . $key . "'";
		
		$query = $this->db->query($sql);
		return $query->row['value'];
	}
	
	public function tz_list() {
	  	$zones_array = array();
	  	$timestamp = time();
	  	foreach(timezone_identifiers_list() as $key => $zone) {
			date_default_timezone_set($zone);
			$zones_array[$key]['zone'] = $zone;
			$zones_array[$key]['GMT_diff'] = 'UTC/GMT ' . date('P', $timestamp);
	  	}
	  return $zones_array;
	}

}
