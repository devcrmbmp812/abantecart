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
 * Class ModelDesignBlogComment
 */
class ModelDesignBlogComment extends Model {
	/**
	 * @param $data
	 * @return int
	 */
	public function addBlogComment($data) {
		
		if (isset($data['blog_user_id']) && $data['blog_user_id'] != '0') {
				
			$query = $this->db->query("SELECT username, email, site_url
							 FROM " . $this->db->table("blog_user") . "
							 WHERE blog_user_id =  '" . $data['blog_user_id'] . "'");
			
			$user_data = $query->row;
			
			if(!$user_data['email'] || !$user_data['site_url']) {
				$query = $this->db->query("SELECT email, site_url
							 FROM " . $this->db->table("blog_author") . "
							 WHERE blog_user_id =  '" . $data['blog_user_id'] . "'");
				$cust_data = $query->row;
			}
			
			$added_data = ($cust_data ? array_merge($user_data, $cust_data) : $user_data);			
		}else{ //must be owner
			$added_data['username'] = $this->getblog_config('owner');
			$added_data['email'] = $this->getblog_config('owner_email');
		}
		
		
		
		$sql = "INSERT INTO " . $this->db->table("blog_comment") . " 
						  SET blog_entry_id = '" . (int)$data['blog_entry_id'] . "',
						  	  parent_id = '" . (int)$data['parent_id'] . "',
							  primary_comment_id = '" . (int)$data['primary_comment_id'] . "',
						      username = '" . $this->db->escape($added_data['username']) . "',
							  email = '" . $this->db->escape($added_data['email']) . "',
							  site_url = '" . $this->db->escape($added_data['site_url']) . "',
							  blog_user_id = '" . (int)$data['blog_user_id'] . "',
							  status = '1',
							  comment = '" . $this->db->escape($data['comment']) . "',
							  approved = '" . (int)$data['approved'] . "',
						      date_added = NOW()";
		$this->db->query($sql);
		$blog_comment_id = $this->db->getLastId();
		
		if(!$data['primary_comment_id']) {
			$sql = "UPDATE " . $this->db->table("blog_comment") . " SET `primary_comment_id` = '" . (int)$blog_comment_id . "' WHERE blog_comment_id = '" . (int)$blog_comment_id . "'";		
		}

		$this->cache->delete('blog_comment');
		
		return $blog_comment_id;
	}

	/**
	 * @param int $blog_comment_id
	 * @param array $data
	 */
	public function editBlogComment($blog_comment_id, $data) {

		$fields = array('status', 'approved', 'username', 'blog_author_id', 'comment');
		
		foreach ( $fields as $f ) {
			if ( isset($data[$f]) ) {
				$update[] = $f." = '".(is_int($data[$f]) ? (int)$data[$f] : $this->db->escape($data[$f]))."'";
			}
		}
		$update[] = 'date_modified = NOW()';
		if ( !empty($update) ) $this->db->query("UPDATE " . $this->db->table("blog_comment") . " SET ". implode(',', $update) ." WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");
		
		$this->cache->delete('blog_comment');
		
		return;
	}
	
	
	/**
	 * @param int $blog_comment_id
	 */
	public function deleteBlogComment($blog_comment_id) {
		
		$this->db->query("DELETE FROM " . $this->db->table("blog_comment") . " WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("blog_notifications") . " WHERE blog_comment_id = '" . (int)$blog_comment_id . "' AND on_reply = '1'");
		//delete children comments
		$query = $this->db->query("SELECT blog_comment_id FROM " . $this->db->table("blog_comment") . " WHERE parent_id = '" . (int)$blog_comment_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteBlogComment($result['blog_comment_id']);
		}

		$this->cache->delete('blog_comment');
	}
	
	/**
	 * @param int $blog_entry_id
	 */
	public function getEntryAuthor($blog_entry_id) {
		if(!$blog_entry_id){
			return false;
		}
		
		$query = $this->db->query("SELECT ba.blog_user_id, ba.firstname, ba.lastname, bur.role_description
				FROM " . $this->db->table("blog_author") . " ba
				LEFT JOIN " . $this->db->table("blog_entry") . " be
					ON (be.blog_author_id = ba.blog_author_id)
				LEFT JOIN " . $this->db->table("blog_user_role") . " bur
					ON (ba.role_id = bur.role_id)
				WHERE be.blog_entry_id = '" . (int)$blog_entry_id . "'");
				
		return $query->row;
		
	}


	/**
	 * @param int $blog_comment_id
	 * @return array
	 */
	public function getBlogComment($blog_comment_id) {
		
		$blog_comment_id = (int)$blog_comment_id;
		if(!$blog_comment_id){
			return false;
		}
		$data = array();
		
		$query = $this->db->query("SELECT DISTINCT bc.*, be.allow_comment
				FROM " . $this->db->table("blog_comment") . " bc
				LEFT JOIN " . $this->db->table("blog_entry") . " be
					ON (bc.blog_entry_id = be.blog_entry_id)
				WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");

		foreach($query->row as $key => $value) {
			$data[$key] = $value; 	
		}
		
		$data['approve_comments'] = $this->getblog_config('approve_comments');
		
		return $data;
	}

	/**
	 * @param array $data
	 * @param string $mode
	 * @return array|int
	 */
	public function getBlogCommentsData($data, $mode = 'default') {

		if ( $data['language_id'] ) {
			$language_id = (int)$data['language_id'];
		} else {
			$language_id = (int)$this->language->getContentLanguageID();
		}
		
		$filter = (isset($data['filter']) ? $data['filter'] : array());

		if ($mode == 'total_only') {
			$total_sql = 'count(*) as total';
		}
		else {
			$total_sql = "bc.*, bed.entry_title";
		}
		
		$sql = "SELECT ". $total_sql ."
				FROM " . $this->db->table('blog_comment')." bc
				LEFT JOIN " . $this->db->table('blog_entry')." be
					ON (bc.blog_entry_id = be.blog_entry_id)
				LEFT JOIN " . $this->db->table('blog_entry_description')." bed
					ON (bc.blog_entry_id = bed.blog_entry_id AND bed.language_id = '" . $language_id . "')
				";
				
		$sql .= "WHERE 1=1";
		
		if (isset($data['parent_id'])) { 
			$sql .= " AND bc.parent_id = '" . (int)$data['parent_id'] . "'";
		}

		if (isset($filter['blog_entry_id']) && $filter['blog_entry_id'] != 0) {
			$sql .= " AND bc.blog_entry_id = '" . $filter['blog_entry_id'] . "'";
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
		    'release_date' => 'be.release_date',
			'date_added' => 'be.date_added'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data)) ) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY be.release_date, bc.date_added ";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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
		$blog_comment_data = array();
		foreach ($query->rows as $result) {
			
			$blog_comment_data[] = array(
				'blog_comment_id' => $result['blog_comment_id'],
				'primary_comment_id' => $result['primary_comment_id'],
				'blog_entry_id' => $result['blog_entry_id'],
				'parent_id' => $result['parent_id'],
				'entry_title' => $result['entry_title'],
				'username'	  => $result['username'],
				'blog_user_id' => $result['blog_user_id'],
				'site_url'	  => $result['site_url'],
				'email'		  => $result['email'],
				'comment'  	  => $this->truncate(html_entity_decode($result['comment'], ENT_QUOTES, 'UTF-8'),200,190),
				'status'  	  => $result['status'],
				'approved'    => $result['approved'],
				'date_added'  => $result['date_added'],
				'date_modified' => $result['date_modified'],
				'approval_req' => $this->getblog_config('approve_comments'),
			);
		}		
		return $blog_comment_data;
	}
	
	public function getEntryNames() {
		
		if ( $data['language_id'] ) {
			$language_id = (int)$data['language_id'];
		} else {
			$language_id = (int)$this->language->getContentLanguageID();
		}
		
		$sql = "SELECT be.blog_entry_id, bed.entry_title, allow_comment,
		 		 (SELECT COUNT(*) as cnt 
						FROM " . $this->db->table("blog_comment") . " bc
						WHERE be.blog_entry_id = bc.blog_entry_id ) as comments_count 
				FROM " . $this->db->table("blog_entry") . " be
				LEFT JOIN " . $this->db->table('blog_entry_description')." bed
					ON (be.blog_entry_id = bed.blog_entry_id AND bed.language_id = '" . $language_id . "')
				WHERE be.status = 1 ";	
						
		$query = $this->db->query($sql);

		$data = array();
		foreach($query->rows as $result) {
			$data[] = array(
				'blog_entry_id' => $result['blog_entry_id'],
				'entry_title' => $result['entry_title'],
				'comments_count' => $result['comments_count'],
				'allow_comment' => $result['allow_comment']
			);
		}
		return $data;	
		
	}
	
	public function getCommentReplyTotals($blog_comment_id, $primary_comment_id = 0, $blog_entry_id = 0) {
		$blog_comment_approval_totals = array();
	
		$sql = "SELECT blog_comment_id, primary_comment_id
				FROM " . $this->db->table("blog_comment") . "
				WHERE parent_id <> 0";
		
		if ($blog_comment_id == $primary_comment_id) {
			$sql .=  " AND primary_comment_id = '" . $primary_comment_id . "'";
		}else{
			$sql .=  " AND parent_id = '" . $blog_comment_id . "'";
		}
		
		if (isset($blog_entry_id) && $blog_entry_id != 0) {
			$sql .= " AND blog_entry_id = '" . $blog_entry_id . "'";
		}
		
		$query = $this->db->query($sql);
		
		foreach($query->rows as $row) {
				$blog_comment_ids[] = $row['blog_comment_id']; 
		}
		if(count($blog_comment_ids)) {
			$blog_comment_approval_totals = array(
				'total_approved' => $this->getApprovedReplies($blog_comment_ids),
				'total_unapproved' => $this->getUnApprovedReplies($blog_comment_ids)
			);
		}
		
		return $blog_comment_approval_totals;
		
	}
	
	public function getApprovedReplies($blog_comment_ids) {
		
		$sql = "SELECT count(*) as approved
				FROM " . $this->db->table("blog_comment") . " 
				WHERE blog_comment_id IN (" . implode(', ', $blog_comment_ids). ")
				AND approved = '1'";
				
		$query = $this->db->query($sql);
		return $query->row['approved'];
	}
	
	
	public function getUnApprovedReplies($blog_comment_ids) {
		
		$sql = "SELECT count(*) as un_approved
				FROM " . $this->db->table("blog_comment") . " 
				WHERE blog_comment_id IN (" . implode(', ', $blog_comment_ids). ")
				AND approved = '0'";
				
		$query = $this->db->query($sql);
		return $query->row['un_approved'];
		
	}


	/**
	 * @param array $data
	 * @return array
	 */
	public function getTotalBlogComments($data = array()) {
		return $this->getBlogCommentsData($data, 'total_only');
	}
	
	public function getTotalCommentsCount($approved_status) {
		$query = $this->db->query("SELECT count(*) as total
			FROM " . $this->db->table("blog_comment") . "
			WHERE status = '1' AND approved = '" . (int)$approved_status . "'");
			
		return $query->row['total'];
	}
	
	public function getLatestComments($start, $limit) {
		
		$this->loadloadLanguage('blog_manager/blog_entry');
		
		$query = $this->db->query("SELECT bc.blog_entry_id, bc.*, bed.entry_title
					FROM " . $this->db->table("blog_comment") . " bc
					LEFT JOIN " . $this->db->table("blog_entry") . " be
							ON (be.blog_entry_id = bc.blog_entry_id)
					LEFT JOIN " . $this->db->table("blog_entry_description") . " bed
							ON (be.blog_entry_id = bed.blog_entry_id)
					WHERE be.status = '1' AND be.allow_comment = '1'
					AND bc.status = '1'
					ORDER BY bc.date_modified DESC
					LIMIT " . $start . ", " . $limit . "");
		
		$parent_username = '';			
		foreach ($query->rows as $row) {
				$type = ($row['parent_id'] == 0 ? 'comment' : 'reply'); 
				if($type == 'reply') {
					$parent_username = $this->getCommentAuthor($row['parent_id']);	
				}
				$latest[] = array(
					'blog_entry_id' => $row['blog_entry_id'],
					'blog_comment_id' => $row['blog_comment_id'],
					'entry_title' => $this->truncate(html_entity_decode($row['entry_title'], ENT_QUOTES, 'UTF-8'),3,40),
					'comment' => $this->truncate(html_entity_decode($row['comment'], ENT_QUOTES, 'UTF-8'),10,100),
					'username' => $row['username'],
					'parent_username' => $parent_username,
					'type' => $type,
					'date_modified' => dateISO2Display($row['date_modified'], $this->language->get('date_format_short')),
					'approved' => $row['approved']
				);
			}
				
		return $latest;
		
	}
	
	public function getCommentAuthor($blog_comment_id) {
		$query = $this->db->query("SELECT username
			FROM " . $this->db->table("blog_comment") . "
			WHERE blog_comment_id = '" . $blog_comment_id . "'");
			
		return $query->row['username'];	
	}
	
	public function toggleCommentApproval($blog_comment_id) {
		
		$query = $this->db->query("SELECT approved
				FROM " . $this->db->table("blog_comment") . "	
				WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");
		
		$approved = ($query->row['approved'] == 1 ? 0 : 1);
		
		$this->db->query("UPDATE " . $this->db->table("blog_comment") . "
				SET `approved` = '" . (int)$approved . "'
				WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");
		
		return $approved;
				 
	}
	
	public function getBlogEntryDetails($blog_entry_id) {
		
		$sql = "SELECT *
				FROM " . $this->db->table("blog_entry") . "
				WHERE blog_entry_id = '" . $blog_entry_id . "'";
				
		$query = $this->db->query($sql);
		return $query->row;
	}

	/**
	 * @return array
	 */
	public function getBlogLeafComments() {
		$query = $this->db->query(
			"SELECT t1.blog_comment_id as blog_comment_id FROM " . $this->db->table("blog_comment") . " AS t1 
			LEFT JOIN " . $this->db->table("blog_comment") . " as t2
			 ON t1.blog_comment_id = t2.parent_id WHERE t2.blog_comment_id IS NULL");
		$result = array();
		foreach ( $query->rows as $r ) {
			$result[$r['blog_comment_id']] = $r['blog_comment_id'];
		}

		return $result;
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
	
	public function getblog_config($key) {
		
		$sql = "SELECT `value` 
						FROM " . $this->db->table("blog_settings") . "
						WHERE `key` = '" . $key . "'";
		
		$query = $this->db->query($sql);
		return $query->row['value'];
	}
	
	public function truncate($input, $maxWords, $maxChars) {
		$words = preg_split('/\s+/', $input);
		$words = array_slice($words, 0, $maxWords);
		$words = array_reverse($words);
	
		$chars = 0;
		$truncated = array();
	
		while(count($words) > 0)
		{
			$fragment = trim(array_pop($words));
			$chars += strlen($fragment);
	
			if($chars > $maxChars) break;
	
			$truncated[] = $fragment;
		}
	
		$result = implode($truncated, ' ');
	
		return $result . ($input == $result ? '' : '...');
	}
}
