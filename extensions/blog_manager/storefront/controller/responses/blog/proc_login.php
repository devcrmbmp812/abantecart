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
class ControllerResponsesBlogProcLogin extends AController {
	public $error = array();
	public $data = array();
	
	public function main() {
		
		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$json = array();
		
		$this->loadModel('blog/blog');
		$this->loadLanguage('blocks/blog_login');

		//get data source
		$user_source_data = $this->model_blog_blog->getUserData($this->request->post['loginname']);
		//if nothing, and using customer data, then check cusotmer table and copy to blog_user table -> returns user data
		if(empty($user_source_data) && $this->model_blog_blog->getblog_config('login_data') == 'customer') { 
			$user_source_data = $this->model_blog_blog->getCustomerData($this->request->post['loginname'], $this->request->post['password']);
		}
		if($user_source_data) { //if user data
			if($user_source_data['source'] == 'customer') {
				if (!$this->customer->login($this->request->post['loginname'], $this->request->post['password'])) {
					$json['error'] = $this->language->get('text_error_login');
				}else{
					$this->loadModel('account/address');
					$address_id = $this->customer->getAddressId();
					$address = $this->model_account_address->getAddress($address_id);
					$this->tax->setZone($address['country_id'], $address['zone_id']);	
					$this->session->data['country_id'] = $address['country_id'];
					$this->session->data['zone_id'] = $address['zone_id'];
					$this->session->data['customer_id'] = $user_source_data['customer_id'];
					$customer_data = $this->model_blog_blog->getCustBlogUserData($user_source_data['customer_id']);
					$this->session->data['blog_user_logged'] = 'true';
					$this->session->data['blog_role'] = $customer_data['role'];
					$this->session->data['blog_role_id'] = $customer_data['role_id'];
					$this->session->data['blog_user_id'] = $customer_data['blog_user_id'];
					$this->session->data['blog_first_name'] = $customer_data['firstname'];
					if($this->model_blog_blog->getblog_config('autofill_form')) {
						$json['blog_user_name'] = $customer_data['username'];
						$json['email'] = $customer_data['email'];
						$json['site_url'] = $customer_data['site_url'];
					}
					$json['message'] = 'success';
					

					$encryption = new AEncryption($this->config->get('encryption_key'));
					$cutomer_data = $encryption->encrypt(serialize(array(
																'first_name' => $user_source_data['firstname'], 
																'customer_id' => $user_source_data['customer_id'], 
																'script_name'	=> $this->request->server['SCRIPT_NAME']
																)));
					setcookie('customer', $cutomer_data, time() + 60 * 60 * 24 * 365, '/', str_replace(array('http://', 'https://', '/'),'', $this->config->get('config_url')));
					setcookie('customer', $cutomer_data, time() + 60 * 60 * 24 * 365, '/', str_replace(array('http://', 'https://', '/'),'', $this->config->get('blog_url')));
					$user_data = $encryption->encrypt(serialize(array(
															'blog_first_name' => $this->session->data['blog_first_name'], 
															'blog_user_id' => $this->session->data['blog_user_id'], 
															'script_name'	=> $this->request->server['SCRIPT_NAME']
															)));
					setcookie('blog_user', $user_data, time() + 60 * 60 * 24 * 365, '/', $this->request->server['HTTP_HOST']);
					
				}
			}else{
				//verify login
				if(!$this->model_blog_blog->verifyUser($this->request->post['loginname'], $this->request->post['password'])) {
					$json['error'] = $this->language->get('text_error_login');
				}else{
					$this->session->data['blog_user_logged'] = 'true';
					$this->session->data['blog_user_id'] = $user_source_data['blog_user_id'];
					$this->session->data['blog_user_name'] = $user_source_data['username'];
					$this->session->data['blog_role'] = $user_source_data['role'];
					$this->session->data['blog_role_id'] = $user_source_data['role_id'];
					$this->session->data['blog_first_name'] = $user_source_data['firstname'];
					if($this->model_blog_blog->getblog_config('autofill_form')) {
						$json['blog_user_name'] = $user_source_data['username'];
						$json['email'] = $user_source_data['email'];
						$json['site_url'] = $user_source_data['site_url'];
					}
					$json['message'] = 'success';

					$encryption = new AEncryption($this->config->get('encryption_key'));
					$user_data = $encryption->encrypt(serialize(array(
															'blog_first_name' => $this->session->data['blog_first_name'], 
															'blog_user_id' => $this->session->data['blog_user_id'], 
															'script_name'	=> $this->request->server['SCRIPT_NAME']
															)));
					setcookie('blog_user', $user_data, time() + 60 * 60 * 24 * 365, '/', $this->request->server['HTTP_HOST']);
				}
			}
		}else{ //no login data
			$json['error'] = $this->language->get('text_error_login');
		}
	
		
		
		//init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
		
		$this->load->library('json');
		$this->response->setOutput(AJson::encode($json));
		
		
		
	}
	
	
	

	
	
	
}

?>