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

class ControllerPagesBlogSuccess extends AController {
	public function main() {

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
						$this->view->assign('text_message', sprintf( $this->language->get('text_resend_activation_email'), "\n".$this->blog_html->getSecureURL('blog/success/sendcode','&email='.$this->request->get['email'])));
					}
				}
			}elseif(has_value($this->request->get['email'])){ 
				if($this->request->get['email']){
					$this->document->setTitle($this->language->get('heading_title_account_not_found'));
					$this->view->assign('heading_title', $this->language->get('heading_title_account_not_found'));
					$this->view->assign('text_message', sprintf( $this->language->get('text_resend_activation_email'), "\n".$this->blog_html->getSecureURL('blog/success/sendcode','&email='.$this->request->get['email'])));
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

		$message .= sprintf($this->language->get('text_activate'), "\n".$this->blog_html->getSecureURL('blog/success', '&activation='.$code.'&email='.$email) ) . "\n\n";
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
		$this->redirect($this->blog_html->getSecureURL('blog/success'));
	}
}
