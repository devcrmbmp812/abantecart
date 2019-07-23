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
class ControllerBlocksBlogArchive extends AController {
	public $data = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->loadModel('blog/blog');
		$this->view->assign('heading_title', $this->language->get('blog_archive_title') );
		
		$status = $this->model_blog_blog->getblog_config('archive_block_status');
		$min = $this->model_blog_blog->getblog_config('archive_block_min');
		$this->data['limit'] = $this->model_blog_blog->getblog_config('archive_block_limit');
		$this->data['max'] = $this->model_blog_blog->getblog_config('archive_block_max');
		$this->view->assign('more', $this->language->get('text_more'));
		$this->view->assign('less', $this->language->get('text_less'));
		
		if($status) {
			$archive_months = $this->model_blog_blog->getArchiveMonths();
			$this->data['archive_count'] = count($archive_months);
			if($this->data['archive_count'] >= $min) {
				$archive_links = array();
				foreach ($archive_months as $month) {
					$archive_links[] = array(
						'month' => $month['month'],
						'year' => $month['year'],
						'count' => ' (' . $month['count'] . ')',
						'href' => $this->blog_html->getBlogArchiveURL($month['month_num'],$month['year'], '', $encode),
					);
				}
				$this->data['archive_links'] = $archive_links;
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
