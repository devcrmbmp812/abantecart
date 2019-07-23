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
class ControllerBlocksBlogLogin extends AController {
	private $error = array();
	public $data = array();
	
	public function main() {
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
			
		$this->loadModel('blog/blog');
		$blog_info = $this->model_blog_blog->getBlogSettings();
			
		if($blog_info['blog_access'] == "restrict") {

		
			$this->view->assign('block_framed',true);
			$this->view->assign('heading_title', $this->language->get('text_login_title'));
			if($blog_info['login_data'] == 'customer') {
				if ($this->customer->isLogged()) {
					$this->data['customer_logged'] = $this->customer->isLogged();
					$customer_id = $this->customer->getId();
					$user_data = $this->model_blog_blog->getCustBlogUserData($customer_id);
					$this->data['customer_name'] = $this->customer->getFirstName();
					$this->data['settings_link'] = $this->blog_html->getSecureURL('account/blog_settings');
					$this->data['notification_link'] = $this->blog_html->getSecureURL('account/blog_settings').'#notifications'; 
					if($blog_info['use_store_url'] == '1') {
						$this->data['logout_link'] = $this->blog_html->getSecureURL('blog/account/logout');
					}
					$this->view->batchAssign($this->data);
					$this->processTemplate('blocks/blog_login.tpl');
				}elseif(isset($this->session->data['blog_user_logged'])) {
					$this->data['customer_logged'] = 'true'; 
					$this->data['customer_name'] = $this->session->data['blog_first_name'];
					$this->data['settings_link'] = $this->blog_html->getSecureURL('blog/account/settings');
					$this->data['notification_link'] = $this->blog_html->getSecureURL('blog/account/settings').'#notifications';
					$this->data['logout_link'] = $this->blog_html->getSecureURL('blog/account/logout');
					$this->view->batchAssign($this->data);
					$this->processTemplate('blocks/blog_login.tpl');
				}else{
					$this->data['forgotten_pass'] = $this->html->getSecureURL('account/forgotten/password');
					$this->data['register'] = $this->html->getSecureURL('account/login');
					$this->data['source'] = 'customer';	
				}
			}else{
				if (isset($this->session->data['blog_user_logged'])) {
					$this->data['customer_logged'] = 'true';
					$this->data['customer_name'] = $this->session->data['blog_first_name'];
					$this->data['settings_link'] = $this->blog_html->getSecureURL('blog/account/settings');
					$this->data['notification_link'] = $this->blog_html->getSecureURL('blog/account/settings').'#notifications';
					$this->data['logout_link'] = $this->blog_html->getSecureURL('blog/account/logout');
					$this->view->batchAssign($this->data);
					$this->processTemplate('blocks/blog_login.tpl');
				}else{
					$this->data['forgotten_pass'] = $this->blog_html->getSecureURL('blog/account/forgot');
					$this->data['register'] = $this->blog_html->getSecureURL('blog/account');
					$this->data['source'] = 'self';	
				}
			}

			$form = new AForm();
			$form->setForm(array( 'form_name' => 'loginFrm' ));
			$this->data['form']['id'] = 'loginFrm';
			$this->data['form'][ 'form_open' ] = $form->getFieldHtml(array(
				   'type' => 'form',
				   'name' => 'loginFrm',
				   'action' => ''
				   ));													   
			$this->data['form']['loginname'] = $form->getFieldHtml( array(
				   'type' => 'input',
				   'name' => 'loginname',
				   'placeholder' => $this->language->get('text_username'),
				   ));
			$this->data['form']['password'] = $form->getFieldHtml( array(
				   'type' => 'password',
				   'name' => 'password',
				   'placeholder' => $this->language->get('text_password'),
				   ));
			$this->data['form']['source'] = $form->getFieldHtml( array(
				   'type' => 'hidden',
				   'name' => 'source',
				   'value' => $this->data['source']
				   ));
																		   
			$this->data['login_submit'] = HtmlElementFactory::create( array (
							   'type' => 'button',
							   'text' => $this->language->get('button_login'),
							   'href' => '#',
							   'attr'=> 'go_login',
							   'style' => 'button'));
							   
			$this->data['login_url'] = $this->blog_html->getURL('blog/proc_login');
			
			

			$this->view->batchAssign($this->data);
			$this->processTemplate('blocks/blog_login.tpl');
		}

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}
	
	

}
?>