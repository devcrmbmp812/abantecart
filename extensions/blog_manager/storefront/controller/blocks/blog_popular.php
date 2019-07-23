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
class ControllerBlocksBlogPopular extends AController {
	public $data = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->loadModel('blog/blog');
		$this->view->assign('heading_title', $this->language->get('blog_popular_title') );
		
		$status = $this->model_blog_blog->getblog_config('popular_block_status');
		$min = $this->model_blog_blog->getblog_config('popular_block_min');
		$this->data['limit'] = $this->model_blog_blog->getblog_config('popular_block_limit');
		$this->data['max'] = $this->model_blog_blog->getblog_config('popular_block_max');
		$this->view->assign('more', $this->language->get('text_more'));
		$this->view->assign('less', $this->language->get('text_less'));
		
		if($status) {
			$popular = $this->model_blog_blog->getPopularEntries($this->data['max']);
			$this->data['popular_count'] = count($popular);
			if($this->data['popular_count'] >= $min) {
				$popular_links = array();
				foreach ($popular as $row) {
					$popular_links[] = array(
						'title' => $row['entry_title'],
						'views' => $row['view'],
						'href' => $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode')
					);
				}
				$this->data['popular_links'] = $popular_links;
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
