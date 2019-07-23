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

class ControllerPagesDesignBlogEntry extends AController {
	private $error = array();
	public $data = array();
	

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_entry');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_entry'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: ',
				'current' => true
		));

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		
		$blog_categories = array(0 => $this->language->get('text_select_category'));
		$this->loadModel('design/blog_category');
		$results = $this->model_design_blog_category->getThisBlogCategories(0,'');
		foreach ($results as $bc) {
			$blog_categories[$bc['blog_category_id']] = $bc['name'];
		}
		
		if(isset($this->request->get['blog_category'])) {
			$url = $this->html->getSecureURL('listing_grid/blog_entry','&blog_category='.(int)$this->request->get['blog_category']);
		}elseif(isset($this->request->get['blog_author'])) {
			$url = $this->html->getSecureURL('listing_grid/blog_entry','&blog_author='.(int)$this->request->get['blog_author']);
		}else{
			$url = $this->html->getSecureURL('listing_grid/blog_entry');	
		}

		$grid_settings = array(
				'table_id' => 'blog_entry_grid',
				'url' => $url,
				'editurl' => $this->html->getSecureURL('listing_grid/blog_entry/update'),
				'update_field' => $this->html->getSecureURL('listing_grid/blog_entry/update_field'),
				'sortname' => 'sort_order',
				'sortorder' => 'asc',
				'actions' => array(
						'edit' => array(
								'text' => $this->language->get('text_edit'),
								'href' => $this->html->getSecureURL('design/blog_entry/update', '&blog_entry_id=%ID%'),
								'children' => array_merge(array(
							                'general' => array(
										                'text' => $this->language->get('tab_general'),
										                'href' => $this->html->getSecureURL('design/blog_entry/update', '&blog_entry_id=%ID%'),
						                                ),
							                'layout' => array(
										                'text' => $this->language->get('tab_layout'),
										                'href' => $this->html->getSecureURL('design/blog_entry/edit_layout', '&blog_entry_id=%ID%'),
						                                ),
								),(array)$this->data['grid_edit_expand'])
						),
						'share-square-o' => array(
								'text' => $this->language->get('text_comment'),
								'href' => $this->html->getSecureURL('design/blog_comment/reply', '&blog_entry_id=%ID%'),
						),
						'delete' => array(
								'text' => $this->language->get('button_delete'),
								'href' => $this->html->getSecureURL('design/blog_entry/delete', '&blog_entry_id=%ID%')
						)
				),
				'multiselect' => 'false',
		);

		$grid_settings['colNames'] = array(
				$this->language->get('column_entry_id'),
				$this->language->get('column_entry_title'),
				$this->language->get('column_author'),
				$this->language->get('column_status'),
				$this->language->get('column_views'),
				$this->language->get('column_entry_comments'),
				$this->language->get('column_release_date'),
		);
		$grid_settings['colModel'] = array(
				array(
						'name' => 'entry_id',
						'index' => 'entry_id',
						'align' => 'center',
						'width' => 70,
						'sortable' => false,
						'search' => false,
				),
				array(
						'name' => 'entry_name',
						'index' => 'entry_name',
						'width' => 150,
						'align' => 'left',
				),
				array(
						'name' => 'author',
						'index' => 'author',
						'width' => 120,
						'align' => 'left',
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
						'name' => 'views',
						'index' => 'views',
						'width' => 100,
						'align' => 'center',
						'search' => false,
						'sortable' => false,
				),
				array(
						'name' => 'comments',
						'index' => 'comments',
						'width' => 100,
						'align' => 'center',
						'search' => false,
						'sortable' => false,
				),
				array(
						'name' => 'release_date',
						'index' => 'release_date',
						'width' => 75,
						'align' => 'center',
						'search' => false,
				),
		);
		
		$this->loadModel('design/blog_author');
		$authors = array(0 => $this->language->get('text_select_author') );
        $results = $this->model_design_blog_author->getAuthorNames();
        foreach( $results as $r ) {
            $authors[ $r['blog_author_id'] ] = $r['firstname'] . ' ' . $r['lastname'];
        }

		$form = new AForm();
		$form->setForm(array(
				'form_name' => 'blog_entry_grid_search',
		));

		$grid_search_form = array();
		$grid_search_form['id'] = 'blog_entry_grid_search';
		$grid_search_form['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blog_entry_grid_search',
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
		$grid_search_form['fields']['blog_category'] = $form->getFieldHtml(array(
		    'type' => 'selectbox',
		    'name' => 'blog_category',
            'options' => $blog_categories,
			'style' =>'chosen',
	    ));

		$grid_settings['search_form'] = true;

		$grid = $this->dispatch('common/listing_grid', array($grid_settings));
		$this->view->assign('listing_grid', $grid->dispatchGetOutput());
		$this->view->assign('search_form', $grid_search_form);
		$this->view->assign('grid_url', $this->html->getSecureURL('listing_grid/blog_entry'));

		$this->document->setTitle($this->language->get('heading_title'));
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_entry/insert'));
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

		$this->processTemplate('pages/design/blog_entry_list.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function insert() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));
		$this->loadLanguage('blog_manager/blog_entry');

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		if ($this->request->is_POST() && $this->_validateForm()) {

			$languages = $this->language->getAvailableLanguages();
			foreach ($languages as $l) {
				if ($l['language_id'] == $this->session->data['content_language_id']) continue;
				$this->request->post['blog_entry_descriptions'][$l['language_id']] = $this->request->post['blog_entry_description'][$this->session->data['content_language_id']];
			}

			$blog_entry_id = $this->model_design_blog_entry->addEntry($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/blog_entry/update', '&blog_entry_id=' . $blog_entry_id));
		}
		$this->_getForm();

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));
		$this->loadLanguage('blog_manager/blog_entry');
		$blog_entry_id = $this->request->get['blog_entry_id'];

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
		$this->view->assign('insert', $this->html->getSecureURL('design/blog_entry/insert'));

		if ($this->request->is_POST() && $this->_validateForm()) {
			$this->model_design_blog_entry->editEntry($blog_entry_id, $this->request->post);
			$this->session->data['success'] = $this->language->get('text_update_success');
			$this->redirect($this->html->getSecureURL('design/blog_entry/update', '&blog_entry_id=' . $blog_entry_id));
		}
		$this->_getForm();

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function delete() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		if ($this->request->is_GET()) {
			$blog_entry_id = $this->request->get['blog_entry_id'];
			
			$this->loadModel('design/blog_entry');
			$this->model_design_blog_entry->deleteEntry($blog_entry_id);
			$this->redirect($this->html->getSecureURL('design/blog_entry'));
		}
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	private function _getForm() {

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('error_name', $this->error['name']);
		$this->loadModel('design/blog_entry');
		$this->loadLanguage('blog_manager/blog_entry');

		$blog_entry_id = $this->request->get['blog_entry_id'];
		
		if (has_value($blog_entry_id)) {
			$blog_entry_info = $this->model_design_blog_entry->getEntry($blog_entry_id);
		}
		
		$this->document->initBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_entry'),
				'text' => $this->language->get('heading_title'),
				'separator' => ' :: '
		));
		
		$dc = $this->model_design_blog_entry->getblog_config('default_blog_category');
		$default_category = ($dc ? $dc : '');
		
		$blog_categories = array();
		$this->loadModel('design/blog_category');
		$results = $this->model_design_blog_category->getThisBlogCategories(0,'');
		foreach ($results as $bc) {
			$blog_categories[$bc['blog_category_id']] = $bc['name'];
		}
		
		$this->loadModel('design/blog_author');
		$blog_authors = $this->model_design_blog_author->getAuthorNames();
		$this->data['blog_authors'] = array(0 => $this->language->get('text_none'));
		foreach ($blog_authors as $ba) {
			$this->data['blog_authors'][$ba['blog_author_id']] = $ba['firstname'] . ' ' . $ba['lastname'];
		}

		$blog_entries= $this->model_design_blog_entry->getBlogsEntriesForSelect();
		$this->data['blog_entries'] = array(0 => $this->language->get('text_none'));
		foreach ($blog_entries as $be) {
			$this->data['blog_entries'][$be['blog_entry_id']] = $be['entry_name'];
		}
		
		if (isset($this->request->get['blog_entry_id'])) {
			$blog_entry_id = $this->request->get['blog_entry_id'];
			$this->data['blog_entry_id'] = $blog_entry_id;
			unset($this->data['blog_entries'][$blog_entry_id]);
		}
		
		$this->view->assign('cancel', $this->html->getSecureURL('design/blog_entry'));
		
		$fields = array('blog_entry_id', 'status', 'use_intro', 'use_image', 'blog_author_id', 'keyword', 'allow_comment', 'release_date');
		
		foreach ($fields as $f) {
			if (isset ($this->request->post [$f])) {
				$this->data [$f] = $this->request->post [$f];
			} elseif (isset($blog_entry_info) && isset($blog_entry_info[$f])) {
				$this->data[$f] = $blog_entry_info[$f];
			} else {
				$this->data[$f] = '';
			}
		}

		if (isset($this->request->post['blog_entry_descriptions'])) {
			$this->data['blog_entry_descriptions'] = $this->request->post['blog_entry_descriptions'];
		} elseif (isset($blog_entry_info)) {
			$this->data['blog_entry_descriptions'] = $this->model_design_blog_entry->getEntryDescriptions($blog_entry_id);
		} else {
			$this->data['blog_entry_descriptions'] = array();
		}
		
		if (isset($this->request->post['blog_category'])) {
			$this->data['blog_category'] = $this->request->post['blog_category'];
		} elseif (isset($blog_entry_info)) {
			$this->data['blog_category'] = $this->model_design_blog_entry->getBlogCategories($blog_entry_id);
		} else {
			$this->data['blog_category'] = array();
		}
		
		if (isset($this->request->post['related_entries'])) {
			$this->data['related_entries'] = $this->request->post['related_entries'];
		} elseif (isset($blog_entry_info)) {
			$this->data['related_entries'] = $this->model_design_blog_entry->getRelatedEntries($blog_entry_id);
		} else {
			$this->data['related_entries'] = array();
		}
		
		if (isset($this->request->post['related_category'])) {
			$this->data['related_category'] = $this->request->post['related_category'];
		} elseif (isset($blog_entry_info)) {
			$this->data['related_category'] = $this->model_design_blog_entry->getRelatedCategories($blog_entry_id);
		} else {
			$this->data['related_category'] = array();
		}
		
		if (isset($this->request->post['related_products'])) {
			$this->data['related_products'] = $this->request->post['related_product'];
		} elseif (isset($blog_entry_info)) {
			$this->data['related_products'] = $this->model_design_blog_entry->getRelatedProducts($blog_entry_id);
		} else {
			$this->data['related_products'] = array();
		}

		if (!$blog_entry_id) {
			$this->data['action'] = $this->html->getSecureURL('design/blog_entry/insert');
			$this->data['heading_title'] = $this->language->get('text_entry_insert');
			$this->data['update'] = '';
			$form = new AForm('ST');
		} else {
			$this->data['action'] = $this->html->getSecureURL('design/blog_entry/update', '&blog_entry_id=' . $blog_entry_id);
			$this->data['heading_title'] = $this->language->get('text_entry_update') . ' - ' . $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['entry_title'];
			$this->data['update'] = $this->html->getSecureURL('listing_grid/blog_entry/update_field', '&id=' . $blog_entry_id);
			$form = new AForm('HS');
		}

		$this->document->addBreadcrumb(
				array('href' => $this->data['action'],
						'text' => $this->data['heading_title'],
						'separator' => ' :: ',
						'current' => true
				));

		$form->setForm(
				array('form_name' => 'editEntryFrm',
						'update' => $this->data['update'],
				));

		$this->data['form']['id'] = 'editEntryFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(
				array('type' => 'form',
						'name' => 'editEntryFrm',
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
		if ( $this->data['allow_comment'] == 0) {
			$allow_comment = 0;
		}else{
			$allow_comment = 1;
		}
		$this->data['form']['fields']['allow_comment'] = $form->getFieldHtml(
				array('type' => 'checkbox',
						'name' => 'allow_comment',
						'value' => $allow_comment,
						'style' => 'btn_switch',
				));	
		$this->data['form']['fields']['entry_title'] = $form->getFieldHtml(
				array('type' => 'input',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][entry_title]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['entry_title'],
						'style' => 'large-field',
						'attr' => ' maxlength="255" ',
						'required' => true,
				));
		$this->data['js_date_format'] = format4Datepicker($this->language->get('date_format_short'));
		$this->data['form']['fields']['release_date'] = $form->getFieldHtml(array(
				'type' => 'date',
				'name' => 'release_date',
				'value' => dateISO2Display($this->data['release_date'], $this->language->get('date_format_short')),
				'default' => '',
				'dateformat' => format4Datepicker($this->language->get('date_format_short')),
				'highlight' => 'future',
				'required' => true,
				'style' => 'small-field',
		));		
		$this->data['form']['fields']['blog_category'] = $form->getFieldHtml(array(
			'type' => 'checkboxgroup',
			'name' => 'blog_category[]',
			'value' => $this->data['blog_category'] ? $this->data['blog_category'] : $default_category,
            'options' => $blog_categories,
            'style' => 'chosen',
            'placeholder' => $this->language->get('text_select_blog_category'),
		));
			
		$this->data['form']['fields']['author'] = $form->getFieldHtml(
				array('type' => 'selectbox',
						'name' => 'blog_author_id',
						'value' => $this->data['blog_author_id'],
						'options' => $this->data['blog_authors'],
						'style' => 'medium-field',
				));
		$this->data['form']['fields']['use_intro'] = $form->getFieldHtml(
				array('type' => 'checkbox',
						'name' => 'use_intro',
						'value' => $this->data['use_intro'],
						'style' => 'btn_switch',
				));				
		$this->data['form']['fields']['entry_intro'] = $form->getFieldHtml(
				array('type' => 'texteditor',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][entry_intro]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['entry_intro'],
						'style' => 'xl-field',
				));
		if ($blog_entry_id) {
			$this->data['form']['fields']['use_image'] = $form->getFieldHtml(
					array('type' => 'checkbox',
							'name' => 'use_image',
							'value' => $this->data['use_image'],
							'style' => 'btn_switch',
					));	
		}
		$this->data['form']['fields']['content'] = $form->getFieldHtml(
				array('type' => 'texteditor',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][content]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['content'],
						'style' => 'xl-field',
				));	
		$this->data['form']['fields']['reference'] = $form->getFieldHtml(
				array('type' => 'textarea',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][reference]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['reference'],
						'style' => 'xl-field',
				));
		$this->data['form']['fields']['meta_keywords'] = $form->getFieldHtml(
				array('type' => 'textarea',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][meta_keywords]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['meta_keywords'],
						'style' => 'xl-field',
				));
		$this->data['form']['fields']['meta_description'] = $form->getFieldHtml(
				array('type' => 'textarea',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][meta_description]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['meta_description'],
						'style' => 'xl-field',
				));

		$this->data['keyword_button'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'generate_seo_keyword',
				'text' => $this->language->get('button_generate'),
				'attr' => 'type="button"',
				'style' => 'btn btn-info'
		));
		$this->data['generate_seo_url'] = $this->html->getSecureURL('common/common/getseokeyword', '&object_key_name=blog_entry_id&id=' . $blog_entry_id);

		$this->data['form']['fields']['keyword'] .= $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'keyword',
				'help_url' => $this->gen_help_url('seo_keyword'),
				'multilingual' => true,
				'value' => $this->data['keyword'],
				'attr' => ' gen-value="' . SEOEncode($this->data['blog_entry_description']['entry_title']) . '" '
		));
		$this->data['form']['fields']['copyright'] = $form->getFieldHtml(
				array('type' => 'textarea',
						'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][copyright]',
						'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['copyright'],
						'style' => 'xl-field',
				));

		$this->data['form']['fields']['entries_lead'] .= $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][entries_lead]',
					'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['entries_lead'],
					'style' => 'large-field',
			));
		$this->data['form']['fields']['related_entries'] = $form->getFieldHtml(array(
		    	'type' => 'checkboxgroup',
		    	'name' => 'related_entries[]',
		    	'value' => $this->data['related_entries'],
		    	'options' => $this->data['blog_entries'],
		    	'style' => 'chosen',
		    	'placeholder' => $this->language->get('text_select_related_entry'),
		));
		
		$this->data['form']['fields']['category_lead'] .= $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][category_lead]',
				'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['category_lead'],
				'style' => 'large-field',
		));
		
		$this->loadModel('catalog/category');
        $this->data['categories'] = array();
        $results = $this->model_design_blog_entry->getCategories(0);
        foreach ($results as $r) {
            $this->data['categories'][$r['category_id']] = $r['name'];
        }
		
		$this->data['form']['fields']['related_category'] = $form->getFieldHtml(array(
		    	'type' => 'checkboxgroup',
		    	'name' => 'related_category[]',
		    	'value' => $this->data['related_category'],
		    	'options' => $this->data['categories'],
		    	'style' => 'chosen',
		    	'placeholder' => $this->language->get('text_select_category'),
		));
		
		//load only prior saved products 
		$resource = new AResource('image');
		$this->data['products'] = array();
		if (count($this->data['related_products'])) {
			$this->loadModel('catalog/product');
			$filter = array('subsql_filter' => 'p.product_id in (' . implode(',', $this->data['related_products']) . ')' );
			$results = $this->model_catalog_product->getProducts($filter);
			foreach( $results as $r ) {
				$thumbnail = $resource->getMainThumb('products',
												$r['product_id'],
												(int)$this->config->get('config_image_grid_width'),
												(int)$this->config->get('config_image_grid_height'),
												true);
				$this->data['products'][$r['product_id']]['name'] = $r['name']." (".$r['model'].")";
				$this->data['products'][$r['product_id']]['image'] = $thumbnail['thumb_html'];
			}
		}
		$this->data['form']['fields']['product_lead'] .= $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'blog_entry_descriptions[' . $this->session->data['content_language_id'] . '][product_lead]',
				'value' => $this->data['blog_entry_descriptions'][$this->session->data['content_language_id']]['product_lead'],
				'style' => 'large-field',
		));
		$this->data['form']['fields']['related_products'] = $form->getFieldHtml( array(
		    	'type' => 'multiselectbox',
		    	'name' => 'related_products[]',
		    	'value' => $this->data['related_products'],
		    	'options' => $this->data['products'],
		    	'style' => 'chosen',
		    	'ajax_url' => $this->html->getSecureURL('r/product/product/products'),
		    	'placeholder' => $this->language->get('text_select_from_lookup'),
		));
		
		
		$this->addChild('responses/common/resource_library/get_resources_html', 'resources_html', 'responses/common/resource_library_scripts.tpl');
		$resources_scripts = $this->dispatch(
				'responses/common/resource_library/get_resources_scripts',
				array(
						'object_name' => 'blog_entry',
						'object_id' => $this->data['blog_entry_id'],
						'types' => array('image'),
				)
		);
		
		$this->view->assign('current_url', $this->html->currentURL());
		$this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());
		$this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&object_name=blog_entry&type=image'));
		$this->view->assign('entry_use_intro', sprintf($this->language->get('entry_use_intro'), $blog_info['word_count_main']));
		
		$this->data['blog_entry_id'] = $blog_entry_id;
		
		$this->data['active'] = 'general';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/design/blog_entry_tabs', array($this->data));
		$this->data['entry_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->view->batchAssign($this->data);
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('language_id', $this->session->data['content_language_id']);
		$this->view->assign('language_code', $this->session->data['language']);

		$this->processTemplate('pages/design/blog_entry_form.tpl');
	}

	private function _validateForm() {

		if (!$this->user->canModify('design/blog_entry')) {
			$this->error['warning'][] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['blog_entry_descriptions'] as $language_id => $value) {
			$len = mb_strlen($value['entry_title']);
			if (($len < 2) || ($len > 250)) {
				$this->error['warning'][] = $this->language->get('error_title');
			}
			
			$len = mb_strlen($value['content']);
			if ($len < 2) {
				$this->error['warning'][] = $this->language->get('error_content');
			}
		}
		if (($error_text = $this->html->isSEOkeywordExists('blog_entry_id=' . $this->request->get['blog_entry_id'], $this->request->post['keyword']))) {
			$this->error['warning'][] = $error_text;
		}
		if (!$this->request->post['release_date']) {
			$this->error['warning'][] = $this->language->get('error_date');	
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
		$page_controller = 'pages/blog/entry';
		$page_key_param = 'blog_entry_id';
		
		$blog_entry_id = (int)$this->request->get['blog_entry_id'];
		$this->data['blog_entry_id'] = $blog_entry_id;

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('design/layout');
		$this->loadLanguage('blog_manager/blog_entry');
		$this->document->setTitle($this->language->get('update_layout'));

		$page_url = $this->html->getSecureURL('design/blog_entry/edit_layout');

	    // Alert messages
	    if (isset($this->session->data['warning'])) {
	      $this->data['error_warning'] = $this->session->data['warning'];
	      unset($this->session->data['warning']);
	    }
	    if (isset($this->session->data['success'])) {
	      $this->data['success'] = $this->session->data['success'];
	      unset($this->session->data['success']);
	    }
		
		if (has_value($blog_entry_id) && $this->request->is_GET()) {
			$this->loadModel('design/blog_entry');
			$this->data['blog_entry_description'] = $this->model_design_blog_entry->getEntryDescriptions($blog_entry_id);
		}
		
		$this->data['heading_title'] = $this->language->get('text_edit') . ' ' . $this->language->get('tab_layout') . ' - ' . $this->data['blog_entry_description'][$this->session->data['content_language_id']]['entry_title'];


		$this->document->resetBreadcrumbs();
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('index/home'),
				'text' => $this->language->get('text_home'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/blog_entry'),
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
		$tabs_obj = $this->dispatch('pages/design/blog_entry_tabs', array($this->data));
		$this->data['entry_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$layout = new ALayoutManager();
		//get existing page layout or generic
		$page_layout = $layout->getPageLayoutIDs($page_controller, $page_key_param, $blog_entry_id);
		$page_id = $page_layout['page_id'];
		$layout_id = $page_layout['layout_id'];
		if (isset($this->request->get['tmpl_id'])) {
			$tmpl_id = $this->request->get['tmpl_id'];
		} else {
			$tmpl_id = $this->config->get('config_storefront_template');
		}			
	    $params = array(
	      'blog_entry_id' => $blog_entry_id,
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

		$action = $this->html->getSecureURL('design/blog_entry/save_layout');
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
	    $this->data['current_url'] = $this->html->getSecureURL('design/blog_entry/edit_layout', $url);
	
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
		$this->processTemplate('pages/design/blog_entry_layout.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function save_layout() {
		
		if ($this->request->is_GET()) {
			$this->redirect($this->html->getSecureURL('design/blog_entry'));
		}
		
		$page_controller = 'pages/blog/entry';
		$page_key_param = 'blog_entry_id';
		$blog_entry_id = $this->request->post['blog_entry_id'];
		
		$this->loadLanguage('blog_manager/blog_entry');

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		if ($this->request->is_POST()) {

			// need to know unique page existing
			$post_data = $this->request->post;
			$tmpl_id = $post_data['tmpl_id'];
			$layout = new ALayoutManager();
			
			$pages = $layout->getPages($page_controller, $page_key_param, $blog_entry_id);
			if (count($pages)) {
				$page_id = $pages[0]['page_id'];
				$layout_id = $pages[0]['layout_id'];
			} else {
				// create new page record
				$page_info = array('controller' => $page_controller,
						'key_param' => $page_key_param,
						'key_value' => $blog_entry_id);

				$default_language_id = $this->language->getDefaultLanguageID();
				$languages = $this->language->getAvailableLanguages();
				$this->loadModel('design/blog_entry');
				$entry_info = $this->model_design_blog_entry->getEntry($blog_entry_id, $default_language_id);
				if ($entry_info) {
					foreach ( $languages as $l ) {
						$entry_info['name'] = 'Blog Article: ' . $entry_info['entry_title'];
						$page_info['page_descriptions'][ $l['language_id'] ] = $entry_info;
					}
				}
				$page_id = $layout->savePage($page_info);
				$layout_id = '';
				// need to generate layout name
				$post_data['layout_name'] = 'Blog Article: ' . $entry_info['entry_title'];
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

			$this->redirect($this->html->getSecureURL('design/blog_entry/edit_layout', '&blog_entry_id=' . $blog_entry_id));
		}
		$this->redirect($this->html->getSecureURL('design/blog_entry/'));
	}


}
