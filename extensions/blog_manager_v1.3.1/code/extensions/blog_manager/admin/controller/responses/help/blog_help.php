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

class ControllerResponsesHelpBlogHelp extends AController {
	public $data = array();
	
	public function main() {
		
		if(!defined('IS_ADMIN') || !IS_ADMIN) {
			return false;
		}

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		
		$this->loadLanguage('blog_manager/blog_help');
		$page = $this->request->get['page'];
		$this->data['title'] = $this->language->get('text_help');
		
		$this->data['help_file_path'] = DIR_EXT . 'blog_manager/admin/view/default/template/responses/help/' . $page . '_help.html';

		if ( file_exists($this->data['help_file_path']) && is_file($this->data['help_file_path']) ) {
			$this->data['content'] = file_get_contents($this->data['help_file_path']);
		} else {
			$this->data['content'] = $this->language->get('error_no_help_file');
		}
	
		$this->view->batchAssign($this->data);
		$this->response->setOutput($this->view->fetch('responses/help/blog_help.tpl'));

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}
	
}


?>