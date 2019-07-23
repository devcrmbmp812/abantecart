<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}
class ControllerPagesDesignBlogManagerTabs extends AController {

	public $data = array();
     
  	public function main() {

        //Load input argumets for gid settings
        $this->data = func_get_arg(0);
        if (!is_array($this->data)) {
            throw new AException (AC_ERR_LOAD, 'Error: Could not create grid. Grid definition is not array.');
        }
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadLanguage('blog_manager/blog_manager');

		$this->data['link_details'] = $this->html->getSecureURL('design/blog_manager');

		if(isset($this->data['proc']) && ($this->data['proc'] == 'exist')){
			$this->data['groups'] = array('users', 'settings', 'blocks', 'comments');
			
			$this->loadModel('design/blog_manager');

			foreach ($this->data['groups'] as $group) {
				$this->data['link_' . $group] = $this->html->getSecureURL('design/blog_manager/'. $group . '');
			}
			$this->view->batchAssign( $this->data );
			$this->processTemplate('pages/design/blog_manager_tabs.tpl');
		}
		

		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}
}

