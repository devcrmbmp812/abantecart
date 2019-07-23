<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE') || !IS_ADMIN) {
	header('Location: static_pages/');
}

class ControllerResponsesToolContent extends AController {
	private $error = array();
	public $data = array();

	public function showEntry() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$blog_entry_id = $this->request->get['blog_entry_id'];

		$this->loadModel('design/blog_entry');
		$description = $this->model_design_blog_entry->getEntryDescriptions($blog_entry_id);
		$article_title = $description[$this->session->data['content_language_id']]['entry_title'];
		$article_content = $description[$this->session->data['content_language_id']]['content'];
		
		//future: get resource_image
		$this->data['content'] = array();
		$this->data['content'] = $article_content; 
		
		$this->data['content'] = html_entity_decode($this->data['content']);
		$this->data['content'] = $this->html->convertLinks($this->data['content']);
		$this->data['title'] = $article_title;
		
		
		$this->view->batchAssign($this->data);
		$this->response->setOutput($this->view->fetch('responses/tool/content.tpl'));

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function showComment() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_comment');
		$blog_comment_id = $this->request->get['blog_comment_id'];
		$this->loadModel('design/blog_comment');
		$result = $this->model_design_blog_comment->getBlogComment($blog_comment_id);
		
		$details = '<span class="detail_list_head">'. $this->language->get('text_comment_by') . '</span>';
		$details .= ' <span class="detail_list_name">'. $result['author'] . '</span>';
		
		$comment = '<span class="comment_list_head">' . $this->language->get('text_comment_on') . '</span> <span class="comment_list_date">' . $result['date_added'] . '</span>';
		if ($result['date_added'] != $result['date_modified']) {
			$comment .= ' - <span class="comment_list_edited">' . $this->language->get('text_last_edited') . ' ' . $result['date_modified'] . '</span>';
		}
		$comment .= '<br /><span class="comment_list_comment">' . $result['comment'] . '</span>';
		
		$this->data['content'] = $details . '<br /><br />' . $comment;
		$this->data['title'] = $this->language->get('text_comment');
		
		$this->data['content'] = html_entity_decode($this->data['content']);
		$this->data['content'] = $this->html->convertLinks($this->data['content']);
		
		$this->view->batchAssign($this->data);
		$this->response->setOutput($this->view->fetch('responses/tool/content.tpl'));

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function stores() {
	
		$store_id = $this->request->get['store_id'];
		$field_array = array('config_url','config_ssl','config_ssl_url');
		
		$result = $this->db->query("SELECT `key`, `value`
			FROM  " . $this->db->table("settings") . "
			WHERE store_id = '" . $store_id . "'
			AND `group` = 'details'");
		
		$values = array();
		foreach($result->rows as $row) {
			if(in_array($row['key'], $field_array)) {
				$values[$row['key']] = ($row['value']);
			}
		}
		
		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($values));
		
	}
	
	public function getArticles() {
		
		$start = $this->request->get['start'];
		$limit = $this->request->get['limit'];
		
		$this->loadModel('design/blog_entry');
		$latest_articles = $this->model_design_blog_entry->getLatestArticles($start, $limit);
		
		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($latest_articles));
		
	}
	
	public function getComments() {
		
		$start = $this->request->get['start'];
		$limit = $this->request->get['limit'];
		
		$this->loadModel('design/blog_comment');
		$latest_comments = $this->model_design_blog_comment->getLatestComments($start, $limit);
		
		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($latest_comments));
		
	}
	
	public function toggleApproval() {
		
		$id = $this->request->get['id'];
		$this->loadModel('design/blog_comment');
		
		$approval =  $this->model_design_blog_comment->toggleCommentApproval($id);	
		
		return $this->response->setOutput($approval);
	}
}
?>