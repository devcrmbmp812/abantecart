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
class ControllerBlocksBlogActive extends AController {
	public $data = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->loadModel('blog/blog');
		$this->view->assign('heading_title', $this->language->get('blog_active_title') );
		
		$status = $this->model_blog_blog->getblog_config('active_block_status');
		$min = $this->model_blog_blog->getblog_config('active_block_min');
		$this->data['limit'] = $this->model_blog_blog->getblog_config('active_block_limit');
		$this->data['max'] = $this->model_blog_blog->getblog_config('active_block_max');
		$this->view->assign('more', $this->language->get('text_more'));
		$this->view->assign('less', $this->language->get('text_less'));
		
		if($status) {
			$active = $this->model_blog_blog->getActiveEntries($this->data['max']);
			$this->data['active_count'] = count($active);
			if($this->data['active_count'] >= $min) {
				$active_links = array();
				foreach ($active as $row) {
					$active_links[] = array(
						'title' => $row['entry_title'],
						'count' => $row['comments_count'],
						'href' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode')
					);
				}
				$this->data['active_links'] = $active_links;
				// framed needs to show frames for generic block.
				//If tpl used by listing block framed was set by listing block settings
				$this->view->assign('block_framed',true);
				
				$this->view->batchAssign($this->data);
				$this->processTemplate();
			}
		}
        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}	

}
