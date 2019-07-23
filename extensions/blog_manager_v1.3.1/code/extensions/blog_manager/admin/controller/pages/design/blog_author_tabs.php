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
class ControllerPagesDesignBlogAuthorTabs extends AController {

	public $data = array();
     
  	public function main() {

        //Load input argumets for gid settings
        $this->data = func_get_arg(0);
        if (!is_array($this->data)) {
            throw new AException (AC_ERR_LOAD, 'Error: Could not create grid. Grid definition is not array.');
        }
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadLanguage('blog_manager/blog_author');

		$this->data['groups'] = array('general' );

		foreach ($this->data['groups'] as $group) {
			$this->data['link_' . $group] = $this->html->getSecureURL('design/blog_author/'.($this->data['blog_author_id'] ? 'update' : 'insert'),
																	 ($this->data['blog_author_id'] ? '&blog_author_id='.$this->data['blog_author_id'] : '')). '#'.$group;
		}

		if($this->data['blog_author_id']){
			$this->data['groups'][] = 'layout';
			$this->data['link_layout'] = $this->html->getSecureURL('design/blog_author/edit_layout', '&blog_author_id='.$this->data['blog_author_id']);
		}

		$this->view->batchAssign( $this->data );
		$this->processTemplate('pages/design/blog_author_tabs.tpl');

		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
}

