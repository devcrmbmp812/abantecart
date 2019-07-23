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
 * @property ModelDesignBlogAuthor $model_design_blog_author
 */
class ControllerPagesDesignBlogAuthor extends AController {
	public $data = array();
	public $error = array();

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_author');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->data['heading_title'] = $this->language->get('heading_title');
		
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

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE,
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_author'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: ',
				'current' => true
		));

		$grid_settings = array('table_id' => 'blog_grid',
				'url' => $this->html->getSecureURL('listing_grid/blog_author'),
				'editurl' => $this->html->getSecureURL('listing_grid/blog_author/update'),
				'update_field' => $this->html->getSecureURL('listing_grid/blog_author/update_field'),
				'sortname' => 'name',
				'sortorder' => 'asc',
				'columns_search' => false,
				'actions' => array(
						'edit' => array(
								'text' => $this->language->get('text_edit'),
								'href' => $this->html->getSecureURL('design/blog_author/update', '&blog_author_id=%ID%'),
								'children' => array_merge(array(
							                'general' => array(
										                'text' => $this->language->get('tab_general'),
										                'href' => $this->html->getSecureURL('design/blog_author/update', '&blog_author_id=%ID%'),
						                                ),
							                'layout' => array(
										                'text' => $this->language->get('tab_layout'),
										                'href' => $this->html->getSecureURL('design/blog_author/edit_layout', '&blog_author_id=%ID%'),
						                                ),
								),(array)$this->data['grid_edit_expand'])
						),
						'delete' => array(
								'text' => $this->language->get('button_delete'),
								'href' => $this->html->getSecureURL('design/blog_author/delete', '&blog_author_id=%ID%')
						)
				),
				'multiselect' => 'false',
		);

		$form = new AForm ();
		$form->setForm(array('form_name' => 'blog_grid_search'));

		$grid_settings['colNames'] = array($this->language->get('column_author_id'),
				'', //icons
				$this->language->get('column_author_name'),
				$this->language->get('column_author_role'),
				$this->language->get('column_article_entries'),
				$this->language->get('column_status'));

		$grid_settings['colModel'] = array(
				array('name' => 'blog_author_id',
						'index' => 'blog_author_id',
						'width' => 20,
						'align' => 'center',
						'sortable' => false,
						'search' => false
				),
				array('name' => 'author_icon',
						'index' => 'icon',
						'width' => 50,
						'align' => 'center',
						'sortable' => false,
						'search' => false
				),
				array('name' => 'name',
						'index' => 'name',
						'width' => 100,
						'align' => 'left',
				),
				array('name' => 'role',
						'index' => 'role',
						'width' => 100,
						'align' => 'left',
						'search' => false
				),
				array('name' => 'entries',
						'index' => 'entries',
						'width' => 50,
						'align' => 'center',
						'search' => false
				),
				array('name' => 'status',
						'index' => 'status',
						'align' => 'center',
						'width' => 100,
						'search' => false));
		
		
		$authors = array(0 => $this->language->get('text_select_author') );
        $results = $this->model_design_blog_author->getAuthorNames();
        foreach( $results as $r ) {
            $authors[ $r['blog_author_id'] ] = $r['firstname'] . ' ' . $r['lastname'];
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
		$grid_search_form['fields']['blog_author'] = $form->getFieldHtml(array(
				'type' => 'selectbox',
				'name' => 'blog_author',
				'options' => $authors,
				'value' => $this->request->get['blog_author'],
				'style' => 'chosen',
		));

		$grid_settings['search_form'] = true;
		
		$grid = $this->dispatch('common/listing_grid', array($grid_settings));
		$this->view->assign('listing_grid', $grid->dispatchGetOutput());
		$this->view->assign('search_form', $grid_search_form);
		$this->view->assign('grid_url', $this->html->getSecureURL('listing_grid/blog_author'));
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_author/insert'));
		$this->view->assign('text_add_author', $this->language->get('text_add_author'));

		$this->data['form_language_switch'] = $this->html->getContentLanguageSwitcher();

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);

		$this->processTemplate('pages/design/blog_author_list.tpl');
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function insert() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_author');

		$this->document->setTitle($this->language->get('blog_author_name'));
		$this->data['heading_title'] = $this->language->get('blog_author_name');

		if ($this->request->is_POST() && $this->_validateForm()) {

			$this->loadModel('design/blog_author');
			$blog_author_id = $this->model_design_blog_author->addAuthor($this->request->post);
			$this->session->data['author_post'] = $this->request->post;
			$this->session->data['blog_author_id'] = $blog_author_id;
			$this->extensions->hk_ProcessData($this, __FUNCTION__);

			$this->session->data ['success'] = $this->language->get('text_insert_success');
			$this->redirect($this->html->getSecureURL('design/blog_author/update', '&blog_author_id=' . $blog_author_id));
		}

		$this->_getForm();

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_author');

		$this->document->setTitle($this->language->get('blog_author_name'));+
		$this->data['heading_title'] = $this->language->get('blog_author_name');
		$blog_author_id = (int)$this->request->get['blog_author_id'];
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_author/insert'));

		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->loadModel('design/blog_author');
			$this->model_design_blog_author->editAuthor($blog_author_id, $this->request->post);
			$this->session->data['author_post'] = $this->request->post;
			$this->extensions->hk_ProcessData($this, __FUNCTION__);
			
			$this->session->data['success'] = $this->language->get('text_update_success');
			$this->redirect($this->html->getSecureURL('design/blog_author/update', '&blog_author_id=' . $blog_author_id));
		}

		$this->_getForm();
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function delete() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$blog_author_id = (int)$this->request->get['blog_author_id'];
		$this->loadModel('design/blog_author');
		$this->model_design_blog_author->deleteAuthor($blog_author_id);
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		$this->redirect($this->html->getSecureURL('design/blog_author'));
	}

	private function _getForm() {

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
		
		$blog_author_id = $this->request->get['blog_author_id'];
		$this->data['access'] = $this->model_design_blog_author->getblog_config('blog_access');

		if (has_value($blog_author_id)) {
			$author_info = $this->model_design_blog_author->getAuthor($blog_author_id);
			$this->data['author_info'] = $author_info;
		}

		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_author'),
				'text' => $this->language->get('blog_author_name'),
				'separator' => ' :: '));

		if (has_value($blog_author_id)) {
			$this->document->addBreadcrumb(array(
					'href' => $this->html->getSecureURL('design/blog_author/update', '&blog_author_id=' . $blog_author_id),
					'text' => $this->language->get('update_author_title'),
					'separator' => ' :: ',
					'current' => true
			));
		} else {
			$this->document->addBreadcrumb(array(
					'href' => $this->html->getSecureURL('design/blog_author/insert'),
					'text' => $this->language->get('insert_author_title'),
					'separator' => ' :: ',
					'current' => true
			));
		}
		
		$this->loadModel('design/blog_user');
		$results = $this->model_design_blog_author->getActiveUsers();
		$users = array(0 => $this->language->get('text_select_user'));
		foreach ($results as $r) {
			$users[$r['blog_user_id']] = $r['name'] . ' ('.$r['username'].')';
		}
		natcasesort($users);

		$results = $this->model_design_blog_user->getUserRoles();
		$roles = array(0 => $this->language->get('text_select_role'));
		$ids = array(2,3);
		foreach ($results as $r) {
			if(in_array($r['role_id'],$ids)) {
				$roles[$r['role_id']] = $r['role_description'];
			}
		}
		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_author');
		$this->data ['user_link'] = $this->html->getSecureURL('listing_grid/blog_author/user_data');
		
		$allowedFields = array(
			'status',
			'contact_info',
			'show_author_page',
			'firstname',
			'lastname',
			'email',
			'role_id',
			'site_url',
			'show_details',
			'show_details_ap',
			'blog_user_id',
			'keyword',
			'show_author_link',
			'blog_author_id'
		);
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($author_info)) {
				$this->data[$field] = $author_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}
		
		if (isset($this->request->post['blog_author_descriptions'])) {
			$this->data['blog_author_descriptions'] = $this->request->post['blog_author_descriptions'];
		} elseif (isset($author_info)) {
			$this->data['blog_author_descriptions'] = $this->model_design_blog_author->getAuthorDescriptions($blog_author_id);
		} else {
			$this->data['blog_author_descriptions'] = array();
		}
		
		if (!isset($this->request->get['blog_author_id'])) {	
			$this->data ['action'] = $this->html->getSecureURL('design/blog_author/insert');
			$this->data ['form_title'] = $this->language->get('insert_author_title');
			$this->data ['update'] = '';
			$form = new AForm ('ST');
			
			$this->data['help'] = array(
				'link' => $this->html->getSecureURL('help/blog_help', '&page=author'),
				'text' => $this->language->get('button_help', 'blog_manager/blog_manager')
			);
			
		} else {
			$this->data ['action'] = $this->html->getSecureURL('design/blog_author/update', '&blog_author_id=' . $this->request->get ['blog_author_id']);
			$this->data ['form_title'] = $this->language->get('update_author_title') . ' ' . $this->data['name'];
			$this->data ['update'] = $this->html->getSecureURL('listing_grid/blog_author/update_field', '&id=' . $this->request->get ['blog_author_id']);
			$form = new AForm ('HS');
		} 

		$form->setForm(array(
				'form_name' => 'blogAuthorFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'blogAuthorFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogAuthorFrm',
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
		if (!isset($this->request->get['blog_author_id']) && $this->data['access'] == 'restrict') {
			$this->data['form']['fields']['blog_users'] = $form->getFieldHtml(array(
					'type' => 'selectbox',
					'name' => 'blog_users',
					'options' => $users,
					'style' => 'medium-field',
			));
		}
		$this->data['form']['fields']['status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'status',
				'value' => $this->data['status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['show_author_page'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_author_page',
				'value' => $this->data['show_author_page'],
				'style' => 'btn_switch',
		));
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
				'style' => 'medium-field',
		));
		$this->data['form']['fields']['author_title'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_author_descriptions[' . $this->session->data['content_language_id'] . '][author_title]',
			'value' => $this->data['blog_author_descriptions'][$this->session->data['content_language_id']]['author_title'],
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['email'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'email',
			'value' => $this->data['email'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['site_url'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'site_url',
				'value' => $this->data['site_url'],
				'style' => 'large-field',
				'placeholder' => $this->language->get('text_http'), 
		));
		$this->data['form']['fields']['show_author_link'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_author_link',
				'value' => $this->data['show_author_link'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['contact_info'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'contact_info',
			'value' => $this->data['contact_info'],
			'style' => 'large-field',
		));
		$this->data['form']['fields']['author_description'] = $form->getFieldHtml(array(
			'type' => 'texteditor',
			'name' => 'blog_author_descriptions[' . $this->session->data['content_language_id'] . '][author_description]',
			'value' => $this->data['blog_author_descriptions'][$this->session->data['content_language_id']]['author_description'],
			'style' => 'xl-field',
		));
		$this->data['form']['fields']['meta_description'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'blog_author_descriptions[' . $this->session->data['content_language_id'] . '][meta_description]',
			'value' => $this->data['blog_author_descriptions'][$this->session->data['content_language_id']]['meta_description'],
			'style' => 'large-field',
		));
		$this->data['form']['fields']['meta_keywords'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'blog_author_descriptions[' . $this->session->data['content_language_id'] . '][meta_keywords]',
			'value' => $this->data['blog_author_descriptions'][$this->session->data['content_language_id']]['meta_keywords'],
			'style' => 'large-field',
		)); 
		
		$this->data['keyword_button'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'generate_seo_keyword',
				'text' => $this->language->get('button_generate'),
				'attr' => 'type="button"',
				'style' => 'btn btn-info'
		));
		$this->data['generate_seo_url'] = $this->html->getSecureURL('common/common/getseokeyword', '&object_key_name=blog_author_id&id=' . $blog_author_id);

		$this->data['form']['fields']['keyword'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'keyword',
				'value' => $this->data['keyword'],
				'help_url' => $this->gen_help_url('seo_keyword'),
				'attr' => ' gen-value="' . SEOEncode($this->data['username']) . '" '
		));
		
		$this->data['form']['fields']['show_details'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_details',
				'value' => $this->data['show_details'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['show_details_ap'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_details_ap',
				'value' => $this->data['show_details_ap'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['blog_user_id'] = $form->getFieldHtml(
				array('type' => 'hidden',
				'name' => 'blog_user_id',
				'value' => $this->data['blog_user_id']
		));
		$this->data['form']['fields']['blog_author_id'] = $form->getFieldHtml(
				array('type' => 'hidden',
				'name' => 'blog_author_id',
				'value' => $this->data['blog_author_id']
		));
		
		$this->data['blog_author_id'] = $blog_author_id;
		$this->data['active'] = 'general';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_author_tabs', array($this->data));
		$this->data['blog_author_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);
	
		
//		$this->addChild('responses/common/resource_library/get_resources_html', 'resources_html', 'responses/common/resource_library_scripts.tpl');
//		$resources_scripts = $this->dispatch(
//				'responses/common/resource_library/get_resources_scripts',
//				array(
//						'object_name' => 'blog_author',
//						'object_id' => $this->data['blog_author_id'],
//						'types' => array('image'),
//				)
//		);
//		
		
		//$this->view->assign('current_url', $this->html->currentURL());
		//$this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());
//		$this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&object_name=&object_id&type=image&mode=url'));

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
	

		$this->processTemplate('pages/design/blog_author_form.tpl');
	}

	private function _validateForm() {
		
		if (!$this->user->canModify('design/blog_author')) {
			$this->session->data['warning'] = $this->error ['warning'] = $this->language->get('error_permission');
		}
		
		$this->loadModel('design/blog_author');
		$access = $this->model_design_blog_author->getblog_config('blog_access');
		
		if (mb_strlen($this->request->post['firstname']) < 1 || mb_strlen($this->request->post['firstname']) > 32) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}
		if (mb_strlen($this->request->post['lastname']) < 1 || mb_strlen($this->request->post['lastname']) > 32) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}
		
		if (mb_strlen($this->request->post['email']) > 60 || !preg_match(EMAIL_REGEX_PATTERN, $this->request->post['email'])) {
		    $this->error['email'] = $this->language->get('error_email');
		}
		
		if($this->request->post['role_id'] == 0) {
			$this->error['role_id'] = $this->language->get('error_role_id');	
		}

		$this->extensions->hk_ValidateData( $this );
	
    	if (!$this->error) {
      		return TRUE;
    	} else {
			$this->error['warning'] = $this->language->get('error_required_data');
			$this->session->data['warning'] = $this->language->get('error_required_data');
      		return FALSE;
    	}

	}
	
	public function edit_layout() {
		$page_controller = 'pages/blog/author';
		$page_key_param = 'blog_author_id';
		
		$blog_author_id = (int)$this->request->get['blog_author_id'];
		$this->data['blog_author_id'] = $blog_author_id;

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('design/layout');
		$this->loadLanguage('blog_manager/blog_author');
		$this->document->setTitle($this->language->get('update_layout'));

		$page_url = $this->html->getSecureURL('design/blog_author/edit_layout');

	    // Alert messages
	    if (isset($this->session->data['warning'])) {
	      $this->data['error_warning'] = $this->session->data['warning'];
	      unset($this->session->data['warning']);
	    }
	    if (isset($this->session->data['success'])) {
	      $this->data['success'] = $this->session->data['success'];
	      unset($this->session->data['success']);
	    }
		
		if (has_value($blog_author_id) && $this->request->is_GET()) {
			$this->loadModel('design/blog_author');
			$this->data['blog_author'] = $this->model_design_blog_author->getAuthor($blog_author_id);
			$this->data['author_name'] = $this->data['blog_author']['firstname'].'_'.$this->data['blog_author']['lastname'];
		}
		
		$this->data['heading_title'] = $this->language->get('text_edit') . ' ' . $this->language->get('tab_layout') . ' -  Blog Author: ' . $this->data['blog_author']['firstname'].' '.$this->data['blog_author']['lastname'];


		$this->document->resetBreadcrumbs();
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_author'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: '
		));

		$this->document->addBreadcrumb(array(
				'href' => $page_url,
				'text' => $this->data['heading_title'],
				'current' =>  true
		));

		$this->data['active'] = 'layout';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_author_tabs', array($this->data));
		$this->data['blog_author_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$layout = new ALayoutManager();
		//get existing page layout or generic
		$page_layout = $layout->getPageLayoutIDs($page_controller, $page_key_param, $blog_author_id);
		$page_id = $page_layout['page_id'];
		$layout_id = $page_layout['layout_id'];
		if (isset($this->request->get['tmpl_id'])) {
			$tmpl_id = $this->request->get['tmpl_id'];
		} else {
			$tmpl_id = $this->config->get('config_storefront_template');
		}			
	    $params = array(
	      'blog_author_id' => $blog_author_id,
	      'page_id' => $page_id,
	      'layout_id' => $layout_id,
	      'tmpl_id' => $tmpl_id,
	    );	
	    $url = '&'.$this->html->buildURI($params);

		// get templates
		$this->data['templates'] = array();
		$directories = glob(DIR_STOREFRONT . 'view/*', GLOB_ONLYDIR);
		foreach ($directories as $directory) {
		  $this->data['templates'][] = basename($directory);
		}
		$enabled_templates = $this->extensions->getExtensionsList(array(
		  'filter' => 'template',
		  'status' => 1,
		));
		foreach ($enabled_templates->rows as $template) {
		  $this->data['templates'][] = $template['key'];
		}

		$action = $this->html->getSecureURL('design/blog_author/save_layout');
	    // Layout form data
	    $form = new AForm('HT');
	    $form->setForm(array(
	      'form_name' => 'layout_form',
	    ));
	
	    $this->data['form_begin'] = $form->getFieldHtml(array(
	      'type' => 'form',
	      'name' => 'layout_form',
	      'attr' => 'data-confirm-exit="true"',
	      'action' => $action
	    ));
	
	    $this->data['hidden_fields'] = '';
	    foreach ($params as $name => $value) {
	      $this->data[$name] = $value;
	      $this->data['hidden_fields'] .= $form->getFieldHtml(array(
	        'type' => 'hidden',
	        'name' => $name,
	        'value' => $value
	      ));
	    }
		
	    $this->data['page_url'] = $page_url;
	    $this->data['current_url'] = $this->html->getSecureURL('design/blog_author/edit_layout', $url);
	
		// insert external form of layout
		$layout = new ALayoutManager($tmpl_id, $page_id, $layout_id);

	    $layoutform = $this->dispatch('common/page_layout', array($layout));
	    $this->data['layoutform'] = $layoutform->dispatchGetOutput();
		
		//build pages and available layouts for clonning
		$this->data['pages'] = $layout->getAllPages();
		$av_layouts = array( "0" => $this->language->get('text_select_copy_layout'));
		foreach($this->data['pages'] as $page){
			if ( $page['layout_id'] != $layout_id ) {
				$av_layouts[$page['layout_id']] = $page['layout_name'];
			}
		}

		$form = new AForm('HT');
		$form->setForm(array(
		    'form_name' => 'cp_layout_frm',
	    ));
	    
		$this->data['cp_layout_select'] = $form->getFieldHtml(array('type' => 'selectbox',
													'name' => 'layout_change',
													'value' => '',
													'options' => $av_layouts ));

		$this->data['cp_layout_frm'] = $form->getFieldHtml(array('type' => 'form',
		                                        'name' => 'cp_layout_frm',
		                                        'attr' => 'class="aform form-inline"',
			                                    'action' => $action));

		$this->view->assign('heading_title', $this->language->get('heading_title'));

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/design/blog_author_layout.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function save_layout() {
		$page_controller = 'pages/blog/author';
		$page_key_param = 'blog_author_id';
		$blog_author_id = $this->request->post['blog_author_id'];
		
		$this->loadLanguage('blog_manager/blog_author');

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		if ($this->request->is_POST()) {

			// need to know unique page existing
			$post_data = $this->request->post;
			$tmpl_id = $post_data['tmpl_id'];
			$layout = new ALayoutManager();
			
			$pages = $layout->getPages($page_controller, $page_key_param, $blog_author_id);
			if (count($pages)) {
				$page_id = $pages[0]['page_id'];
				$layout_id = $pages[0]['layout_id'];
			} else {
				// create new page record
				$page_info = array('controller' => $page_controller,
						'key_param' => $page_key_param,
						'key_value' => $blog_author_id);

				$default_language_id = $this->language->getDefaultLanguageID();
				$this->loadModel('design/blog_author');
				$author_info = $this->model_design_blog_author->getAuthor($blog_author_id, $default_language_id);
				
				$author_info['name'] = 'Blog Author: ' . $author_info['firstname'].'_'.$author_info['lastname'];
				$page_info['page_descriptions'][$default_language_id] = $author_info;

				$page_id = $layout->savePage($page_info);
				$layout_id = '';
				// need to generate layout name
				$post_data['layout_name'] = 'Blog Author: ' . $author_info['firstname'].'_'.$author_info['lastname'];
			}

			//create new instance with specific template/page/layout data
			$layout = new ALayoutManager($tmpl_id, $page_id, $layout_id);
			if (has_value($post_data['layout_change'])) {	
				//update layout request. Clone source layout
				$layout->clonePageLayout($post_data['layout_change'], $layout_id, $post_data['layout_name']);
				$this->session->data[ 'success' ] = $this->language->get('text_success_layout');
			} else {
				//save new layout
	      		$layout_data = $layout->prepareInput($post_data);
	      		if ($layout_data) {
	      			$layout->savePageLayout($layout_data);
	      			$this->session->data[ 'success' ] = $this->language->get('text_success_layout');
	      		} 
			}

			$this->redirect($this->html->getSecureURL('design/blog_author/edit_layout', '&blog_author_id=' . $blog_author_id));
		}
		$this->redirect($this->html->getSecureURL('design/blog_author/'));
	}

}