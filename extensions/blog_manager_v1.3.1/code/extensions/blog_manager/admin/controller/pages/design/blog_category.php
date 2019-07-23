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

class ControllerPagesDesignBlogCategory extends AController {
	private $error = array();
	public $data = array();

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_category'),
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
				'table_id' => 'blog_category_grid',
				'url' => $this->html->getSecureURL('listing_grid/blog_category'),
				'editurl' => $this->html->getSecureURL('listing_grid/blog_category/update'),
				'update_field' => $this->html->getSecureURL('listing_grid/blog_category/update_field'),
				'sortname' => 'sort_order',
				'sortorder' => 'asc',
				'actions' => array(
						'edit' => array(
								'text' => $this->language->get('text_edit'),
								'href' => $this->html->getSecureURL('design/blog_category/update', '&blog_category_id=%ID%'),
								'children' => array_merge(array(
							                'general' => array(
										                'text' => $this->language->get('tab_general'),
										                'href' => $this->html->getSecureURL('design/blog_category/update', '&blog_category_id=%ID%'),
						                                ),
							                'layout' => array(
										                'text' => $this->language->get('tab_layout'),
										                'href' => $this->html->getSecureURL('design/blog_category/edit_layout', '&blog_category_id=%ID%'),
						                                ),
								),(array)$this->data['grid_edit_expand'])
						),
						'delete' => array(
								'text' => $this->language->get('button_delete'),
								'href' => $this->html->getSecureURL('design/blog_category/delete', '&blog_category_id=%ID%')
						)
				),
				'multiselect' => 'false',
		);

		$grid_settings['colNames'] = array(
				$this->language->get('column_category_id'),
				'',
				$this->language->get('column_name'),
				$this->language->get('column_sort_order'),
				$this->language->get('column_status'),
				$this->language->get('column_entries'),
				$this->language->get('column_subcategories'),
		);
		$grid_settings['colModel'] = array(
				array('name' => 'blog_category_id',
						'index' => 'blog_category_id',
						'width' => 20,
						'align' => 'center',
						'search' => false,
				),
				array(
						'name' => 'icon',
						'index' => 'icon',
						'align' => 'center',
						'width' => 50,
						'sortable' => false,
						'search' => false,
				),
				array(
						'name' => 'name',
						'index' => 'name',
						'width' => 250,
						'align' => 'left',
				),
				array(
						'name' => 'sort_order',
						'index' => 'sort_order',
						'width' => 150,
						'align' => 'center',
						'search' => false,
				),
				array(
						'name' => 'status',
						'index' => 'status',
						'width' => 100,
						'align' => 'center',
						'search' => false,
				),
				array(
						'name' => 'entries',
						'index' => 'entries',
						'width' => 100,
						'align' => 'center',
						'search' => false,
						'sortable' => false,
				),
				array(
						'name' => 'subcategories',
						'index' => 'subcategories',
						'width' => 140,
						'align' => 'center',
						'search' => false,
						'sortable' => false,
				),
		);
		if ($this->config->get('config_show_tree_data')) {
			$grid_settings['expand_column'] = "name";
			$grid_settings['multiaction_class'] = 'hidden';
		}
		
		$results = $this->model_design_blog_category->getThisBlogCategories(0);
		$parents = array(0 => $this->language->get('text_select_parent'));
		foreach ($results as $c) {
			$parents[$c['blog_category_id']] = $c['name'];
		}

		$form = new AForm();
		$form->setForm(array(
				'form_name' => 'blog_category_grid_search',
		));

		$grid_search_form = array();
		$grid_search_form['id'] = 'blog_category_grid_search';
		$grid_search_form['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blog_category_grid_search',
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
		$grid_search_form['fields']['parent_id'] = $form->getFieldHtml(array(
				'type' => 'selectbox',
				'name' => 'parent_id',
				'options' => $parents,
				'style' => 'chosen',
				'placeholder' => $this->language->get('text_select_parent')
		));

		$grid_settings['search_form'] = true;
		
		$this->view->assign('search_form', $grid_search_form);
		$grid = $this->dispatch('common/listing_grid', array($grid_settings));
		$this->view->assign('listing_grid', $grid->dispatchGetOutput());
		$this->view->assign('grid_url', $this->html->getSecureURL('listing_grid/blog_category'));

		$this->document->setTitle($this->language->get('heading_title'));
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_category/insert'));
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

		$this->processTemplate('pages/design/blog_category_list.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function insert() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		
		$this->loadLanguage('blog_manager/blog_category');
		if ($this->request->is_POST() && $this->_validateForm()) {

			$languages = $this->language->getAvailableLanguages();
			foreach ($languages as $l) {
				if ($l['language_id'] == $this->session->data['content_language_id']) continue;
				$this->request->post['blog_category_description'][$l['language_id']] = $this->request->post['blog_category_description'][$this->session->data['content_language_id']];
			}

			$blog_category_id = $this->model_design_blog_category->addBlogCategory($this->request->post);
			$this->session->data['success'] = $this->language->get('text_insert_success');
			$this->redirect($this->html->getSecureURL('design/blog_category/update', '&blog_category_id=' . $blog_category_id));
		}
		$this->_getForm($blog_category_id);

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));
		//$this->view->assign('help_url', $this->gen_help_url('category_edit'));
		
		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		$this->loadLanguage('blog_manager/blog_category');
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_category/insert'));
		
		$blog_category_id = $this->request->get['blog_category_id'];
		
		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->model_design_blog_category->editBlogCategory($blog_category_id, $this->request->post);
			$this->session->data['success'] = $this->language->get('text_update_success');
			$this->redirect($this->html->getSecureURL('design/blog_category/update', '&blog_category_id=' . $blog_category_id));
		}
		$this->_getForm($blog_category_id);

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function delete() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$blog_category_id = (int)$this->request->get['blog_category_id'];
		$this->loadModel('design/blog_category');
		
		$this->model_design_blog_category->deleteBlogCategory($blog_category_id);
		$this->redirect($this->html->getSecureURL('design/blog_category'));
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		
	}

	private function _getForm($blog_category_id) {

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('error_name', $this->error['name']);
		
		$this->loadLanguage('blog_manager/blog_category');

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_category'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: '
		));

		$this->view->assign('cancel', $this->html->getSecureURL('design/blog_category'));

		if (isset($blog_category_id) && $this->request->is_GET()) {
			$blog_category_info = $this->model_design_blog_category->getBlogCategory($blog_category_id);
			$current_category_id = $blog_category_id;
		}
		
		$parent_categories = array();
		$results = $this->model_design_blog_category->getThisBlogCategories(0, $current_category_id);
		$parent_categories = array(0 => $this->language->get('text_none'));
		foreach ($results as $bc) {
			$parent_categories[$bc['blog_category_id']] = $bc['name'];
		}
		
		$fields =  array('status', 'parent_id', 'keyword', 'sort_order');

		foreach ($fields as $f) {
			if (isset ($this->request->post [$f])) {
				$this->data [$f] = $this->request->post [$f];
			} elseif (isset($blog_category_info) && isset($blog_category_info[$f])) {
				$this->data[$f] = $blog_category_info[$f];
			} else {
				$this->data[$f] = '';
			}
		}

		if (isset($this->request->post['blog_category_description'])) {
			$this->data['blog_category_description'] = $this->request->post['blog_category_description'];
		} elseif (isset($blog_category_info)) {
			$this->data['blog_category_description'] = $this->model_design_blog_category->getBlogCategoryDescriptions($blog_category_id);
		} else {
			$this->data['blog_category_description'] = array();
		}


		if ($this->data['parent_id'] == '') {
			$this->data['parent_id'] = 0;
		}

		if (!$blog_category_id) {
			$this->data['action'] = $this->html->getSecureURL('design/blog_category/insert');
			$this->data['heading_title'] = $this->language->get('text_insert') . ' ' . $this->language->get('text_blog_category');
			$this->data['update'] = '';
			$form = new AForm('ST');
		} else {
			$this->data['action'] = $this->html->getSecureURL('design/blog_category/update', '&blog_category_id=' . $blog_category_id);
			$this->data['heading_title'] = $this->language->get('text_edit') . ' ' . $this->language->get('text_blog_category') . ' - ' . $this->data['blog_category_description'][$this->session->data['content_language_id']]['name'];
			$this->data['update'] = $this->html->getSecureURL('listing_grid/blog_category/update_field', '&id=' . $blog_category_id);
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

		$this->data['form']['fields']['status'] = $form->getFieldHtml(
				array('type' => 'checkbox',
						'name' => 'status',
						'value' => $this->data['status'],
						'style' => 'btn_switch',
				));
		$this->data['form']['fields']['parent_category'] = $form->getFieldHtml(
				array('type' => 'selectbox',
						'name' => 'parent_id',
						'value' => $this->data['parent_id'],
						'options' => $parent_categories,
						'style' => 'medium-field'
				));
		$this->data['form']['fields']['name'] = $form->getFieldHtml(
				array('type' => 'input',
						'name' => 'blog_category_description[' . $this->session->data['content_language_id'] . '][name]',
						'value' => $this->data['blog_category_description'][$this->session->data['content_language_id']]['name'],
						'required' => true,
						'style' => 'large-field',
						'attr' => ' maxlength="255" ',
				));
		$this->data['form']['fields']['description'] = $form->getFieldHtml(
				array('type' => 'texteditor',
						'name' => 'blog_category_description[' . $this->session->data['content_language_id'] . '][description]',
						'value' => $this->data['blog_category_description'][$this->session->data['content_language_id']]['description'],
						'style' => 'xl-field',
				));
		$this->data['form']['fields']['page_title'] = $form->getFieldHtml(
				array('type' => 'input',
						'name' => 'blog_category_description[' . $this->session->data['content_language_id'] . '][page_title]',
						'value' => $this->data['blog_category_description'][$this->session->data['content_language_id']]['page_title'],
						'style' => 'large-field',
						'attr' => ' maxlength="255" ',
				));
		$this->data['form']['fields']['meta_keywords'] = $form->getFieldHtml(
				array('type' => 'textarea',
						'name' => 'blog_category_description[' . $this->session->data['content_language_id'] . '][meta_keyword]',
						'value' => $this->data['blog_category_description'][$this->session->data['content_language_id']]['meta_keyword'],
						'style' => 'xl-field',
				));
		$this->data['form']['fields']['meta_description'] = $form->getFieldHtml(
				array('type' => 'textarea',
						'name' => 'blog_category_description[' . $this->session->data['content_language_id'] . '][meta_description]',
						'value' => $this->data['blog_category_description'][$this->session->data['content_language_id']]['meta_description'],
						'style' => 'xl-field',
				));

		$this->data['keyword_button'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'generate_seo_keyword',
				'text' => $this->language->get('button_generate'),
			//set button not to submit a form
				'attr' => 'type="button"',
				'style' => 'btn btn-info'
		));
		$this->data['generate_seo_url'] = $this->html->getSecureURL('common/common/getseokeyword', '&object_key_name=blog_category_id&id=' . $blog_category_id);

		$this->data['form']['fields']['keyword'] .= $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'keyword',
				'value' => $this->data['keyword'],
				'help_url' => $this->gen_help_url('seo_keyword'),
				'multilingual' => true,
				'attr' => ' gen-value="' . SEOEncode($this->data['blog_category_description']['name']) . '" '
		));

		$this->data['form']['fields']['sort_order'] = $form->getFieldHtml(
				array('type' => 'input',
						'name' => 'sort_order',
						'value' => $this->data['sort_order'],
						'style' => 'tiny-field'
				));
				
		$this->data['form']['fields']['org_parent_id'] = $form->getFieldHtml(
				array('type' => 'hidden',
						'name' => 'org_parent_id',
						'value' => $this->data['parent_id'],
				));
		
		$this->data['blog_category_id'] = $blog_category_id;
		$this->data['active'] = 'general';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_category_tabs', array($this->data));
		$this->data['blog_category_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);
		
		
		$this->data['bc_url'] = $this->html->getSecureURL('category/blog_category/get_categories');
		$this->data['text_none'] = $this->language->get('text_none');

		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);
		
		$this->addChild('responses/common/resource_library/get_resources_html', 'resources_html', 'responses/common/resource_library_scripts.tpl');
		$resources_scripts = $this->dispatch(
				'responses/common/resource_library/get_resources_scripts',
				array(
						'object_name' => 'blog_category',
						'object_id' => $blog_category_id,
						'types' => array('image'),
						'mode' => 'url',
				)
		);

		$this->view->assign('current_url', $this->html->currentURL());

		$this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());
		$this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&action=list_library&object_name=&object_id&type=image&mode=single'));
		
		$this->processTemplate('pages/design/blog_category_form.tpl');
	}

	private function _validateForm() {

		if (!$this->user->canModify('design/blog_category')) {
			$this->error['warning'][] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['blog_category_description'] as $language_id => $value) {
			$len = mb_strlen($value['name']);
			if (($len < 2) || ($len > 255)) {
				$this->error['warning'][] = $this->language->get('error_name');
			}
		}
		if (($error_text = $this->html->isSEOkeywordExists('blog_category_id=' . $this->request->get['blog_category_id'], $this->request->post['keyword']))) {
			$this->error['warning'][] = $error_text;
		}

		$this->extensions->hk_ValidateData($this);

		if (!$this->error) {
			return TRUE;
		} else {
			if (!isset($this->error['warning'])) {
				$this->error['warning'][] = $this->language->get('error_required_data');
			}
			$this->error['warning'] = implode('<br>', $this->error['warning']);
			return FALSE;
		}
	}


	public function edit_layout() {
		$page_controller = 'pages/blog/category';
		$page_key_param = 'blog_category_id';
		
		
		$blog_category_id = (int)$this->request->get['blog_category_id'];
		$this->data['blog_category_id'] = $blog_category_id;
		
		$page_url = $this->html->getSecureURL('design/blog_category/edit_layout', '&blog_category_id=' . $blog_category_id);
		//note: blog_category can not be ID of 0.
		if (!has_value($blog_category_id)) {
			$this->redirect($this->html->getSecureURL('design/blog_category'));
		}

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_category');
		$this->loadLanguage('design/layout');

		if (has_value($blog_category_id) && $this->request->is_GET()) {
			$this->loadModel('design/blog_category');
			$this->data['blog_category_description'] = $this->model_design_blog_category->getBlogCategoryDescriptions($blog_category_id);
		}

	    // Alert messages
	    if (isset($this->session->data['warning'])) {
	      $this->data['error_warning'] = $this->session->data['warning'];
	      unset($this->session->data['warning']);
	    }
	    if (isset($this->session->data['success'])) {
	      $this->data['success'] = $this->session->data['success'];
	      unset($this->session->data['success']);
	    }

		$this->data['heading_title'] = $this->language->get('text_edit') . ' ' . $this->language->get('tab_layout') . ' - ' . $this->data['blog_category_description'][$this->session->data['content_language_id']]['name'];

		$this->document->setTitle($this->data['heading_title']);
		$this->document->resetBreadcrumbs();
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_category'),
				'text' => $this->language->get('blog_category_name'),
				'separator' => ' :: '
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_category/update', '&blog_category_id=' . $blog_category_id),
				'text' => $this->data['heading_title'],
				'current' =>  true
		));

		$this->data['active'] = 'layout';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_category_tabs', array($this->data));
		$this->data['blog_category_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$layout = new ALayoutManager();
		//get existing page layout or generic
		$page_layout = $layout->getPageLayoutIDs($page_controller, $page_key_param, $blog_category_id);
		$page_id = $page_layout['page_id'];
		$layout_id = $page_layout['layout_id'];
		if (isset($this->request->get['tmpl_id'])) {
			$tmpl_id = $this->request->get['tmpl_id'];
		} else {
			$tmpl_id = $this->config->get('config_storefront_template');
		}			
	    $params = array(
	      'blog_category_id' => $blog_category_id,
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

		$action = $this->html->getSecureURL('design/blog_category/save_layout');
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
	    $this->data['current_url'] = $this->html->getSecureURL('design/blog_category/edit_layout', $url);
	
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
		
		$this->view->batchAssign($this->data);

		$this->processTemplate('pages/design/blog_category_layout.tpl');
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function save_layout() {
		if (($this->request->is_GET())) {
			$this->redirect($this->html->getSecureURL('design/blog_category'));
		}

		$page_controller = 'pages/blog/category';
		$page_key_param = 'blog_category_id';
		$blog_category_id = (int)$this->request->get_or_post('blog_category_id');
		
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_category');

		if (!has_value($blog_category_id)) {
			$this->redirect($this->html->getSecureURL('design/blog_category'));
		}

		// need to know unique page existing
		$post_data = $this->request->post;
		$tmpl_id = $post_data['tmpl_id'];
		$layout = new ALayoutManager();
		$pages = $layout->getPages($page_controller, $page_key_param, $category_id);
		if (count($pages)) {
			$page_id = $pages[0]['page_id'];
			$layout_id = $pages[0]['layout_id'];
		} else {
			$page_info = array('controller' => $page_controller,
					'key_param' => $page_key_param,
					'key_value' => $blog_category_id);
			$this->loadModel('design/blog_category');
			$blog_category_info = $this->model_design_blog_category->getBlogCategoryDescriptions($blog_category_id);
			if ($blog_category_info) {
				foreach ($blog_category_info as $language_id => $description) {
					if (!has_value($language_id)) {
						continue;
					}
					$page_info['page_descriptions'][$language_id] = $description;
				}
			}
		
			$page_id = $layout->savePage($page_info);
			$layout_id = '';
			// need to generate layout name
			$default_language_id = $this->language->getDefaultLanguageID();
			$post_data['layout_name'] = 'Blog Category: ' . $blog_category_info[$default_language_id]['name'];
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
		$this->redirect($this->html->getSecureURL('design/blog_category/edit_layout', '&blog_category_id=' . $blog_category_id));
	}

}
