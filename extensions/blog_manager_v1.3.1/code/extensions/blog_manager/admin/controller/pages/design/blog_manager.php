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
define('BLOG_ADMIN', DIR_EXTENSIONS . 'blog_manager/admin');

/**
 * @property ModelDesignBlogManager $model_design_blog_manager
 */
class ControllerPagesDesignBlogManager extends AController {
	public $data = array();
	public $error = array();
	
	

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_manager');

		$this->document->setTitle($this->language->get('blog_manager_name'));
		$this->data['heading_title'] = $this->language->get('blog_manager_name');

		if ($this->request->is_POST() && $this->_validateDetailsForm()) {
			$this->loadModel('design/blog_manager');
			$this->model_design_blog_manager->editBlog($this->request->post);

			$this->session->data ['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_manager'));
		}

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
		
		$blog_info = $this->model_design_blog_manager->getBlog();
		
        if (!$blog_info['blog_url']) { 
			$this->data['proc'] = 'new';
		}else {
			$this->data['proc'] = 'exist';
		}
		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_manager'),
				'text' => $this->language->get('blog_manager_name'),
				'separator' => ' :: '));

		if ($this->data['proc'] == 'exist') {
			$this->document->addBreadcrumb(array(
					'href' => $this->html->getSecureURL('design/blog_manager'),
					'text' => $this->language->get('update_title'),
					'separator' => ' :: ',
					'current' => true
			));
		} else {
			$this->document->addBreadcrumb(array(
					'href' => $this->html->getSecureURL('design/blog_manager'),
					'text' => $this->language->get('insert_title'),
					'separator' => ' :: ',
					'current' => true
			));
		}
		
		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_manager');
		
		$domains = array(
			0 => $this->language->get('text_use_store_url'),
			1 => $this->language->get('text_use_new_url')
		);
		
		$allowedFields = array(
			'title',
			'description',
			'owner',
			'owner_email',
			'use_store_url',
			'blog_url',
			'blog_store_id',
			'blog_ssl', 
			'blog_ssl_url', 
			'message_top',
			'message_bottom',
			'disclaimer',
			'meta_description',
			'meta_keywords', 
		);
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($blog_info)) {
				$this->data[$field] = $blog_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}
		
		if(!isset($this->data['use_store_url'])) {
			$this->data['use_store_url'] = 0;
		}
		
		if($this->data['proc'] == 'new') {
			$store_url = $this->config->get('config_url').'blog';
			$store_ssl = $this->config->get('config_ssl');
			if($store_ssl) {
				$store_ssl_url = $this->config->get('config_ssl_url').'blog';
			}
		}else{
			$store_url = $this->data['blog_url'];
			$store_ssl = $this->data['blog_ssl'];
			$store_ssl_url = $this->data['blog_ssl_url'];
		}

		$this->data ['action'] = $this->html->getSecureURL('design/blog_manager');

		if ($this->data['proc'] == 'new') {	
			$this->data ['form_title'] = $this->language->get('text_create');
		} else {
			$this->data ['form_title'] = $this->language->get('text_edit') . ' ' . $this->data['name'];
			$this->data ['update'] = '';
		} 
		
		$form = new AForm ('ST');
		$form->setForm(array(
				'form_name' => 'blogManagerFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'blogSettingsFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogManagerFrm',
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
		$this->data['form']['fields']['title'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'title',
			'value' => $this->data['title'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['description'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'description',
			'value' => $this->data['description'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['owner'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'owner',
			'value' => $this->data['owner'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['owner_email'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'owner_email',
			'value' => $this->data['owner_email'],
			'required' => true,
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['use_store_url'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'use_store_url',
			'value' => $this->data['use_store_url'],
			'options' => $domains,
			'style' => 'medium-field',
			
		));
		
		$this->data['form']['fields']['blog_url'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_url',
			'value' => $store_url,
			'style' => 'medium-field',
			'attr' => $this->data['use_store_url'] == 0 ? 'readonly="readonly"' : '',
			'required' => true,
		));

		$this->data['form']['fields']['blog_ssl'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'blog_ssl',
			'value' => $store_ssl,
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['blog_ssl_url'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_ssl_url',
			'value' => $store_ssl_url,
			'style' => 'medium-field',
			'attr' => $this->data['use_store_url'] == 0 ? 'readonly="readonly"' : '',
			'required' => true,
		));
		
		$this->data['form']['fields']['message_top'] = $form->getFieldHtml(array(
			'type' => 'texteditor',
			'name' => 'message_top',
			'value' => $this->data['message_top'],
			'style' => 'xl-field',
		));
		$this->data['form']['fields']['message_bottom'] = $form->getFieldHtml(array(
			'type' => 'texteditor',
			'name' => 'message_bottom',
			'value' => $this->data['message_bottom'],
			'style' => 'xl-field',
		));
		$this->data['form']['fields']['meta_description'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'meta_description',
			'value' => $this->data['meta_description'],
			'style' => 'large-field',
		));
		$this->data['form']['fields']['meta_keywords'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'meta_keywords',
			'value' => $this->data['meta_keywords'],
			'style' => 'large-field',
		));
		$this->data['form']['fields']['disclaimer'] = $form->getFieldHtml(array(
			'type' => 'texteditor',
			'name' => 'disclaimer',
			'value' => $this->data['disclaimer'],
			'style' => 'xl-field',
		)); 
		$this->data['form']['fields']['blog_store_id'] = $form->getFieldHtml(array(
			'type'    => 'hidden',
			'name'    => 'blog_store_id',
			'value'   => $this->data['blog_store_id'],
		)); 
		
		$this->data['extension_info']['help']['file'] = array(
			'link' => $this->html->getSecureURL('extension/extension/help', '&extension=blog_manager'),
			'text' => $this->language->get('button_howto'));
		
		 
		$this->addChild('responses/common/resource_library/get_resources_html', 'resources_html', 'responses/common/resource_library_scripts.tpl');
		$resources_scripts = $this->dispatch(
				'responses/common/resource_library/get_resources_scripts',
				array(
						'object_name' => 'blog',
						'object_id' => $this->data['blog_entry_id'],
						'types' => array('image'),
				)
		);
		
		$this->view->assign('current_url', $this->html->currentURL());
		$this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());
		$this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&object_name=blog&type=image'));

		$this->data['active'] = 'details';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_manager_tabs', array($this->data));
		$this->data['blog_manager_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		

		$this->processTemplate('pages/design/blog_manager_form.tpl');
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function blocks() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_manager');

		$this->document->setTitle($this->language->get('blog_manager_name'));
		$this->data['heading_title'] = $this->language->get('blog_manager_name');

		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->loadModel('design/blog_manager');
			$this->model_design_blog_manager->editBlog($this->request->post);

			$this->session->data ['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_manager/blocks'));
		}

		$this->data['error'] = $this->error;

		if (isset ($this->session->data['warning'])) {
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data ['error_warning'] = '';
		}

		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		
		$this->loadModel('design/blog_manager');
		$blog_info = $this->model_design_blog_manager->getBlog();
		$this->data['blog_info'] = $blog_info;
		if (!$blog_info['blog_url']) { 
			$this->data['proc'] = 'new';
			$this->data['details_link'] = $this->html->getSecureURL('design/blog_manager');
		}else {
			$this->data['proc'] = 'exist';
		}
		
		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_manager'),
				'text' => $this->language->get('blog_manager_name'),
				'separator' => ' :: '));

		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_manager/blocks'),
				'text' => $this->language->get('update_title'),
				'separator' => ' :: ',
				'current' => true
		));

		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_manager');
		
		$allowedFields = array(
			'author_list_block_status', 'author_list_block_min', 'author_list_block_limit', 'author_list_block_max',
			'category_block_status', 'category_block_min', 'category_block_limit', 'category_block_max',
			'latest_block_status', 'latest_block_min', 'latest_block_limit', 'latest_block_max',
			'archive_block_status', 'archive_block_min', 'archive_block_limit', 'archive_block_max',
			'popular_block_status', 'popular_block_min', 'popular_block_limit', 'popular_block_max',
			'active_block_status', 'active_block_min', 'active_block_limit', 'active_block_max',
			'top_menu_block_status', 'store_top_menu', 'feed_top_menu', 'author_list_top_menu', 'category_top_menu', 'latest_top_menu', 
			'archive_top_menu', 'popular_top_menu', 'active_top_menu', 'login_top_menu', 'search_top_menu'
		);
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($blog_info)) {
				$this->data[$field] = $blog_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}

		$this->data ['action'] = $this->html->getSecureURL('design/blog_manager/blocks');
		$this->data ['form_title'] = $this->language->get('text_edit') . ' ' . $this->data['name'];
		$form = new AForm ('ST');

		$form->setForm(array(
				'form_name' => 'blogManagerFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'blogManagerFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogManagerFrm',
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
		$this->data['author_list_block_active'] = $this->data['author_list_block_status'];
		$this->data['form']['fields']['author_list_block']['author_list_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'author_list_block_status',
				'value' => $this->data['author_list_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['author_list_block']['author_list_block_min'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'author_list_block_min',
			'value' => $this->data['author_list_block_min'] ? $this->data['author_list_block_min'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['author_list_block']['author_list_block_limit'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'author_list_block_limit',
			'value' => $this->data['author_list_block_limit'] ? $this->data['author_list_block_limit'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['author_list_block']['author_list_block_max'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'author_list_block_max',
			'value' => $this->data['author_list_block_max'] ? $this->data['author_list_block_max'] : 20,
			'style' => 'tiny-field',
		));
		$this->data['category_block_active'] = $this->data['category_block_status'];
		$this->data['form']['fields']['category_block']['category_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'category_block_status',
				'value' => $this->data['category_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['category_block']['category_block_min'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'category_block_min',
			'value' => $this->data['category_block_min'] ? $this->data['category_block_min'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['category_block']['category_block_limit'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'category_block_limit',
			'value' => $this->data['category_block_limit'] ? $this->data['category_block_limit'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['category_block']['category_block_max'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'category_block_max',
			'value' => $this->data['category_block_max'] ? $this->data['category_block_max'] : 20,
			'style' => 'tiny-field',
		));
		$this->data['archive_block_active'] = $this->data['archive_block_status'];
		$this->data['form']['fields']['archive_block']['archive_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'archive_block_status',
				'value' => $this->data['archive_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['archive_block']['archive_block_min'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'archive_block_min',
			'value' => $this->data['archive_block_min'] ? $this->data['archive_block_min'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['archive_block']['archive_block_limit'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'archive_block_limit',
			'value' => $this->data['archive_block_limit'] ? $this->data['archive_block_limit'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['archive_block']['archive_block_max'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'archive_block_max',
			'value' => $this->data['archive_block_max'] ? $this->data['archive_block_max'] : 20,
			'style' => 'tiny-field',
		));
		$this->data['latest_block_active'] = $this->data['latest_block_status'];
		$this->data['form']['fields']['latest_block']['latest_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'latest_block_status',
				'value' => $this->data['latest_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['latest_block']['latest_block_min'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'latest_block_min',
			'value' => $this->data['latest_block_min'] ? $this->data['latest_block_min'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['latest_block']['latest_block_limit'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'latest_block_limit',
			'value' => $this->data['latest_block_limit'] ? $this->data['latest_block_limit'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['latest_block']['latest_block_max'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'latest_block_max',
			'value' => $this->data['latest_block_max'] ? $this->data['latest_block_max'] : 20,
			'style' => 'tiny-field',
		));
		$this->data['popular_block_active'] = $this->data['popular_block_status'];
		$this->data['form']['fields']['popular_block']['popular_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'popular_block_status',
				'value' => $this->data['popular_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['popular_block']['popular_block_min'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'popular_block_min',
			'value' => $this->data['popular_block_min'] ? $this->data['popular_block_min'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['popular_block']['popular_block_limit'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'popular_block_limit',
			'value' => $this->data['popular_block_limit'] ? $this->data['popular_block_limit'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['popular_block']['popular_block_max'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'popular_block_max',
			'value' => $this->data['popular_block_max'] ? $this->data['popular_block_max'] : 20,
			'style' => 'tiny-field',
		));
		$this->data['active_block_active'] = $this->data['active_block_status'];
		$this->data['form']['fields']['active_block']['active_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'active_block_status',
				'value' => $this->data['active_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['active_block']['active_block_min'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'active_block_min',
			'value' => $this->data['active_block_min'] ? $this->data['active_block_min'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['active_block']['active_block_limit'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'active_block_limit',
			'value' => $this->data['active_block_limit'] ? $this->data['active_block_limit'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['active_block']['active_block_max'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'active_block_max',
			'value' => $this->data['active_block_max'] ? $this->data['active_block_max'] : 20,
			'style' => 'tiny-field',
		));

		//top_menu
		$this->data['top_menu_status'] = $this->data['top_menu_block_status'];
		$this->data['form']['fields']['top_menu']['top_menu_block_status'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'top_menu_block_status',
				'value' => $this->data['top_menu_block_status'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['store_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'store_top_menu',
				'value' => $this->data['store_top_menu'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['author_list_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'author_list_top_menu',
				'value' => $this->data['author_list_top_menu'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['category_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'category_top_menu',
				'value' => $this->data['category_top_menu'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['archive_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'archive_top_menu',
				'value' => $this->data['archive_top_menu'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['popular_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'popular_top_menu',
				'value' => $this->data['popular_top_menu'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['active_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'active_top_menu',
				'value' => $this->data['active_top_menu'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['top_menu']['feed_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'feed_top_menu',
				'value' => $this->data['feed_top_menu'],
				'style' => 'btn_switch',
		));
		if($blog_info['blog_access'] == 'restrict') {
			$this->data['form']['fields']['top_menu']['login_top_menu'] = $form->getFieldHtml(array(
					'type' => 'checkbox',
					'name' => 'login_top_menu',
					'value' => $this->data['login_top_menu'],
					'style' => 'btn_switch',
			));
		}
		$this->data['form']['fields']['top_menu']['search_top_menu'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'search_top_menu',
				'value' => $this->data['search_top_menu'],
				'style' => 'btn_switch',
		));
		
		$this->data['help'] = array(
			'link' => $this->html->getSecureURL('help/blog_help', '&page=block'),
			'text' => $this->language->get('button_help'));
	
		$this->view->assign('current_url', $this->html->currentURL());

		$this->data['active'] = 'blocks';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_manager_tabs', array($this->data));
		$this->data['blog_manager_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		$this->processTemplate('pages/design/blog_manager_block_settings_form.tpl');
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function users() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_manager');

		$this->document->setTitle($this->language->get('blog_manager_name'));
		$this->data['heading_title'] = $this->language->get('blog_manager_name');

		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->loadModel('design/blog_manager');
			$this->model_design_blog_manager->editBlog($this->request->post);

			$this->session->data ['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_manager/users'));
		}

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
		
		$this->loadModel('design/blog_manager');
		$blog_info = $this->model_design_blog_manager->getBlog();
		if (!$blog_info['blog_url']) { 
			$this->data['proc'] = 'new';
			$this->data['details_link'] = $this->html->getSecureURL('design/blog_manager');
		}else {
			$this->data['proc'] = 'exist';
		}
		
		$groups = array();
		$customer_groups = $this->model_design_blog_manager->getCustomerGroups();
		foreach ($customer_groups as $cg) {
			$groups[$cg['customer_group_id']] = $cg['name'];
		}
		
		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_manager'),
				'text' => $this->language->get('blog_manager_name'),
				'separator' => ' :: '));

		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_manager/users'),
				'text' => $this->language->get('update_title'),
				'separator' => ' :: ',
				'current' => true
		));
		
		$access_options = array(
			'all' => $this->language->get('text_allow_all'),
			'restrict' => $this->language->get('text_restrict_access')
		);
		
		$login_options = array(
			'customer' => $this->language->get('text_use_customer'),
			'self' => $this->language->get('text_seperate_login')
		);
			
		
		
		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_manager');
		
		$allowedFields = array(
			'blog_access',
			'login_data',
			'approve_user',
			'user_email_activation',
			'notification_option',
			'notification_if_logged',
			'autofill_form',
			'customer_groups'
		);
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($blog_info)) {
				if ($field =='customer_groups') {
					 $this->data[$field] = explode(',',$blog_info[$field]);
				}else{
					$this->data[$field] = $blog_info[$field];
				}
			} else {
				$this->data[$field] = '';
			}
		}

		$this->data ['action'] = $this->html->getSecureURL('design/blog_manager/users');
		$this->data ['form_title'] = $this->language->get('text_edit') . ' ' . $this->data['name'];
		$form = new AForm ('ST');

		$form->setForm(array(
				'form_name' => 'blogManagerFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'blogSettingsFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogManagerFrm',
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
		
		$this->data['form']['fields']['select']['blog_access'] = $form->getFieldHtml(array(
				'type' => 'radio',
				'name' => 'blog_access',
				'value' => $this->data['blog_access'],
				'options' => $access_options,
			
		));
		
		$this->data['form']['fields']['restrict']['login_data'] = $form->getFieldHtml(array(
				'type' => 'radio',
				'name' => 'login_data',
				'value' => $this->data['login_data'],
				'options' => $login_options,
			
		));
		
		$this->data['form']['fields']['customer']['customer_groups'] = $form->getFieldHtml(array(
			'type' => 'checkboxgroup',
			'name' => 'customer_groups[]',
			'value' => $this->data['customer_groups'],
            'options' => $groups,
            'style' => 'chosen medium-field',
            'placeholder' => $this->language->get('text_select_customer_groups'),
		));
		$this->data['form']['fields']['new_user']['approve_user'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'approve_user',
				'value' => $this->data['approve_user'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['new_user']['user_email_activation'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'user_email_activation',
				'value' => $this->data['user_email_activation'],
				'style' => 'btn_switch',
		));
		$this->data['form']['fields']['misc']['autofill_form'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'autofill_form',
				'value' => $this->data['autofill_form'],
				'style' => 'btn_switch',
		));
		
		$this->data['help'] = array(
			'link' => $this->html->getSecureURL('help/blog_help', '&page=user_settings'),
			'text' => $this->language->get('button_help'));
		
		$this->view->assign('current_url', $this->html->currentURL());

		$this->data['active'] = 'users';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_manager_tabs', array($this->data));
		$this->data['blog_manager_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		

		$this->processTemplate('pages/design/blog_manager_user_form.tpl');
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}


	public function settings() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_manager');

		$this->document->setTitle($this->language->get('blog_manager_name'));
		$this->data['heading_title'] = $this->language->get('blog_manager_name');

		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->loadModel('design/blog_manager');
			$this->model_design_blog_manager->editBlog($this->request->post);

			$this->session->data ['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_manager/settings'));
		}

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
		
		$this->loadModel('design/blog_manager');
		$blog_info = $this->model_design_blog_manager->getBlog();
		$this->data['blog_info'] = $blog_info;
		if (!$blog_info['blog_url']) { 
			$this->data['proc'] = 'new';
			$this->data['details_link'] = $this->html->getSecureURL('design/blog_manager');
		}else {
			$this->data['proc'] = 'exist';
		}
	
		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_manager'),
				'text' => $this->language->get('blog_manager_name'),
				'separator' => ' :: '));

		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_manager/settings'),
				'text' => $this->language->get('update_title'),
				'separator' => ' :: ',
				'current' => true
		));
		
		$sort_order = array(
			'DESC' => $this->language->get('text_new_first'),
			'ASC' => $this->language->get('text_old_first'),
		);
		$show_months = array(
			'0' => $this->language->get('text_show_all'),
			'1' => $this->language->get('text_current_month'),
			'2' => $this->language->get('text_current_month_plus_one'),
			'3' => $this->language->get('text_current_month_plus_two'),
			'4' => $this->language->get('text_current_month_plus_three'),
		);
		$feed_types = array(
			'rss2' => 'RSS 2.0',
			'atom' => 'Atom'
		);
		$times = array(
			'dt_system' => $this->language->get('text_system_dt'),
			'dt_gmt' => $this->language->get('text_gmt_dt')
		);
			
		$search_types = array(
			'simp_search' =>  $this->language->get('text_search_simple'),
			'full_search' =>  $this->language->get('text_search_full'),
			'extd_search' =>  $this->language->get('text_search_extd')
		);
		
		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_manager');
		
		$blog_categories = array();
		$this->loadModel('design/blog_category');
		$results = $this->model_design_blog_category->getThisBlogCategories(0,'');
		foreach ($results as $bc) {
			$name = !$bc['status'] ? $bc['name'] . ' (' . $this->language->get('text_inactive') .')' : $bc['name'];
			$blog_categories[$bc['blog_category_id']] = $name; 
		}

		$default_found = 0;	
		foreach($blog_categories as $key=>$value) {
			if($key == $blog_info['default_blog_category'] && $value) {
				$default_found = 1;
			}

			if(!$default_found) {
				$blog_categories[1] = $this->model_design_blog_category->getDefaultCategoryName();
			}
		}
		
		$allowedFields = array(
			'omit_uncatergorized',
			'show_month',
			'show_dt',
			'default_blog_category',
			'show_related_prices',
			'entries_per_main_page',
			'entries_per_rss_feed',
			'feed_type',
			'word_count_main',
			'word_count_feed',
			'feed_show_thumb',
			'entry_display_order',
			'comment_display_order',
			'show_sub_categories',
			'show_entry_view_count', 
			'blog_entry_image_width',
			'blog_entry_image_height',
			'blog_product_image_width',
			'blog_product_image_height',
			'blog_user_image_width',
			'blog_user_image_height',
			'blog_category_image_width',
			'blog_category_image_height',
			'blog_feed_image_width',
			'blog_feed_image_height',
			'enable_dashboard',
			'dashboard_article_count',
			'dashboard_comment_count',
			'search_article_title',
			'search_article_intro',
			'search_article_content',
			'search_meta_keywords',
			'search_meta_desc',
			'search_type'
		);
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($blog_info)) {
				$this->data[$field] = $blog_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}

		$this->data ['action'] = $this->html->getSecureURL('design/blog_manager/settings');
		$this->data ['form_title'] = $this->language->get('text_edit') . ' ' . $this->data['name'];
		$form = new AForm ('ST');

		$form->setForm(array(
				'form_name' => 'blogManagerFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'blogSettingsFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogManagerFrm',
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
		//general
		$this->data['form']['fields']['general']['show_dt'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'show_dt',
			'options' => $times,
			'value'   => $this->data['show_dt'],
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['general']['show_entry_view_count'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'show_entry_view_count',
			'value' => $this->data['show_entry_view_count'],
			'style' => 'btn_switch',
		));

		$this->data['form']['fields']['general']['default_blog_category'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'default_blog_category',
			'value' => $this->data['default_blog_category'],
            'options' => $blog_categories,
            'style' => 'chosen medium-field',
            'placeholder' => $this->language->get('text_select_blog_category'),
		));
		$this->data['form']['fields']['general']['omit_uncatergorized'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'omit_uncatergorized',
			'value' => $this->data['omit_uncatergorized'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['general']['show_sub_categories'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'show_sub_categories',
			'value' => $this->data['show_sub_categories'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['general']['show_related_prices'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_related_prices',
				'value' => $this->data['show_related_prices'],
				'style' => 'btn_switch',
		));
		//Search 
		$this->data['search_fields'] = $this->language->get('text_search_fields');
		$this->data['form']['fields']['search']['search_article_title'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'search_article_title',
			'value' => $this->data['search_article_title'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['search']['search_article_intro'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'search_article_intro',
			'value' => $this->data['search_article_intro'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['search']['search_article_content'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'search_article_content',
			'value' => $this->data['search_article_content'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['search']['search_meta_keywords'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'search_meta_keywords',
			'value' => $this->data['search_meta_keywords'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['search']['search_meta_desc'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'search_meta_desc',
			'value' => $this->data['search_meta_desc'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['search']['search_type'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'search_type',
			'options' => $search_types,
			'value'   => $this->data['search_type'],
			'style' => 'medium-field',
		));	
		//dashboard
		$this->data['form']['fields']['dashboard']['enable_dashboard'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'enable_dashboard',
			'value' => $this->data['enable_dashboard'],
			'style' => 'btn_switch',
		));
		$this->data['form']['fields']['dashboard']['dashboard_article_count'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'dashboard_article_count',
			'value' => $this->data['dashboard_article_count'] ? $this->data['dashboard_article_count'] : 5,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['dashboard']['dashboard_comment_count'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'dashboard_comment_count',
			'value' => $this->data['dashboard_comment_count'] ? $this->data['dashboard_comment_count'] : 5,
			'style' => 'tiny-field',
		));

		//display
		$this->data['form']['fields']['display']['show_month'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'show_month',
			'options' => $show_months,
			'value'   => $this->data['show_month'],
			'style' => 'medium-field',
		));
		$this->data['form']['fields']['display']['entry_display_order'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'entry_display_order',
			'options' => $sort_order,
			'value'   => $this->data['entry_display_order'],
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['display']['comment_display_order'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'comment_display_order',
			'options' => $sort_order,
			'value'   => $this->data['comment_display_order'],
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['display']['entries_per_main_page'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'entries_per_main_page',
			'value' => $this->data['entries_per_main_page'] ? $this->data['entries_per_main_page'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['display']['word_count_main'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'word_count_main',
			'value' => $this->data['word_count_main'] ? $this->data['word_count_main'] : 300,
			'style' => 'tiny-field',
		));
		
		
		
		
		//datafeed
		
		$this->data['form']['fields']['datafeed']['feed_type'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'feed_type',
			'options' => $feed_types,
			'value'   => $this->data['feed_type'],
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['datafeed']['entries_per_rss_feed'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'entries_per_rss_feed',
			'value' => $this->data['entries_per_rss_feed'] ? $this->data['entries_per_rss_feed'] : 10,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['datafeed']['word_count_feed'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'word_count_feed',
			'value' => $this->data['word_count_feed'] ? $this->data['word_count_feed'] : 300,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['datafeed']['feed_show_thumb'] = $form->getFieldHtml($props[] = array(
			'type' => 'checkbox',
			'name' => 'feed_show_thumb',
			'value' => $this->data['feed_show_thumb'],
			'style' => 'btn_switch',
		));
		
		
		//media
		$this->data['form']['fields']['media']['blog_entry_image_width'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_entry_image_width',
			'value' => $this->data['blog_entry_image_width'] ? $this->data['blog_entry_image_width'] : 100,
			'required' => true,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['media']['blog_entry_image_height'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_entry_image_height',
			'value' => $this->data['blog_entry_image_height'] ? $this->data['blog_entry_image_height'] : 100,
			'required' => true,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['media']['blog_product_image_width'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_product_image_width',
			'value' => $this->data['blog_product_image_width'] ? $this->data['blog_product_image_width'] : 100,
			'required' => true,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['media']['blog_product_image_height'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_product_image_height',
			'value' => $this->data['blog_product_image_height'] ? $this->data['blog_product_image_height'] : 100,
			'required' => true,
			'style' => 'tiny-field',
		));
		//$this->data['form']['fields']['media']['blog_user_image_width'] = $form->getFieldHtml(array(
//			'type' => 'input',
//			'name' => 'blog_user_image_width',
//			'value' => $this->data['blog_user_image_width'] ? $this->data['blog_user_image_width'] : 100,
//			'required' => true,
//			'style' => 'tiny-field',
//		));
//		$this->data['form']['fields']['media']['blog_user_image_height'] = $form->getFieldHtml(array(
//			'type' => 'input',
//			'name' => 'blog_user_image_height',
//			'value' => $this->data['blog_user_image_height'] ? $this->data['blog_user_image_height'] : 100,
//			'required' => true,
//			'style' => 'tiny-field',
//		));
		$this->data['form']['fields']['media']['blog_category_image_width'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_category_image_width',
			'value' => $this->data['blog_category_image_width'] ? $this->data['blog_category_image_width'] : 57,
			'required' => true,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['media']['blog_category_image_height'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_category_image_height',
			'value' => $this->data['blog_category_image_height'] ? $this->data['blog_category_image_height'] : 57,
			'required' => true,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['media']['blog_feed_image_width'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_feed_image_width',
			'value' => $this->data['blog_feed_image_width'] ? $this->data['blog_feed_image_width'] : 100,
			'required' => true,
			'style' => 'tiny-field',
		));
		$this->data['form']['fields']['media']['blog_feed_image_height'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'blog_feed_image_height',
			'value' => $this->data['blog_feed_image_height'] ? $this->data['blog_feed_image_height'] : 100,
			'required' => true,
			'style' => 'tiny-field',
		));
		
		$this->addChild('responses/common/resource_library/get_resources_html', 'resources_html', 'responses/common/resource_library_scripts.tpl');
		$resources_scripts = $this->dispatch(
				'responses/common/resource_library/get_resources_scripts',
				array(
						'object_name' => 'blog',
						'object_id' => 0,
						'types' => array('image'),
				)
		);
		
		$this->view->assign('current_url', $this->html->currentURL());
		$this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());
		$this->view->assign('rl', $this->html->getSecureURL('common/resource_library','&action=list_library&object_name=&object_id&type=image&mode=single'));

		$this->data['active'] = 'settings';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_manager_tabs', array($this->data));
		$this->data['blog_manager_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		

		$this->processTemplate('pages/design/blog_manager_settings_form.tpl');
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		
	}
	
	public function comments() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_manager');

		$this->document->setTitle($this->language->get('blog_manager_name'));
		$this->data['heading_title'] = $this->language->get('blog_manager_name');

		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->loadModel('design/blog_manager');
			$this->model_design_blog_manager->editBlog($this->request->post);

			$this->session->data ['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_manager/comments'));
		}

		$this->data['error'] = $this->error;
		
		if (isset ($this->session->data['warning'])) {
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data ['error_warning'] = '';
		}
		
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		
		$this->loadModel('design/blog_manager');
		$blog_info = $this->model_design_blog_manager->getBlog();
		if (!$blog_info['blog_url']) { 
			$this->data['proc'] = 'new';
			$this->data['details_link'] = $this->html->getSecureURL('design/blog_manager');
		}else {
			$this->data['proc'] = 'exist';
		}

		$this->document->initBreadcrumb(array('href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE));
		$this->document->addBreadcrumb(array('href' => $this->html->getSecureURL('design/blog_manager'),
				'text' => $this->language->get('blog_manager_name'),
				'separator' => ' :: '));

		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_manager/comments'),
				'text' => $this->language->get('update_title'),
				'separator' => ' :: ',
				'current' => true
		));
		
		$sort_order = array(
			'DESC' => $this->language->get('text_ascending'),
			'ASC' => $this->language->get('text_descending'),
		);
		
		$this->data ['cancel'] = $this->html->getSecureURL('design/blog_manager');
		
		$allowedFields = array(
			'approve_comments',
			'sanitize',
			'show_comment_policy',
			'comment_policy',
			'whitelist_tags',
			'filter_bad_words',
			'bad_words',
			'email_all',
			'email_to_be_approved',
			'show_site_url',
			'notification_all', 
			'notification_on_reply'
		);
		
		
		
		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($blog_info)) {
				$this->data[$field] = $blog_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}

		$this->data ['action'] = $this->html->getSecureURL('design/blog_manager/comments');
		$this->data ['form_title'] = $this->language->get('text_edit') . ' ' . $this->data['name'];
		$form = new AForm ('ST');

		$form->setForm(array(
				'form_name' => 'blogManagerFrm',
				'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'blogSettingsFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogManagerFrm',
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

		$this->data['form']['fields']['approve_comments'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'approve_comments',
				'value' => $this->data['approve_comments'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['show_comment_policy'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_comment_policy',
				'value' => $this->data['show_comment_policy'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['comment_policy'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'comment_policy',
			'value' => $this->data['comment_policy'],
			'style' => 'xl-field',
		));
		$this->data['form']['fields']['sanitize'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'sanitize',
				'value' => $this->data['sanitize'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['whitelist_tags'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'whitelist_tags',
			'value' => $this->data['whitelist_tags'],
			'style' => 'xl-field',
		));
		$this->data['form']['fields']['filter_bad_words'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'filter_bad_words',
				'value' => $this->data['filter_bad_words'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['bad_words'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'bad_words',
			'value' => $this->data['bad_words'],
			'style' => 'xl-field',
		));
		$this->data['form']['fields']['email_all'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'email_all',
				'value' => $this->data['email_all'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['email_to_be_approved'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'email_to_be_approved',
				'value' => $this->data['email_to_be_approved'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['show_site_url'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'show_site_url',
				'value' => $this->data['show_site_url'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['notification_all'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'notification_all',
				'value' => $this->data['notification_all'],
				'style' => 'btn_switch', 
		));
		$this->data['form']['fields']['notification_on_reply'] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => 'notification_on_reply',
				'value' => $this->data['notification_on_reply'],
				'style' => 'btn_switch', 
		));
		
		
		$this->view->assign('current_url', $this->html->currentURL());

		$this->data['active'] = 'comments';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_manager_tabs', array($this->data));
		$this->data['blog_manager_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		

		$this->processTemplate('pages/design/blog_manager_form.tpl');
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	private function _validateDetailsForm() {
		if (!$this->user->canModify('design/blog_manager')) {
			$this->session->data['warning'] = $this->error ['warning'] = $this->language->get('error_permission');
		}

		if (mb_strlen($this->request->post['title']) < 3) {
			$this->error['title'] = $this->language->get('error_title');
		}
		if (mb_strlen($this->request->post['description']) < 3) {
			$this->error['description'] = $this->language->get('error_description');
		}
		
		if (mb_strlen($this->request->post['owner']) < 3) {
			$this->error['owner'] = $this->language->get('error_owner');
		}
		
		if (mb_strlen($this->request->post['owner_email']) > 60 || !preg_match(EMAIL_REGEX_PATTERN, $this->request->post['owner_email'])) {
		    $this->error['owner_email'] = $this->language->get('error_owner_email');
		}
		
		if (mb_strlen($this->request->post['blog_url']) < 3) {
			$this->error['blog_url'] = $this->language->get('error_blog_url');
		}
		if ($this->request->post['blog_ssl'] == '1' && mb_strlen($this->request->post['blog_ssl_url']) < 3) {
			$this->error['blog_ssl_url'] = $this->language->get('error_blog_ssl_url');
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
	
	private function _validateForm() {
		if (!$this->user->canModify('design/blog_manager')) {
			$this->session->data['warning'] = $this->error ['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post) {
			$required = array();

			foreach ($this->request->post as $name => $value) {
				if (in_array($name, $required) && empty($value)) {
					$this->error ['warning'] = $this->language->get('error_required_data');
					$this->session->data['warning'] = $this->language->get('error_required_data');
					break;
				}
			}
			
		}

		$this->extensions->hk_ValidateData($this);

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * update only one field
	 *
	 * @return void
	 */
	public function update_field() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('design/blog_manager');
		$this->loadModel('design/blog_manager');

		if (!$this->user->canModify('design/blog_manager')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_manager'),
					'reset_value' => true
				));
		}

		
		if (isset($this->request->get[ 'id' ])) {
			//request sent from edit form. ID in url
			foreach ($this->request->post as $key => $value) {
                $data = array( $key => $value );
				$this->model_design_blog_manager->editBlog($this->request->get[ 'id' ], $data);
			}
			return null;
		}
		
		//request sent from jGrid. ID is key of array

		foreach ($this->request->post as $field => $value ) {
			foreach ( $value as $k => $v ) {
				$this->model_design_blog_manager->editBlog($k, array( $field => $v ));
			}
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

}