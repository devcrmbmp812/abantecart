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

class ControllerPagesDesignBlogComment extends AController {
	private $error = array();
	public $data = array();

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_comment'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: ',
				'current' => true
		));

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$grid_settings = array(
				'table_id' => 'blog_comment_grid',
				'url' => $this->html->getSecureURL('listing_grid/blog_comment','&blog_entry_id='.(int)$this->request->get['blog_entry_id']),
				'editurl' => $this->html->getSecureURL('listing_grid/blog_comment/update'),
				'update_field' => $this->html->getSecureURL('listing_grid/blog_comment/update_field'),
				'sortname' => 'entry_title',
				'sortorder' => 'desc',
				'actions' => array(
						'share-square-o' => array(
								'text' => $this->language->get('text_reply'),
								'href' => $this->html->getSecureURL('design/blog_comment/reply', '&blog_comment_id=%ID%'),
						),
						'edit' => array(
								'text' => $this->language->get('text_edit'),
								'href' => $this->html->getSecureURL('design/blog_comment/update', '&blog_comment_id=%ID%'),
						),
						'delete' => array(
								'text' => $this->language->get('button_delete'),
								'href' => $this->html->getSecureURL('design/blog_comment/delete', '&blog_comment_id=%ID%')
						)
				),
				'multiselect' => 'false',
		);

		$grid_settings['colNames'] = array(
				$this->language->get('column_entry'),
				$this->language->get('column_details'),
				$this->language->get('column_comment'),
				$this->language->get('column_replies'),
				$this->language->get('column_status'),
				$this->language->get('column_approval')
				
		);
		$grid_settings['colModel'] = array(
				array('name' => 'entry',
						'index' => 'entry',
						'width' => 100,
						'align' => 'left',
						'search' => false,
				),
				array(
						'name' => 'details',
						'index' => 'details',
						'width' => 100,
						'align' => 'left',
						'search' => false,
						'sortable' => false,
				),
				array('name' => 'comment',
						'index' => 'comment',
						'width' => 450,
						'align' => 'left',
						'search' => false,
						'sortable' => false,
				),
				array('name' => 'reply',
						'index' => 'reply',
						'width' => 50,
						'align' => 'left',
						'search' => false,
						'sortable' => false,
				),
				array(
						'name' => 'status',
						'index' => 'status',
						'width' => 100,
						'align' => 'center',
						'search' => false,
				),
				array(
						'name' => 'approval',
						'index' => 'approval',
						'width' => 60,
						'align' => 'center',
						'search' => false,
						'sortable' => false,
				),
		);
		if ($this->config->get('config_show_tree_data')) {
			$grid_settings['expand_column'] = "entry";
			$grid_settings['multiaction_class'] = 'hidden';
		}
		

		$blog_entries = array();
		$this->loadModel('design/blog_entry');
		$blog_entries = array(0 => $this->language->get('text_select_entry'));
		$results = $this->model_design_blog_comment->getEntryNames();
		foreach( $results as $r ) {
			$blog_entries[ $r['blog_entry_id'] ] = $r['entry_title'] . ' (' . ($r['allow_comment'] ? $r['comments_count'] : 'Off') . ')';
		}

		$form = new AForm();
		$form->setForm(array(
				'form_name' => 'blog_comment_grid_search',
		));

		$grid_search_form = array();
		$grid_search_form['id'] = 'blog_comment_grid_search';
		$grid_search_form['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blog_comment_grid_search',
				'action' => '',
		));
		$grid_search_form['submit'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_go'),
				'style' => 'button1',
		));
		$grid_search_form['reset'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'reset',
				'text' => $this->language->get('button_reset'),
				'style' => 'button2',
		));

		$grid_search_form['fields']['blog_entry_id'] = $form->getFieldHtml(array(
				'type' => 'selectbox',
				'name' => 'blog_entry_id',
				'options' => $blog_entries,
				'value' => $this->request->get['blog_entry_id'],
				'style' => 'chosen'
		));

		$grid_settings['search_form'] = true;
		
		$this->document->addStyle(array(
			'href' => '/extensions/blog_manager/admin/view/default/stylesheet/blog_manager.css',
			'rel' => 'stylesheet'
		 ));

		$grid = $this->dispatch('common/listing_grid', array($grid_settings));
		$this->view->assign('listing_grid', $grid->dispatchGetOutput());
		$this->view->assign('search_form', $grid_search_form);
		$this->view->assign('grid_url', $this->html->getSecureURL('listing_grid/blog_comment'));
		
		$this->view->assign('help', array(
			'link' => $this->html->getSecureURL('help/blog_help', '&page=comment'),
			'text' => $this->language->get('button_help', 'blog_manager/blog_manager'))
		);

		$this->document->setTitle($this->language->get('heading_title'));
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

		$this->processTemplate('pages/design/blog_comment_list.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function reply() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));

		if($this->request->is_GET() && $this->request->get['blog_comment_id']) {
			$parent_id = $this->request->get['blog_comment_id'];
		}
		if($this->request->is_GET() && $this->request->get['blog_entry_id']) {
			$blog_entry_id = $this->request->get['blog_entry_id'];
		}
		
		$this->data['type'] = 'reply';
		
		if ($this->request->is_POST() && $this->_validateForm()) {  
			$this->loadLanguage('blog_manager/blog_comment');
			$blog_comment_id = $this->model_design_blog_comment->addBlogComment($this->request->post);
			if(isset($this->request->post['notify'])) {
				$this->notifications($blog_comment_id);
			}
			$this->session->data['success'] = $this->language->get('text_insert_success');
			$this->redirect($this->html->getSecureURL('design/blog_comment'));
		}
		$this->_getForm($blog_comment_id, $parent_id, $blog_entry_id);

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function delete() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$blog_comment_id = (int)$this->request->get['blog_comment_id'];
		$this->loadModel('design/blog_comment');
		
		$this->model_design_blog_comment->deleteBlogComment($blog_comment_id);
		$this->redirect($this->html->getSecureURL('design/blog_comment'));
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		
	}

	public function update() {       

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));

		
		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		$this->loadLanguage('blog_manager/blog_comment');
		
		$blog_comment_id = $this->request->get['blog_comment_id'];
		
		$this->data['type'] = 'edit';
		
		if ($this->request->is_POST() && $this->_validateForm()) {
			$response = $this->model_design_blog_comment->editBlogComment($blog_comment_id, $this->request->post);
			if(isset($this->request->post['notify'])) {
				$this->notifications($blog_comment_id);
			}
			$this->session->data['success'] = $this->language->get('text_update_success');
			$this->redirect($this->html->getSecureURL('design/blog_comment/update', '&blog_comment_id=' . $blog_comment_id));
		}
		$this->_getForm($blog_comment_id, $parent_id);

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	private function _getForm($blog_comment_id, $parent_id = 0, $blog_entry_id = 0) {

		if (isset ($this->session->data['warning'])) {
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data ['error_warning'] = '';
		}
		
		$this->data['error'] = $this->error;
		
		$this->loadLanguage('blog_manager/blog_comment');

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_comment'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: '
		));
		
		$this->loadModel('design/blog_user');

		$this->view->assign('cancel', $this->html->getSecureURL('design/blog_comment'));
		if ($blog_comment_id && $this->request->is_GET()) {
			$blog_comment_info = $this->model_design_blog_comment->getBlogComment($blog_comment_id);
			$this->data['allow_comment'] = $blog_comment_info['allow_comment'];
			$this->data['approve_comments'] = $blog_comment_info['approve_comments'];
	
			if($blog_comment_info['parent_id']) {
				$this->data['comment_info'] = array(
					'link' => $this->html->getSecureURL('tool/content/showComment', '&blog_comment_id=' . $blog_comment_info['parent_id']),
					'text' => $this->language->get('text_comment')
				);
			}
		}	
		
		if($this->data['type'] == 'edit') {
	
			$blog_user_info = $this->model_design_blog_user->getUser($blog_comment_info['blog_user_id']);	
			$user_full_name = $blog_user_info['firstname'] . ' ' . $blog_user_info['lastname'] . ' (' . $blog_comment_info['username'] . ')';
			$user_email_link = '<a href="mailto:'.$blog_comment_info['email'].'">' . $blog_comment_info['email'] . '</a>';
		}
				
		$fields =  array('status', 'approved', 'blog_author_id', 'comment', 'blog_entry_id', 'parent_id', 'primary_comment_id');

		foreach ($fields as $f) {
			if (isset ($this->request->post [$f])) {
				$this->data [$f] = $this->request->post [$f];
			} elseif (isset($blog_comment_info) && isset($blog_comment_info[$f])) {
				$this->data[$f] = $blog_comment_info[$f];
			} else {
				$this->data[$f] = '';
			}
		}
		
		// for modal data
		if ($parent_id) {
			$this->data['parent_id'] = $parent_id;
			$blog_comment_info = $this->model_design_blog_comment->getBlogComment($parent_id);
			$this->data['blog_entry_id'] = $blog_comment_info['blog_entry_id'];
			$this->data['primary_comment_id'] = $blog_comment_info['primary_comment_id'];
			$this->data['allow_comment'] = $blog_comment_info['allow_comment'];
			$this->data['approve_comments'] = $blog_comment_info['approve_comments'];
			
			$this->data['comment_info'] = array(
				'link' => $this->html->getSecureURL('tool/content/showComment', '&blog_comment_id=' . $this->data['parent_id']),
				'text' => $this->language->get('text_comment')
			);
		}
		if ($blog_entry_id) {
			$this->data['blog_entry_id'] = $blog_entry_id;
			$this->data['parent_id'] = 0;
			$this->data['primary_comment_id'] = 0;
			$blog = $this->model_design_blog_comment->getBlogEntryDetails($blog_entry_id);
			$this->data['allow_comment'] = $blog['allow_comment'];
			$this->data['approve_comments'] = $this->model_design_blog_comment->getblog_config('approve_comments');
		
			
		}
		
		$users = array();
		$author = $this->model_design_blog_comment->getEntryAuthor($this->data['blog_entry_id']);
		if($author) {
			$users[$author['blog_user_id']] = $author['role_description'] . ' - ' . $author['firstname'] . ' ' . $author['lastname'];
		}
		
		$users[0] = $this->language->get('text_owner') . ' - ' . $this->model_design_blog_comment->getblog_config('owner');
		
		$admins = $this->model_design_blog_user->getBlogAdmins();
		foreach ($admins as $row) {
			$users[$row['blog_user_id']] = $row['role_description'] . ' - ' . $row['firstname'] . ' ' . $row['lastname'];
			
		}
		
		if (!$this->data['allow_comment']) {
			$this->data['comment_warning_message'] = $this->language->get('comment_warning_message');
			$blog_comment_id = ($blog_comment_id ? $blog_comment_id : $parent_id);		
			$this->data['comment_on_button'] = (string)$this->html->buildElement(array(
																'type' => 'button',
																'name' => 'comments_on',
																'text' => $this->language->get('text_allow_comment'),
																'href'=> $this->html->getSecureURL('design/blog_comment/toggle_allow_comment','&blog_entry_id='.$this->data['blog_entry_id'].'&blog_comment_id=' . $blog_comment_id . '&type=' . $this->data['type']),
																'title' => $this->language->get('text_allow_comment')
			));
		}
		
		$this->loadModel('design/blog_entry');
		$description = $this->model_design_blog_entry->getEntryDescriptions($this->data['blog_entry_id']);
		$article_title = $description[$this->session->data['content_language_id']]['entry_title'];
		
		$this->data['article_info'] = array(
			'link' => $this->html->getSecureURL('tool/content/showEntry', '&blog_entry_id=' . $this->data['blog_entry_id']),
			'text' => $article_title
		);

		if (!$blog_comment_id) {
			$this->data['action'] = $this->html->getSecureURL('design/blog_comment/reply');
			$this->data['heading_title'] = $this->language->get('text_insert') . ' ' . $this->language->get('text_blog_comment');
			$this->data['update'] = '';
			$form = new AForm('ST');
		} else {
			$this->data['action'] = $this->html->getSecureURL('design/blog_comment/update', '&blog_comment_id=' . $blog_comment_id);
			$this->data['heading_title'] = $this->language->get('text_edit') . ' ' . $this->language->get('text_blog_comment');
			$this->data['update'] = $this->html->getSecureURL('listing_grid/blog_comment/update_field', '&id=' . $blog_comment_id);
			$form = new AForm('HS');
		}

		$this->document->addBreadcrumb(
				array('href' => $this->data['action'],
						'text' => $this->data['heading_title'],
						'separator' => ' :: ',
						'current' => true
				));

		$form->setForm(
				array('form_name' => 'editFrm',
						'update' => $this->data['update'],
				));

		$this->data['form']['id'] = 'editFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(
				array('type' => 'form',
						'name' => 'editFrm',
						'attr' => 'data-confirm-exit="true" class="aform form-horizontal"',
						'action' => $this->data['action'],
				));
		$this->data['form']['submit'] = $form->getFieldHtml(
				array('type' => 'button',
						'name' => 'submit',
						'text' => $this->language->get('button_save'),
						'style' => 'button1',
				));
		$this->data['form']['cancel'] = $form->getFieldHtml(
				array('type' => 'button',
						'name' => 'cancel',
						'text' => $this->language->get('button_cancel'),
						'style' => 'button2',
				));
		if($this->data['type'] == 'edit') {
			$this->data['form']['fields']['status'] = $form->getFieldHtml(
					array('type' => 'checkbox',
							'name' => 'status',
							'value' => $this->data['status'],
							'style' => 'btn_switch',
					));
		}

		if (isset($this->data['approve_comments']) && $this->data['approve_comments'] == 1) {	
			$this->data['form']['fields']['approved'] = $form->getFieldHtml(
				array('type' => 'checkbox',
						'name' => 'approved',
						'value' => $this->data['approved'],
						'style' => 'btn_switch',
				));
		}else{
			$this->data['form']['fields']['approved'] = $form->getFieldHtml(
				array('type' => 'hidden',
						'name' => 'approved',
						'value' => '1',
				));
		}
		if($this->data['type'] == 'reply') {
			$this->data['form']['fields']['blog_user_id'] = $form->getFieldHtml(array(
					'type' => 'selectbox',
					'name' => 'blog_user_id',
					'options' => $users,
					'style' => '',
			));
		}else{
			
			$this->data['form']['fields']['username'] = '<div style="margin-top: 7px;">' . $user_full_name . '</div>';
			$this->data['form']['fields']['email'] = '<div style="margin-top: 7px;">' . $user_email_link . '</div>';
		
		}
		$this->data['form']['fields']['comment'] = $form->getFieldHtml(
				array('type' => 'texteditor',
						'name' => 'comment',
						'value' => $this->data['comment'],
						'required' => true,
						'style' => 'xl-field',
				));
		$this->data['form']['fields']['notify'] = $form->getFieldHtml(
				array('type' => 'checkbox',
						'name' => 'notify',
						'style' => 'btn_switch',
						'value' => '1'
				));
		$this->data['form']['fields']['parent_id'] = $form->getFieldHtml(
				array('type' => 'hidden',
						'name' => 'parent_id',
						'value' => $this->data['parent_id']
				));
		$this->data['form']['fields']['blog_entry_id'] = $form->getFieldHtml(
				array('type' => 'hidden',
						'name' => 'blog_entry_id',
						'value' => $this->data['blog_entry_id']
				));
		$this->data['form']['fields']['primary_comment_id'] = $form->getFieldHtml(
				array('type' => 'hidden',
						'name' => 'primary_comment_id',
						'value' => $this->data['primary_comment_id']
				));
		
		if($this->data['type'] == 'edit') {		
			$this->data['form']['fields']['blog_user_id'] = $form->getFieldHtml(
					array('type' => 'hidden',
							'name' => 'blog_user_id',
							'value' => $this->data['blog_user_id'],
					));
		}
		
		$this->data['help'] = array(
			'link' => $this->html->getSecureURL('help/blog_help', '&page=comment'),
			'text' => $this->language->get('button_help', 'blog_manager/blog_manager'));
				
		$this->data['blog_comment_id'] = $blog_comment_id;
		$this->data['automatic_approval'] = $this->language->get('text_automatic approval');
		$this->data['comment_on'] = $this->language->get('text_comment_on');
		$this->data['last_edited'] = $this->language->get('text_last_edited');
		$this->data['date_added'] = $blog_comment_info['date_added'];
		$this->data['date_modified'] = $blog_comment_info['date_modified'];


		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		$this->view->assign('current_url', $this->html->currentURL());
		
		$this->processTemplate('pages/design/blog_comment_form.tpl');
	}

	private function _validateForm() {
		
		$this->loadLanguage('blog_manager/blog_comment');

		if (!$this->user->canModify('design/blog_comment')) {
			$this->error['warning'][] = $this->language->get('error_permission');
		}

    	if (mb_strlen($this->request->post['comment']) < 5) {
      		$this->error['comment'] = $this->language->get('error_comment');
    	}

		$this->extensions->hk_ValidateData($this);

		if (!$this->error) {
      		return TRUE;
    	} else {
			$this->error['warning'] = $this->language->get('error_required_data');
			$this->session->data['warning'] = $this->language->get('error_required_data');
      		return FALSE;
    	}
	}
	
	public function toggle_allow_comment() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_comment');

		if($this->request->is_GET() && $this->request->get['blog_entry_id']) {
			$blog_entry_id = $this->request->get['blog_entry_id'];
			$blog_comment_id = $this->request->get['blog_comment_id'];
			$type = $this->request->get['type'];
			$this->loadModel('design/blog_entry');
			$this->model_design_blog_entry->toggleAllowComment($blog_entry_id);
			$this->session->data['success'] = $this->language->get('text_allow_comment_on');
			if($type == 'reply') {
				$this->redirect($this->html->getSecureURL('design/blog_comment/reply', '&blog_comment_id=' . $blog_comment_id));
			}else{
				$this->redirect($this->html->getSecureURL('design/blog_comment/update', '&blog_comment_id=' . $blog_comment_id));
			}
			
		}else{
			return false;
		}
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);	
		
	}
	
	public function notifications($blog_comment_id) {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		if(!$blog_comment_id){
			return false;
		}
		
		$this->loadLanguage('blog_manager/blog_comment');
		
		$blog_title = $this->model_design_blog_comment->getblog_config('title');
		$owner_email = $this->model_design_blog_comment->getblog_config('owner_email');

		$blog_comment_info = $this->model_design_blog_comment->getBlogComment($blog_comment_id);
		$entry_title = $this->model_design_blog_comment->getEntryTitle($blog_comment_info['blog_entry_id']);
		
		$all_notices = $this->model_design_blog_comment->getAllNotifications($blog_comment_info['blog_entry_id']);
		if(count($all_notices)>0) {
			foreach($all_notices as $notices) {
				$subject = sprintf($this->language->get('text_mail_subject'), $blog_title);
				$message = $notices['user_name'].',';
				$message .= "\n\n";
				$message .= sprintf($this->language->get('text_new_comment'), $entry_title) . "\n\n";
				$message .= $this->_getCommentUrl($blog_comment_info['blog_entry_id']).'#comment-'.$blog_comment_id. "\n\n\n";
				$message .= $this->language->get('text_cancel_notification')."\n";
				$message .= $this->_getBlogURL()."\n\n";
				$message .= $blog_title."\n\n";
			
				$mail = new AMail( $this->config );
				$mail->setTo($notices['email']);
				$mail->setFrom($owner_email);
				$mail->setSender($blog_title);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				
			}
		}

		$reply_notices = $this->model_design_blog_comment->getReplyNotifications($blog_comment_info['blog_entry_id'], $blog_comment_info['primary_comment_id']);
		if(count($reply_notices)>0) {
			foreach($reply_notices as $notices) {
				$subject = sprintf($this->language->get('text_subject_new_reply'), $blog_title);
				$message = $notices['user_name'].',';
				$message .= "\n\n";
				$message .= sprintf($this->language->get('text_new_user_reply'), $entry_title) . "\n\n";
				$message .= $this->_getCommentUrl($blog_comment_info['blog_entry_id']).'#comment-'.$blog_comment_id. "\n\n\n";
				$message .= $this->language->get('text_cancel_notification')."\n";
				$message .= $this->_getBlogURL()."\n\n";
				$message .= $blog_title."\n\n";

				$mail = new AMail( $this->config );
				$mail->setTo($notices['email']);
				$mail->setFrom($owner_email);
				$mail->setSender($blog_title);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				
			}
		}
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		
		return true;
	}
	
	private function _getBlogURL() {
		
		if($this->config->get('use_store_url') == 1) {
			$this->config->set('http_blog_server', $this->config->get('blog_url'));
			if($this->config->get('config_ssl')) {
				$this->config->set('https_blog_server', $this->config->get('blog_ssl_url'));
			}else{
				$this->config->set('https_blog_server', $this->config->get('http_blog_server'));
			}
		}else{
			$this->config->set('http_blog_server', $this->config->get('config_url'));
			if($this->config->get('blog_ssl')) {
				$this->config->set('https_blog_server', $this->config->get('config_ssl_url'));
			}else{
				$this->config->set('https_blog_server', $this->config->get('http_blog_server'));
			}
		}
		
		if (isset($this->registry->get('request')->server['HTTPS'])
				&& (($this->registry->get('request')->server['HTTPS'] == 'on') || ($this->registry->get('request')->server['HTTPS'] == '1'))) {
			$this->server = $this->config->get('https_blog_server');
		} else {
			$this->server = defined($this->config->get('http_blog_server')) ? $this->config->get('http_blog_server') : 'http://' . REAL_HOST . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/' ;
		}
		
		return $this->server;
	}
	

	private function _getCommentUrl($id) {
		$url = $this->_getBlogURL();
		if ($this->config->get('enable_seo_url')) {
			$query = $this->db->query("SELECT *
				   FROM " . $this->db->table("url_aliases") . "
				   WHERE `query` = '" . 'blog_entry_id=' . (int)$id . "'
				   AND language_id='".(int)$this->config->get('storefront_language_id')."'");
			
			if($query->row['keyword']) {
				return $url . $query->row['keyword'];
			}else{
				return $url . 'index.php?rt=blog/entry&blog_entry_id='. $id;
			}
		}else{
			return $url . 'index.php?rt=blog/entry&blog_entry_id='. $id; 
		}
	}


}
