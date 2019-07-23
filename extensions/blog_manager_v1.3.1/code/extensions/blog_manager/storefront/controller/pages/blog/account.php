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
class ControllerPagesBlogAccount extends AController {
	public $data = array();
	public $error = array();
	
	public function main() {
		$this->loadModel('blog/blog');
		$source = $this->model_blog_blog->getblog_config('login_data');

		if(!$source || $source == 'customer') {
			$this->redirect($this->html->getSecureURL('account/login'));
		}
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadLanguage('blog/account');
		$this->loadLanguage('blog/blog');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));	
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/jstz-1.0.4.min.js'));
		
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
		
		$name_options = array(
			0 => $this->language->get('text_use_username'),
			1 => $this->language->get('text_use_fullname')
		);

		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$this->data['blog_info'] = $blog_info;
		
		$this->view->assign('heading_title', $blog_info['title']. ' - ' . $this->language->get('text_heading'));
		
		if ($this->request->is_POST() && $this->_validateForm()) {
			
			if (!$this->error) {
				$blog_user_id = $this->model_blog_blog->addUser($this->request->post);			
				$subject = sprintf($this->language->get('text_subject'), $blog_info['title']);
				$message = sprintf($this->language->get('text_welcome'), $blog_info['title']) . "\n\n";
				if (!$blog_info['approve_user']) {
					if($blog_info['user_email_activation']){
						$code = md5(mt_rand(1,3000));
						$email = $this->request->post['email'];
						$this->session->data['blog_activation'] = array(
																	'blog_user_id' => $blog_user_id,
																	'code' => $code,
																	'email' => $email);

						$message .= sprintf($this->language->get('text_activate'), "\n".$this->blog_html->getSecureURL('blog/account/success', '&activation='.$code.'&email='.$email) ) . "\n\n";
						$message .= $this->language->get('text_services') . "\n\n";
					}else{
						$message .= sprintf($this->language->get('text_login'), "\n". $this->blog_html->getBLOGHOME()) . "\n\n";
					}
				} else {
					$message .= sprintf($this->language->get('text_approval'), "\n". $this->blog_html->getBLOGHOME()) . "\n\n";
					$message .= $this->language->get('text_services') . "\n\n";
				}

				$message .= $this->language->get('text_thanks') . "\n";
				$message .= $blog_info['title'];

				$mail = new AMail($this->config);
				$mail->setTo($this->request->post['email']);
				$mail->setFrom($blog_info['owner_email']);
				$mail->setSender($blog_info['title']);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();

				$this->extensions->hk_UpdateData($this,__FUNCTION__);
				
				if (!$blog_info['approve_user']  && !$blog_info['user_email_activation'] ){
					$result = $this->loginUser($this->request->post['user_name'], $this->request->post['password']);
				}
				
				$this->redirect($this->blog_html->getSecureURL('blog/account/success'));
			}
		}
		
		$tz_list = array(0 => $this->language->get('text_select_time_zone'));
		$time_zone_list = $this->model_blog_blog->get_tz_list();
		foreach ($time_zone_list as $list) {
			$tz_list[$list['zone']] =  $list['GMT_diff'] . ' - ' . $list['zone'];
		}

		$this->document->resetBreadcrumbs();

   		$this->document->addBreadcrumb( array ( 
      		'href'      => $this->html->getURL('index/home'),
       		'text'      => $this->language->get('text_store'),
       		'separator' => FALSE
   		));

		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getSecureURL('blog/account'),
			'text'      => $this->language->get('text_heading'),
			'separator' => $this->language->get('text_separator')
		 ));
	
		
		$this->data['action'] = $this->blog_html->getSecureURL('blog/account');
		
		$form = new AForm;
		$form->setForm(array(
			'form_name' => 'blogRegFrm',
			'update' => '',
		));

		$this->data['form']['id'] = 'blogRegFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogRegFrm',
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
	
		$this->data['form']['fields']['first_name'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'first_name',
				'value' => $this->data['first_name'] ? $this->data['first_name'] : $this->request->post['first_name'],
		));
		$this->data['form']['fields']['last_name'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'last_name',
				'value' => $this->data['last_name'] ? $this->data['last_name'] : $this->request->post['last_name'],
		));
		$this->data['form']['fields']['user_name'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'user_name',
				'value' => $this->data['user_name'] ? $this->data['user_name'] : $this->request->post['user_name'],
				'required' => true,
		));
		$this->data['form']['fields']['name_option'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'name_option',
			'value' => $blog_user_data['name_option'] ? $blog_user_data['name_option'] : $this->request->post['name_option'],
            'options' => $name_options,
		));
		$this->data['form']['fields']['email'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'email',
				'value' => $this->data['email'] ? $this->data['email'] : $this->request->post['email'],
				'required' => true,
		));
		$this->data['form']['fields']['password'] = $form->getFieldHtml( array(
			   'type' => 'password',
			   'name' => 'password',
			   'value' => $this->data['password'] ? $this->data['password'] : $this->request->post['password'],
			   'required' => true ));
		$this->data['form']['fields']['confirm'] = $form->getFieldHtml( array(
			   'type' => 'password',
			   'name' => 'confirm',
			   'value' => $this->data['confirm'] ? $this->data['confirm'] : $this->request->post['confirm'],
			   'required' => true ));
		if($blog_info['show_site_url']) {
			$this->data['form']['fields']['site_url'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'site_url',
					'value' => $this->data['site_url'] ? $this->data['site_url'] : $this->request->post['site_url'],
					'placeholder' => $this->language->get('text_http'),
			));
		}
		
		$this->data['form']['fields']['users_tz'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'users_tz',
			'options' => $tz_list,
			'value'   => $this->data['users_tz'],
			
		));
		//If captcha enabled, validate
		if ($this->config->get('config_account_create_captcha')){
			if ($this->config->get('config_recaptcha_site_key')){
				$this->data['form']['fields']['captcha'] = $form->getFieldHtml(
						array (
								'type'               => 'recaptcha',
								'name'               => 'recaptcha',
								'recaptcha_site_key' => $this->config->get('config_recaptcha_site_key'),
								'language_code'      => $this->language->getLanguageCode()
						));

			} else{
				$this->data['form']['fields']['captcha'] = $form->getFieldHtml(
						array (
								'type' => 'captcha',
								'name' => 'captcha',
								'attr' => ''));
			}
		}
		
		$this->data['error_warning'] = $this->error['warning'];
		$this->data['error_user_name'] = $this->error['user_name'];
		$this->data['error_email'] = $this->error['email'];
		$this->data['error_password'] = $this->error['password'];
		$this->data['error_confirm'] = $this->error['confirm'];
		$this->data['error_site_url'] = $this->error['site_url'];
		$this->data['error_captcha'] = $this->error['captcha'];
			
		$this->view->batchAssign($this->data);
        $this->processTemplate('pages/blog/account_register.tpl');

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}
	
	public function settings() {
		if (!$this->session->data['blog_user_logged']) {
			$this->redirect($this->blog_html->getBLOGHOME());
		}

		//init controller data
		$this->extensions->hk_InitData($this,__FUNCTION__);
		
		if(!$this->session->data['blog_user_id']) {
			$this->redirect($this->blog_html->getBLOGHOME());	
		}
		$this->loadLanguage('blog/account');
		$this->loadLanguage('blog/blog');
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));	
		$this->document->setTitle($this->language->get('heading_settings_title'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/jstz-1.0.4.min.js'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/bootstrap-confirmation.min.js'));
		
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

		$blog_user_id = $this->session->data['blog_user_id'];
		
		$this->loadModel('blog/blog');  
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$this->data['blog_info'] = $blog_info;
		
		$name_options = array(
			0 => $this->language->get('text_use_username'),
			1 => $this->language->get('text_use_fullname')
		);
		
		if ($this->request->is_POST() && $this->_validateSettingsForm()) {
			if (!$this->error) {
				$this->model_blog_blog->editUser($this->request->post['blog_user_id'],$this->request->post);
				$this->session->data['success'] = $this->language->get('user_edit_success');
				$this->redirect($this->blog_html->getSecureURL('blog/account/settings'));	
			}
		}
		if ($this->request->is_GET() && isset($this->request->get['remove']) ) {
			$this->model_blog_blog->editUserNotifications($this->request->get['remove'],$this->request->get['type']);
			$this->redirect($this->blog_html->getSecureURL('blog/account/settings'));
		}
		$blog_user_data = $this->model_blog_blog->getBlogUser($blog_user_id);
		
		if($blog_user_data['source'] == 'customer') {
			$this->data['source'] = $blog_user_data['source'];
			$blog_user_data = $this->model_blog_blog->getCustBlogUserData($blog_user_data['customer_id']);
		}
		$tz_list = array(0 => $this->language->get('text_select_time_zone'));
		$time_zone_list = $this->model_blog_blog->get_tz_list();
		foreach ($time_zone_list as $list) {
			$tz_list[$list['zone']] =  $list['GMT_diff'] . ' - ' . $list['zone'];
		}
		
		$this->view->assign('heading_title', $blog_info['title']. ' - ' . $this->language->get('heading_settings_title'));
		
		$this->document->resetBreadcrumbs();

		$this->document->addBreadcrumb( array ( 
			'href'      => $this->html->getURL('index/home'),
			'text'      => $this->language->get('text_store'),
			'separator' => FALSE
		));

		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getSecureURL('blog/account/settings'),
			'text'      => $this->language->get('heading_settings_title'),
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->data['user_name'] = $blog_user_data['username'];
		 $this->data['role'] = $blog_user_data['role'];
		 
		 $this->data['action'] = $this->blog_html->getSecureURL('blog/account/settings');
		
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
				'text' => $this->language->get('button_update'),
				'style' => 'btn btn-primary',
		));
																			   
		if(isset($this->data['source']) && $this->data['source'] == 'customer') {
			$this->data['first_name'] = $blog_user_data['firstname'];
			$this->data['last_name'] = $blog_user_data['lastname'];
			$this->data['email'] = $blog_user_data['email'];
		}else{
			
			$this->data['form']['fields']['first_name'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'first_name',
					'value' => $blog_user_data['firstname'] ? $blog_user_data['firstname'] : $this->request->post['first_name'],
			));
			$this->data['form']['fields']['last_name'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'last_name',
					'value' => $blog_user_data['lastname'] ? $blog_user_data['lastname'] : $this->request->post['last_name'],
			));
	
			$this->data['form']['fields']['email'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'email',
					'value' => $blog_user_data['email'] ? $blog_user_data['email'] : $this->request->post['email'],
					'required' => true,
			));
		}
		$this->data['form']['fields']['name_option'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'name_option',
			'value' => $blog_user_data['name_option'] ? $blog_user_data['name_option'] : $this->request->post['name_option'],
            'options' => $name_options,
		));
		if($blog_info['show_site_url']) {
			$this->data['form']['fields']['site_url'] = $form->getFieldHtml(array(
					'type' => 'input',
					'name' => 'site_url',
					'value' => $blog_user_data['site_url'] ? $blog_user_data['site_url'] : $this->request->post['site_url'],
					'placeholder' => $this->language->get('text_http'),
			));
		}
		
		$this->data['form']['fields']['users_tz'] = $form->getFieldHtml(array(
			'type'    => 'selectbox',
			'name'    => 'users_tz',
			'options' => $tz_list,
			'value'   => $blog_user_data['users_tz'] ? $blog_user_data['users_tz'] : $this->request->post['users_tz'],
			
		));
		
		$this->data['form']['fields']['blog_user_id'] = $form->getFieldHtml(array(
				'type' => 'hidden',
				'name' => 'blog_user_id',
				'value' => $blog_user_data['blog_user_id']
		)); 
		
		$this->data['password_link'] = $this->blog_html->getSecureURL('blog/account/change_password');
		$this->data['cust_edit_link'] = $this->html->getSecureURL('account/edit'); 
			   
		$this->data['error_warning'] = $this->error['warning'];
		$this->data['error_user_name'] = $this->error['user_name'];
		$this->data['error_email'] = $this->error['email'];
		$this->data['error_site_url'] = $this->error['site_url'];
		
		//notifications
		if($blog_info['notification_all'] || $blog_info['notification_on_reply']) {
			$this->data['notifications'] = $this->model_blog_blog->getUserNotifications($blog_user_data['blog_user_id']);
		}
		 
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/blog/account_settings.tpl');

		//init controller data
		$this->extensions->hk_UpdateData($this,__FUNCTION__);

		
	}
	
	public function change_password() {
		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		
		$this->loadLanguage('blog/account');
		$this->loadLanguage('blog/blog');
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));	
		$this->document->setTitle($this->language->get('heading_change_password'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		
		if (isset ($this->session->data['warning'])) {
			$this->data['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$this->data['error_warning'] = '';
			$this->data['error'] = '';
		}
		
		$this->loadModel('blog/blog');  
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$blog_user_id = $this->session->data['blog_user_id'];
		
		if ($this->request->is_POST()&& $this->_validatePWForm()) {
			if (!$this->error) {
				$result = $this->model_blog_blog->editUserPassword($this->request->post['blog_user_id'], $this->request->post['new_password']);
			
				if ($result) {
		
					$result = $this->model_blog_blog->getBlogUser($this->request->post['blog_user_id']);
					
					$subject = sprintf($this->language->get('text_password_change_subject'), $blog_info['title']);
					if($result['firstname']) {
						$message = $result['firstname'].','."\n\n";
					}else{
						$message = $result['username'].','."\n\n";
					}
					
					$message .= sprintf($this->language->get('text_password_change_greeting'), $blog_info['title']) . "\n\n";
					$message .= $this->language->get('text_password_change') . "\n\n";
					
					$message .= $blog_info['title'];
		
					$mail = new AMail( $this->config );
					$mail->setTo($result['email']);
					$mail->setFrom($blog_info['owner_email']);
					$mail->setSender($blog_info['title']);
					$mail->setSubject($subject);
					$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
					$mail->send();
					
					$this->session->data['success'] = $this->language->get('text_password_change_success');
					$this->redirect($this->blog_html->getSecureURL('blog/account/settings'));				
				}
			}
		}
		$this->view->assign('heading_title', $blog_info['title']. ' - ' . $this->language->get('heading_change_password'));
		
		$this->document->resetBreadcrumbs();

   		$this->document->addBreadcrumb( array ( 
      		'href'      => $this->html->getURL('index/home'),
       		'text'      => $this->language->get('text_store'),
       		'separator' => FALSE
   		));

		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getSecureURL('blog/account/settings'),
			'text'      => $this->language->get('heading_change_password'),
			'separator' => $this->language->get('text_separator')
		 ));
		 
		$this->data['action'] = $this->blog_html->getSecureURL('blog/account/change_password');
		$this->data['cancel'] = $this->blog_html->getSecureURL('blog/account/settings');
		$form = new AForm;
		$form->setForm(array(
			'form_name' => 'blogUPFrm',
			'update' => '',
		));

		$this->data['form']['id'] = 'blogUPFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogUPFrm',
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
		
		$this->data['form']['fields']['old_password'] = $form->getFieldHtml( array(
			   'type' => 'password',
			   'name' => 'old_password',
			   'value' => '',
			   'required' => true 
		));
		$this->data['form']['fields']['new_password'] = $form->getFieldHtml( array(
			   'type' => 'password',
			   'name' => 'new_password',
			   'value' => '',
			   'required' => true 
		));
			   
		$this->data['form']['fields']['confirm'] = $form->getFieldHtml( array(
			   'type' => 'password',
			   'name' => 'confirm',
			   'value' => '',
			   'required' => true 
		));
		$this->data['form']['fields']['blog_user_id'] = $form->getFieldHtml(array(
				'type' => 'hidden',
				'name' => 'blog_user_id',
				'value' => $this->request->get['blog_user_id'] ? $blog_user_id : $this->request->post['blog_user_id']
		));
		
		$this->data['error_old_password'] = $this->error['old_password'];
		$this->data['error_new_password'] = $this->error['new_password'];
		$this->data['error_confirm'] = $this->error['confirm'];	
		
		$this->view->batchAssign($this->data);
        $this->processTemplate('pages/blog/account_password.tpl');

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);	
		
	}
	
	public function forgot() {
		
		$this->loadModel('blog/blog');
		$source = $this->model_blog_blog->getblog_config('login_data');

		if(!$source || $source == 'customer') {
			$this->redirect($this->html->getSecureURL('account/password'));
		}
		
		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		
		$this->loadLanguage('blog/account');
		$this->loadLanguage('blog/blog');
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));	
		$this->document->setTitle($this->language->get('heading_reset_password'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		
		if (isset ($this->session->data['warning'])) {
			$this->data['error_warning'] = $this->session->data['warning'];
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
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$this->data['blog_info'] = $blog_info;
		
		if ($this->request->is_POST()) {
			if(!$this->request->post['user_name']) {
				$this->error['user_name'] = $this->language->get('text_error_user_name');
			}else{
				$loginname = $this->request->post['user_name'];
			}
			$pattern = '/^[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\.[a-zA-Z]{2,4}$/i';
			if ((mb_strlen($this->request->post['email']) > 96) || (!preg_match($pattern, $this->request->post['email']))) {
      			$this->error['email'] = $this->language->get('error_email');
    		}else{
				$email = $this->request->post['email'];
			}
			
			if($loginname && $email) {
				$blog_user_id = $this->model_blog_blog->getBlogUserByEmailUsername($email, $loginname);
			
				if ($blog_user_id) {
		
					$password = substr(md5(rand()), 0, 7);
					
					$subject = sprintf($this->language->get('text_subject'), $blog_info['title']);
					
					$message  = sprintf($this->language->get('text_greeting'), $blog_info['title']) . "\n\n";
					$message .= $this->language->get('text_password') . "\n\n";
					$message .= $password;
					$message .= "\n\n";
					$message .= $blog_info['title'];
		
					$mail = new AMail( $this->config );
					$mail->setTo($email);
					$mail->setFrom($blog_info['owner_email']);
					$mail->setSender($blog_info['title']);
					$mail->setSubject($subject);
					$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
					$mail->send();
					
					$this->model_blog_blog->editUserPassword($blog_user_id, $password);
					
					$this->session->data['success'] = $this->language->get('text_password_success');
					$this->redirect($this->blog_html->getSecureURL('blog/account/forgot'));				
				}else{
					$this->session->data['warning'] = $this->language->get('text_credentials_not_found');
				}
			}
		}
		
		$this->view->assign('heading_title', $blog_info['title']. ' - ' . $this->language->get('heading_reset_password'));
		
		$this->document->resetBreadcrumbs();

   		$this->document->addBreadcrumb( array ( 
      		'href'      => $this->html->getURL('index/home'),
       		'text'      => $this->language->get('text_store'),
       		'separator' => FALSE
   		));

		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getSecureURL('blog/account/settings'),
			'text'      => $this->language->get('heading_reset_password'),
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->data['action'] = $this->blog_html->getSecureURL('blog/account/forgot');
		
		$form = new AForm;
		$form->setForm(array(
			'form_name' => 'blogRPFrm',
			'update' => '',
		));

		$this->data['form']['id'] = 'blogRPFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
				'type' => 'form',
				'name' => 'blogRPFrm',
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
	
		$this->data['form']['fields']['user_name'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'user_name',
				'value' => $this->request->post['user_name'] ? $this->request->post['user_name'] : '',
		));
		$this->data['form']['fields']['email'] = $form->getFieldHtml(array(
				'type' => 'input',
				'name' => 'email',
				'value' => $this->request->post['email'] ? $this->request->post['email'] : '',
		));
		
		$this->data['error_user_name'] = $this->error['user_name'];
		$this->data['error_email'] = $this->error['email'];
		
		$this->view->batchAssign($this->data);
        $this->processTemplate('pages/blog/account_forgot.tpl');

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);	
		
	}
	
	public function login() {
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
	
		if ($this->session->data['blog_user_logged']) {
			$this->redirect($this->blog_html->getSecureURL('blog/account/settings'));
		}

		$this->loadLanguage('blog/account');
		$this->loadLanguage('blog/blog');
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));	
		$this->view->assign('heading_title', $this->language->get('text_heading_login'));
		$this->document->setTitle($this->language->get('text_heading_login'));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		
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
		$this->loadModel('blog/blog');
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$this->data['blog_info'] = $blog_info;
		
		$this->document->resetBreadcrumbs();

   		$this->document->addBreadcrumb( array ( 
      		'href'      => $this->html->getURL('index/home'),
       		'text'      => $this->language->get('text_store'),
       		'separator' => FALSE
   		));

		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME('&encode'),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getSecureURL('blog/account/login'),
			'text'      => $this->language->get('text_heading_login'),
			'separator' => $this->language->get('text_separator')
		 ));
		 
		$this->data['blog_home'] = $this->blog_html->getBLOGHOME();

		if($blog_info['blog_access'] == "restrict") {

			if($blog_info['login_data'] == 'customer') {
				$this->data['forgotten_pass'] = $this->html->getSecureURL('account/forgotten/password');
				$this->data['register'] = $this->html->getSecureURL('account/login');
				$this->data['source'] = 'customer';	
			}else{
				$this->data['forgotten_pass'] = $this->blog_html->getSecureURL('blog/account/forgot');
				$this->data['register'] = $this->blog_html->getSecureURL('blog/account');
				$this->data['source'] = 'self';	
			}

			$form = new AForm();
			$form->setForm(array( 'form_name' => 'pageloginFrm' ));
			$this->data['form']['id'] = 'pageloginFrm';
			$this->data['form'][ 'form_open' ] = $form->getFieldHtml(array(
				   'type' => 'form',
				   'name' => 'pageloginFrm',
				   'action' => $this->blog_html->getSecureURL('blog/account/login')
				   ));	
																			   											   
			$this->data['form']['fields']['loginname'] = $form->getFieldHtml( array(
				   'type' => 'input',
				   'name' => 'loginname',
				   ));
			$this->data['form']['fields']['password'] = $form->getFieldHtml( array(
				   'type' => 'password',
				   'name' => 'password',
				   ));
			$this->data['form']['fields']['source'] = $form->getFieldHtml( array(
				   'type' => 'hidden',
				   'name' => 'source',
				   'value' => $this->data['source']
				   ));
			
			$this->data['login_submit'] = HtmlElementFactory::create( array (
							   'type' => 'button',
							   'text' => $this->language->get('button_login'),
							   'href' => '#',
							   'attr'=> 'go_page_login',
							   'style' => 'button'));
							   
			$this->data['login_url'] = $this->blog_html->getURL('blog/proc_login');
			
			$this->view->batchAssign($this->data);
			$this->processTemplate('pages/blog/account_login.tpl');
		}else{
			$this->redirect($this->blog_html->getBLOGHOME());
		}

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}
	
	
	public function logout() {
		///init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		unset($this->session->data['blog_user_logged']);
		unset($this->session->data['blog_user_id']);
		unset($this->session->data['blog_user_name']);
		unset($this->session->data['blog_role']);
		unset($this->session->data['blog_role_id']);
		unset($this->session->data['blog_first_name']);
		unset($this->session->data['customer_id']);

		$this->redirect($this->blog_html->getBLOGHOME());
		
		//init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
	
	private function loginUser($user_name, $password) {
		$this->loadModel('blog/blog');

		$cust_source_data = $this->model_blog_blog->getUserData($user_name);

		if($user_name == $cust_source_data['username'] && $password == $cust_source_data['password']) {
			$this->session->data['blog_user_logged'] = 'true';
			$this->session->data['blog_user_id'] = $cust_source_data['blog_user_id'];
			$this->session->data['blog_user_name'] = $cust_source_data['username'];
			$this->session->data['blog_role'] = $cust_source_data['role'];
			$this->session->data['blog_first_name'] = $cust_source_data['firstname'];

			//set cookie for unauthenticated user (expire in 1 year) 
			$encryption = new AEncryption($this->config->get('encryption_key'));
			$user_data = $encryption->encrypt(serialize(array(
													'blog_first_name' => $this->session->data['blog_first_name'], 
													'blog_user_id' => $this->session->data['blog_user_id'], 
													'script_name'	=> $this->request->server['SCRIPT_NAME']
													)));
			setcookie('blog_user', $user_data, time() + 60 * 60 * 24 * 365, '/', $this->request->server['HTTP_HOST']);
			
			return true;
			
		}
		return false;
	}
	
	public function success() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog/account');
		
		$this->document->resetBreadcrumbs();
		
		$this->loadModel('blog/blog');  
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$this->data['blog_info'] = $blog_info;
		
		$this->document->addStyle(array(
			'href' => $this->view->templateResource('/stylesheet/blog.css'),
			'rel' => 'stylesheet'
		));
		$this->document->addScriptBottom($this->view->templateResource('/javascript/blog.js'));
		$this->document->addBreadcrumb(array(
				'href' => $this->html->getURL('index/home'),
				'text' => $this->language->get('text_store'),
				'separator' => FALSE
		));
		$this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getBLOGHOME(),
			'text'      => $blog_info['title'],
			'separator' => $this->language->get('text_separator')
		 ));
		 
		 $this->document->addBreadcrumb( array ( 
			'href'      => $this->blog_html->getSecureURL('blog/account'),
			'text'      => $this->language->get('text_heading'),
			'separator' => $this->language->get('text_separator')
		 ));

		
		if($this->request->is_GET() && has_value($this->request->get['activation']) ){
			if( $this->session->data['blog_activation'] ){
				$blog_user_id = (int)$this->session->data['blog_activation']['blog_user_id'];

				//if activation code presents in session
				if($this->request->get['activation'] == $this->session->data['blog_activation']['code']){
					$blog_user_info = $this->model_blog_blog->getBlogUser($blog_user_id);

					// if account exists
					if($blog_user_info){
						if(!$blog_user_info['approve'] ){ 
							$this->model_blog_blog->editUserApproval($blog_user_id, 1);
							$this->document->setTitle($this->language->get('heading_title_activated'));
							$this->view->assign('heading_title', $this->language->get('heading_title_activated'));
							$this->view->assign('text_message', $this->language->get('text_success_activated'));
						}else{
							$this->document->setTitle($this->language->get('heading_title_already_activated'));
							$this->view->assign('heading_title', $this->language->get('heading_title_already_activated'));
							$this->view->assign('text_message', $this->language->get('text_already_activated'));
						}
					}
				}else{
					if($this->request->get['email']){
						$this->document->setTitle($this->language->get('heading_title_account_not_found'));
						$this->view->assign('heading_title', $this->language->get('heading_title_account_not_found'));
						$this->view->assign('text_message', sprintf( $this->language->get('text_resend_activation_email'), "\n".$this->blog_html->getSecureURL('blog/account/sendcode','&email='.$this->request->get['email'])));
					}
				}
			}elseif(has_value($this->request->get['email'])){ 
				if($this->request->get['email']){
					$this->document->setTitle($this->language->get('heading_title_account_not_found'));
					$this->view->assign('heading_title', $this->language->get('heading_title_account_not_found'));
					$this->view->assign('text_message', sprintf( $this->language->get('text_resend_activation_email'), "\n".$this->blog_html->getSecureURL('blog/account/sendcode','&email='.$this->request->get['email'])));
				}
			}
		}else{

			if ($blog_info['user_email_activation']) {
				$this->document->setTitle($this->language->get('heading_title_activate'));
				$this->view->assign('heading_title', $this->language->get('heading_title_activate'));
				$this->view->assign('text_message', $this->language->get('text_message_activate'));
			}elseif ($blog_info['approve_user']) {
				$this->document->setTitle($this->language->get('heading_title'));
				$this->view->assign('heading_title', $this->language->get('heading_title_approval'));
				$this->view->assign('text_message', $this->language->get('text_message_approval'));
			} else {
				$this->document->setTitle($this->language->get('heading_title_created'));
				$this->view->assign('heading_title', $this->language->get('heading_title_created'));
				$this->view->assign('text_message', $this->language->get('text_message_success'));
			}
		}

		$this->view->assign('button_continue', $this->language->get('button_continue'));

	
		$this->view->assign('continue', $this->blog_html->getBLOGHOME());
		

		$continue = HtmlElementFactory::create(array('type' => 'button',
				'name' => 'continue_button',
				'text' => $this->language->get('button_continue'),
				'style' => 'button'));
		$this->view->assign('continue_button', $continue);
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/blog/blog_success.tpl');

		//init controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

		
	}

	public function sendCode() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadModel('blog/blog');  
		
		$this->loadLanguage('blog/account');
		
		$blog_info = $this->model_blog_blog->getBlogSettings();
		
		$blog_user_info = $this->model_blog_blog->getUserDataFromEmail($this->request->get['email']);
		
		//if can not find
		if(!$blog_user_info){
			$this->redirect($this->blog_html->getSecureURL('blog/account'));
		}
		$blog_user_id = $blog_user_info['blog_user_id'];
		$email = $blog_user_info['email'];

		$this->loadLanguage('blog/blog');
		$subject = sprintf($this->language->get('text_subject'), $blog_info['title']);
		$message = sprintf($this->language->get('text_welcome'), $blog_info['title']) . "\n\n";

		
		$code = md5(mt_rand(1,3000));
						
		$this->session->data['blog_activation'] = array(
													'blog_user_id' => $blog_user_id,
													'code' => $code,
													'email' => $email);

		$message .= sprintf($this->language->get('text_activate'), "\n".$this->blog_html->getSecureURL('blog/account/success', '&activation='.$code.'&email='.$email) ) . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";

		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $blog_info['title'];

		$mail = new AMail($this->config);
		$mail->setTo($email);
		$mail->setFrom($blog_info['owner_email']);
		$mail->setSender($blog_info['title']);
		$mail->setSubject($subject);
		$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		$mail->send();

		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		$this->redirect($this->blog_html->getSecureURL('blog/account/success'));
	}
	
	private function _validateForm() {																
	
		$username_pattern = '/^[\w._-]+$/i';
		if ( mb_strlen($this->request->post['user_name']) < 6 || mb_strlen($this->request->post['user_name']) > 64 || !preg_match($username_pattern, $this->request->post['user_name'])) {
			$this->error['user_name'] = $this->language->get('text_error_user_name');
		}else if (!$this->model_blog_blog->isUniqueBlogUser($this->request->post['user_name']) ) {
			$this->error['user_name'] = $this->language->get('text_error_user_name_notunique');
		}
		if ((mb_strlen($this->request->post['password']) < 4) || (mb_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->language->get('text_error_password');
		}
		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('text_error_password_match');
		}

    	if ((mb_strlen($this->request->post['email']) > 96) || (!preg_match(EMAIL_REGEX_PATTERN, $this->request->post['email']))) {
      		$this->error['email'] = $this->language->get('text_error_email');
    	}elseif (!$this->model_blog_blog->getBlogUserByEmail($this->request->post['email'])) {
      		$this->error['warning'] = $this->language->get('text_error_email_exists');
    	}
		
		//If captcha enabled, validate
		if($this->config->get('config_account_create_captcha')) {
			if($this->config->get('config_recaptcha_secret_key')) {
				require_once DIR_VENDORS . '/google_recaptcha/autoload.php';
				$recaptcha = new \ReCaptcha\ReCaptcha($this->config->get('config_recaptcha_secret_key'));
				$resp = $recaptcha->verify(	$this->request->post['g-recaptcha-response'],
											$this->request->server['REMOTE_ADDR']);
				if (!$resp->isSuccess() && $resp->getErrorCodes()) {
					$this->error['captcha'] = $this->language->get('error_captcha');
				}
			} else {
				if (!isset($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
					$this->error['captcha'] = $this->language->get('error_captcha');
				}
			}
		}
		
		if($this->request->post['site_url']) {
			$site_url = html_entity_decode($this->request->post['site_url']);
			$site_url = filter_var(trim($site_url), FILTER_SANITIZE_URL);
			if(!filter_var(trim($site_url), FILTER_VALIDATE_URL)) {		
				$this->error['site_url'] = $this->language->get('text_error_site_url');
			}	
		}
		if (!$this->error) {
			return true;
		}else{
			$this->error['warning'] = $this->language->get('error_errors');
			return false;
		}
	}
	
	private function _validateSettingsForm() {	
		
		if($this->request->post['email']) {
			if ((mb_strlen($this->request->post['email']) > 96) || (!preg_match(EMAIL_REGEX_PATTERN, $this->request->post['email']))) {
				$this->error['email'] = $this->language->get('text_error_email');
			}
		}
		
		if($this->request->post['site_url']) {
			$site_url = html_entity_decode($this->request->post['site_url']);
			$site_url = filter_var(trim($site_url), FILTER_SANITIZE_URL);
			if(!filter_var(trim($site_url), FILTER_VALIDATE_URL)) {		
				$this->error['site_url'] = $this->language->get('text_error_site_url');
			}	
		}

		if (!$this->error) {
			return true;
		}else{
			$this->error['warning'] = $this->language->get('error_errors');
			return false;
		}
	
	}
	
	private function _validatePWForm() {
		$error = array();
		
		if ((mb_strlen($this->request->post['old_password']) < 6) || (mb_strlen($this->request->post['old_password']) > 20)) {
			$error['old_password'] = $this->language->get('text_error_password');
		}elseif(!$this->model_blog_blog->verifyUserPassword($this->request->post['blog_user_id'], $this->request->post['old_password'])) {
			$error['old_password'] = $this->language->get('text_error_bad_password');
		}
	
		if ((mb_strlen($this->request->post['new_password']) < 6) || (mb_strlen($this->request->post['new_password']) > 20)) {
			$error['new_password'] = $this->language->get('text_error_password');
		}
		if ($this->request->post['confirm'] != $this->request->post['new_password']) {
			$error['confirm'] = $this->language->get('text_error_password_match');
		}
		
		$this->error = $error;
		if ($this->error) {
			return $this->error;
		}else{
			return true;
		}
		
	}
	
	private function cleanElements($html){
  
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