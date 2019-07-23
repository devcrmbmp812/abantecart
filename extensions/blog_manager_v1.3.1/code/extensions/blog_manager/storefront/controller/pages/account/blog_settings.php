<?php 
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
class ControllerPagesAccountBlogSettings extends AController {
	public $data = array();
	public $error = array();
	
	public function main() {
		
		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

    	if (!$this->customer->isLogged() || !$this->session->data['blog_user_logged']) {
      		$this->session->data['redirect'] = $this->blog_html->getSecureURL('account/blog_settings');
	  		$this->redirect($this->html->getSecureURL('account/login'));
    	}
		
		if (isset ($this->session->data['warning'])) {
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data['error_warning'] = '';
			$this->data['error'] = '';
		}
		if (isset ($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			$this->session->data['success'] = '';
		} else {
			$this->data ['success'] = '';
		}
		$this->loadLanguage('blog/account');
		$this->document->setTitle($this->language->get('heading_settings_title'));
		
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));
		
		$this->loadModel('blog/blog');
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$this->data['blog_info'] = $blog_info;
		
		$this->document->addScriptBottom($this->view->templateResource('/javascript/jstz-1.0.4.min.js'));
		
      	$this->document->resetBreadcrumbs();

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('index/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => FALSE
      	 )); 
		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/account'),
        	'text'      => $this->language->get('text_account'),
        	'separator' => $this->language->get('text_separator')
      	 ));
		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/blog_settings'),
        	'text'      => $this->language->get('heading_settings_title'),
        	'separator' => $this->language->get('text_separator')
      	 ));
			
		  
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		$blog_user_data = $this->model_blog_blog->getCustBlogUserData($this->customer->getId());
		
		if ($this->request->is_POST() && $this->_validateEditForm()) {
			if (!$this->error) {
				$this->model_blog_blog->editUser($this->request->post['blog_user_id'],$this->request->post);
				$this->session->data['success'] = $this->language->get('user_edit_success');
				$this->redirect($this->blog_html->getSecureURL('account/blog_settings'));	
			}
		}
		if ($this->request->is_GET() && isset($this->request->get['remove']) ) {
			$this->model_blog_blog->editUserNotifications($this->request->get['remove'],$this->request->get['type']);
			$this->redirect($this->blog_html->getSecureURL('account/blog_settings'));
		}
		
		$name_options = array(
			0 => $this->language->get('text_use_username'),
			1 => $this->language->get('text_use_fullname')
		);

		$this->view->assign('heading_title', $this->language->get('heading_settings_title'));
		 
		$this->data['user_name'] = $blog_user_data['username'];
		$this->data['role'] = $blog_user_data['role'];
		$this->data['first_name'] = $blog_user_data['firstname'];
		$this->data['last_name'] = $blog_user_data['lastname'];
		$this->data['email'] = $blog_user_data['email'];
		$this->data['name_option'] = $blog_user_data['name_option'];
		
		$tz_list = array(0 => $this->language->get('text_select_time_zone'));
		$time_zone_list = $this->model_blog_blog->get_tz_list();
		foreach ($time_zone_list as $list) {
			$tz_list[$list['zone']] =  $list['GMT_diff'] . ' - ' . $list['zone'];
		}
		 
		$this->data['action'] = $this->blog_html->getSecureURL('account/blog_settings');
		
		$form = new AForm;
		$form->setForm(array(
			'form_name' => 'blogUEFrm',
			'update' => '',
		));

		$this->data['form']['id'] = 'blogUEFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogUEFrm',
				'action' => $this->data['action'],
		));
		$this->data['form']['submit'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_submit'),
				'style' => 'btn btn-primary',
		));
																			   
		$this->data['form']['cancel'] = $form->getFieldHtml(array(
				'type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'btn btn-default',
		));
		$this->data['form']['name_option'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'name_option',
			'value' => $this->data['name_option'],
            'options' => $name_options,
		));

		if($blog_info['show_site_url']) {
			$this->data['form']['site_url'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'site_url',
					'value' => $blog_user_data['site_url'] ? $blog_user_data['site_url'] : $this->request->post['site_url'],
					'placeholder' => $this->language->get('text_http'),
			));
		}else{
			$this->data['no_site_url'] = 'true';
		}
		
		$this->data['form']['users_tz'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'users_tz',
			'options' => $tz_list,
			'value'   => $this->data['users_tz'],
			
		));
		
		$this->data['form']['blog_user_id'] = $form->getFieldHtml(array(
				'type' => 'hidden',
				'name' => 'blog_user_id',
				'value' => $blog_user_data['blog_user_id']
		)); 
			   
		$this->data['error_warning'] = $this->error['warning'];
		$this->data['error_site_url'] = $this->error['site_url'];
		
		//notifications
		if($blog_info['notification_all'] || $blog_info['notification_on_reply']) {
			$this->data['notifications'] = $this->model_blog_blog->getUserNotifications($blog_user_data['blog_user_id']);
		}
		 
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/account/blog_settings.tpl');

		//init controller data
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
		
	}
	
	private function _validateEditForm() {	
		$error = array();
		
		if($this->request->post['site_url']) {
			$site_url = html_entity_decode($this->request->post['site_url']);
			$site_url = $this->cleanElements($site_url);
			$site_url = strip_tags($site_url);
			$site_url = filter_var(trim($site_url), FILTER_SANITIZE_URL);
			if(!filter_var(trim($site_url), FILTER_VALIDATE_URL)) {		
				$error['site_url'] = $this->language->get('error_site_url');
			}	
		}

		$this->error = $error;
		if ($this->error) {
			return $this->error;
		}else{
			return true;
		}
	
	}
	
	static function cleanElements($html){
  
		 $search = array (
			   "'<script[^>]*?>.*?</script>'si",  //remove js
				"'<style[^>]*?>.*?</style>'si", //remove css 
				"'<head[^>]*?>.*?</head>'si", //remove head
			   "'<link[^>]*?>.*?</link>'si", //remove link
			   "'<body[^>]*?>.*?</body>'si", //remove body
			   "'<object[^>]*?>.*?</object>'si",
			   "'on[A-Za-z]*?\=\".*?\"/'", //removes onclick, onmouseover, etc.
			   "'<![\s\S]*?--[ \t\n\r]*>'"  // Strip multi-line comments
	 	);                  
  		return preg_replace ($search, '', $html);
 	}
	
}

?>