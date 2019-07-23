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

/**
 * @property ModelDesignBloguser $model_design_blog_user
 */
class ControllerPagesDesignBlogUser extends AController {
	public $data = array();
	public $error = array();

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_user');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE,
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_user'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: ',
				'current' => true
		));
		$this->loadLanguage('blog_manager/blog_user');
		$this->loadModel('design/blog_manager');
		
		$access = $this->model_design_blog_manager->getblog_config('blog_access');
		
		if($access == 'all') {
			$this->data['access'] = $access;
			$this->data['message'] = $this->language->get('text_users_off');
		
		}else{
			$grid_settings = array('table_id' => 'blog_grid',
					'url' => $this->html->getSecureURL('listing_grid/blog_user'),
					'editurl' => $this->html->getSecureURL('listing_grid/blog_user/update'),
					'update_field' => $this->html->getSecureURL('listing_grid/blog_user/update_field'),
					'sortname' => 'username',
					'sortorder' => 'asc',
					'columns_search' => true,
					'actions' => array(
							'edit' => array(
									'text' => $this->language->get('text_edit'),
									'href' => $this->html->getSecureURL('design/blog_user/update', '&blog_user_id=%ID%'),
							),
							'delete' => array(
									'text' => $this->language->get('button_delete'),
									'href' => $this->html->getSecureURL('design/blog_user/delete', '&blog_user_id=%ID%')
							)
					),
					'multiselect' => 'false',
			);
			
			
	
			$form = new AForm ();
			$form->setForm(array('form_name' => 'blog_grid_search'));
			
			$grid_settings['colNames'] = array(
					$this->language->get('column_name'),
					$this->language->get('column_username'),
					$this->language->get('column_role'),
					$this->language->get('column_source'),
					$this->language->get('column_comments_count'),
					$this->language->get('column_blog_access_status'),
					$this->language->get('column_approve'));
	
			$grid_settings['colModel'] = array(
					array('name' => 'name',
							'index' => 'name',
							'width' => 120,
							'align' => 'left',
							'search' => false,
							'sortable' => false,
					),
					array('name' => 'user_name',
							'index' => 'name',
							'width' => 80,
							'align' => 'left',
							'search' => false,
					),
					array('name' => 'role',
							'index' => 'role',
							'width' => 70,
							'align' => 'left',
							'search' => false
					),
					array('name' => 'source',
							'index' => 'source',
							'width' => 70,
							'align' => 'left',
							'search' => false,
							'sortable' => false,
					),
					array('name' => 'count',
							'index' => 'count',
							'width' => 70,
							'align' => 'center',
							'search' => false,
							'sortable' => false,
					),
					array('name' => 'status',
							'index' => 'status',
							'width' => 85,
							'align' => 'center',
							'search' => false,
							'sortable' => false,
					),
					array('name' => 'approve',
							'index' => 'approve',
							'width' => 75,
							'align' => 'center',
							'search' => false
					));
	
			if (isset ($this->session->data['warning'])) {
				$this->data['error_warning'] = $this->session->data['warning'];
				$this->session->data['warning'] = '';
			} else {
				$this->data ['error_warning'] = '';
			}
			if (isset ($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];
				$this->session->data['success'] = '';
			} else {
				$this->data ['success'] = '';
			}
			
			$this->loadModel('design/blog_user');
			$results = $this->model_design_blog_user->getUserRoles();
			$roles = array(0 => $this->language->get('text_select_role'));
			foreach ($results as $r) {
				$roles[$r['role_id']] = $r['role_description'];
			}
			
			$form = new AForm();
			$form->setForm(array(
					'form_name' => 'blog_grid_search',
			));
	
			$grid_search_form = array();
			$grid_search_form['id'] = 'blog_grid_search';
			$grid_search_form['form_open'] = $form->getFieldHtml(array(
					'type' => 'form',
					'name' => 'blog_grid_search',
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
			$grid_search_form['fields']['role_id'] = $form->getFieldHtml(array(
					'type' => 'selectbox',
					'name' => 'role_id',
					'options' => $roles,
					'style' => 'chosen',
			));
	
			$grid_settings['search_form'] = true;
	
			$grid = $this->dispatch('common/listing_grid', array($grid_settings));
			$this->data['listing_grid'] = $grid->dispatchGetOutput();
			$this->view->assign('search_form', $grid_search_form);
			$this->view->assign('grid_url', $this->html->getSecureURL('listing_grid/blog_category'));
			
			$this->data['insert'] = $this->html->getSecureURL('design/blog_user/insert');
		
		}
		$this->data['form_language_switch'] = $this->html->getContentLanguageSwitcher();
		
		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);

		$this->processTemplate('pages/design/blog_user_list.tpl');
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function insert() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_user');

		$this->document->setTitle($this->language->get('blog_user_name'));
		$this->data['heading_title'] = $this->language->get('blog_user_name');

		if ($this->request->is_POST() && $this->_validateForm()) {

			$this->loadModel('design/blog_user');
			$blog_user_id = $this->model_design_blog_user->addUser($this->request->post);

			$this->session->data ['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_user/update', '&blog_user_id=' . $blog_user_id));
		}
		
		$this->data['error'] = $this->error;

		$this->_getForm();

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_user');

		$this->document->setTitle($this->language->get('blog_user_name'));
		$this->data['heading_title'] = $this->language->get('blog_user_name');
		$blog_user_id = (int)$this->request->get['blog_user_id'];
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_user/insert'));

		if ($this->request->is_POST() && $this->_validateForm() && $blog_user_id) {
			$this->loadModel('design/blog_user');
			$this->model_design_blog_user->editUser($blog_user_id, $this->request->post);

			$this->session->data ['success'] = $this->language->get('text_update_success');
			$this->redirect($this->html->getSecureURL('design/blog_user/update', '&blog_user_id=' . $blog_user_id));
		}

		$this->data['error'] = $this->error;

		$this->_getForm();
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function delete() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$blog_user_id = (int)$this->request->get['blog_user_id'];
		$this->loadModel('design/blog_user');
		
		$this->model_design_blog_user->deleteUser($blog_user_id);
		$this->redirect($this->html->getSecureURL('design/blog_user'));
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		
	}

	private function _getForm() {

		$this->loadModel('design/blog_user');
		
		if (isset ($this->session->data['warning'])) {
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data ['error_warning'] = '';
		}
		
		$this->data['error'] = $this->error;

		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		
		$blog_user_id = $this->request->get['blog_user_id'];
		
		if (has_value($blog_user_id) && $this->request->is_GET()) {
			$user_info = $this->model_design_blog_user->getUser($blog_user_id);
		}

		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_user'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: '));

		if (has_value($blog_user_id)) {
			$this->document->addBreadcrumb(array(
					'href' => $this->html->getSecureURL('design/blog_user/update', '&blog_user_id=' . $blog_user_id),
					'text' => $this->language->get('update_user_title'),
					'separator' => ' :: ',
					'current' => true
			));
		} else {
			$this->document->addBreadcrumb(array(
					'href' => $this->html->getSecureURL('design/blog_user/insert'),
					'text' => $this->language->get('insert_user_title'),
					'separator' => ' :: ',
					'current' => true
			));
		}
		
		$tz_list = array(0 => $this->language->get('text_select_users_time_zone'));
		$time_zone_list = $this->model_design_blog_user->tz_list();
		foreach ($time_zone_list as $list) {
			$tz_list[$list['zone']] =  $list['GMT_diff'] . ' - ' . $list['zone'];
		}
		
		$results = $this->model_design_blog_user->getUserRoles();
		$roles = array(0 => $this->language->get('text_select_role'));
		if (!isset($this->request->get['blog_user_id'])) {	
			$ids = array(1,5);
		}else{
			$ids = array(1,2,3,5);
		}
		if ($this->model_design_blog_user->getblog_config('login_data') == 'customer' && isset($this->request->get['blog_user_id'])) {
			array_push($ids,4);
		}
		natcasesort($ids);
		foreach ($results as $r) {
			if(in_array($r['role_id'],$ids)) {
				$roles[$r['role_id']] = $r['role_description'];
			}
		}
		
		$name_options = array(
			0 => $this->language->get('text_use_username'),
			1 => $this->language->get('text_use_fullname')
		);
		
		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_user');
		
		$approve_comments = $this->model_design_blog_user->getblog_config('approve_comments');
		
		$allowedFields = array(
			'status',
			'role_id', 
			'firstname',
			'lastname',
			'email',
			'site_url', 
			'approve',
			'username',
			'name_option',
			'admin_comment',
			'user_approve_comments',
			'user_require_approval',
			'source',
			'users_tz',
			'customer_id'
		);
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($user_info)) {
				$this->data[$field] = $user_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}
		
		if($user_info['source'] == 'customer') {
			$this->data['source'] == $user_info['source'];	
		}

		if (!isset($this->request->get['blog_user_id'])) {	
			$this->data ['action'] = $this->html->getSecureURL('design/blog_user/insert');
			$this->data ['form_title'] = $this->language->get('insert_user_title');
			$this->data ['update'] = '';
			$form = new AForm ('ST');
		} else {
			$this->data ['action'] = $this->html->getSecureURL('design/blog_user/update', '&blog_user_id=' . $this->request->get ['blog_user_id']);
			$this->data ['form_title'] = $this->language->get('update_user_title') . ' ' . $this->data['name'];
			$this->data ['update'] = $this->html->getSecureURL('listing_grid/blog_user/update_field', '&id=' . $this->request->get ['blog_user_id']);
			$form = new AForm ('HS');
		} 

		$form->setForm(array(
				'form_name' => 'bloguserFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'bloguserFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'bloguserFrm',
				'attr' => 'data-confirm-exit="true" class="aform form-horizontal"',
				'action' => $this->data['action'],
		));
		$this->data['form']['submit'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_save'),
				'style' => 'button1',
		));
		$this->data['form']['cancel'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'button2',
		));
		$this->data['form']['fields']['status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'status',
				'value' => $this->data['status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['approve'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'approve',
				'value' => $this->data['approve'],
				'style' => 'btn_switch',
		));
		if($approve_comments) {
			$this->data['form']['fields']['user_approve_comments'] = $form->getFieldHtml(array(
					'type' => 'checkbox',
					'name' => 'user_approve_comments',
					'value' => $this->data['user_approve_comments'],
					'style' => 'btn_switch',
			));
		}else{
			$this->data['form']['fields']['user_require_approval'] = $form->getFieldHtml(array(
					'type' => 'checkbox',
					'name' => 'user_require_approval',
					'value' => $this->data['user_require_approval'],
					'style' => 'btn_switch',
			));
		}

		$this->data['form']['fields']['firstname'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'firstname',
			'value' => $this->data['firstname'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['lastname'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'lastname',
			'value' => $this->data['lastname'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['role_id'] = $form->getFieldHtml(array(
				'type' => 'selectbox',
				'name' => 'role_id',
				'value' => $this->data['role_id'],
				'options' => $roles,
				'required' => true,
				'style' => 'chosen, medium-field',
		));
		$this->data['form']['fields']['username'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'username',
			'value' => $this->data['username'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['password'] = $form->getFieldHtml(array(
			'type' => 'passwordset',
			'name' => 'password',
			'value' => $this->data['password'],
			'required' => true,
			'style' => 'password, medium-field',
		));
		$this->data['form']['fields']['email'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'email',
			'value' => $this->data['email'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['name_option'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'name_option',
			'value' => $this->data['name_option'],
            'options' => $name_options,
            'style' => 'medium-field',
		));
		$this->data['form']['fields']['site_url'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'site_url',
				'value' => $this->data['site_url'],
				'style' => 'large-field',
				'placeholder' => $this->language->get('text_http'), 
		));
		$this->data['form']['fields']['users_tz'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'users_tz',
			'options' => $tz_list,
			'value'   => $this->data['users_tz'],
			'style' => 'chosen medium-field',
		));
		$this->data['form']['fields']['admin_comment'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'admin_comment',
			'value' => $this->data['admin_comment'],
			'style' => 'large-field',
		));
		
		$this->data['form']['fields']['source'] = $form->getFieldHtml(array(
				'type' => 'hidden',
				'name' => 'source',
				'value' => ($this->data['source'] ? $this->data['source'] : 'self'),
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['customer_id'] = $form->getFieldHtml(array(
				'type' => 'hidden',
				'name' => 'customer_id',
				'value' => ($this->data['customer_id'] ? $this->data['customer_id'] : ''),
				'style' => 'btn_switch',
		));

		$this->data['help'] = array(
				'link' => $this->html->getSecureURL('help/blog_help', '&page=user'),
				'text' => $this->language->get('button_help', 'blog_manager/blog_manager')
			);
			
		$this->data['blog_user_id'] = $blog_user_id;
		
		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
	

		$this->processTemplate('pages/design/blog_user_form.tpl');
	}

	private function _validateForm() {
		
		if (!$this->user->canModify('design/blog_user')) {
			$this->session->data['warning'] = $this->error ['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['source'] == 'customer') {

			if (mb_strlen($this->request->post['username']) < 8 || mb_strlen($this->request->post['username']) > 20) {
				$this->error['username'] = $this->language->get('error_username');
			}
	
			if (mb_strlen($this->request->post['firstname']) < 8 || mb_strlen($this->request->post['firstname']) > 32) {
				$this->error['firstname'] = $this->language->get('error_name');
			}
			if (mb_strlen($this->request->post['lastname']) < 8 || mb_strlen($this->request->post['lastname']) > 32) {
				$this->error['lastname'] = $this->language->get('error_name');
			}
	
			if (mb_strlen($this->request->post['email']) > 96 || !preg_match(EMAIL_REGEX_PATTERN, $this->request->post['email'])) {
				$this->error['email'] = $this->language->get('error_email');
			}
	
			if (($this->request->post['password']) && (!isset($this->request->get['blog_user_id']))) {
				if (mb_strlen($this->request->post['password']) < 4 ) {
					$this->error['password'] = $this->language->get('error_password');
				}
		
				if ($this->request->post['password'] != $this->request->post['password_confirm']) {
					$this->error['password_confirm'] = $this->language->get('error_confirm');
				}
			}
		}
		
		if($this->request->post['role_id'] == 0) {
			$this->error['role_id'] = $this->language->get('error_role_id');	
		}

		$this->extensions->hk_ValidateData( $this );
	
    	if (!$this->error) {
      		return TRUE;
    	} else {
			$this->error ['warning'] = $this->language->get('error_required_data');
			$this->session->data['warning'] = $this->language->get('error_required_data');
      		return FALSE;
    	}

	}

}