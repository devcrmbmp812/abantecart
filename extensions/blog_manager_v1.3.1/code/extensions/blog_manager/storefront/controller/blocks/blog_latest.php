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
class ControllerBlocksBlogLatest extends AController {
	public $data = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->loadModel('blog/blog');
		$this->view->assign('heading_title', $this->language->get('blog_latest_title') );
		
		$blog_status = $this->config->get('blog_manager_status');
		$status = $this->model_blog_blog->getblog_config('latest_block_status');
		$min = $this->model_blog_blog->getblog_config('latest_block_min');
		$this->data['limit'] = $this->model_blog_blog->getblog_config('latest_block_limit');
		$this->data['max'] = $this->model_blog_blog->getblog_config('latest_block_max');
		$this->view->assign('more', $this->language->get('text_more'));
		$this->view->assign('less', $this->language->get('text_less'));
		
		if($blog_status && $status) {
			$latest = $this->model_blog_blog->getLatestActivity($this->data['max']);
			$this->data['latest_count'] = count($latest);
			if($this->data['latest_count'] > $min) {
				$latest_links = array();
				foreach ($latest as $row) {
					if($row['type'] == 'comment') {
						$link = $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode'). '#comment-' . $row['blog_comment_id'];
						$text = $this->language->get('text_new_comment');
					}elseif($row['type'] == 'reply'){
						$link = $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode'). '#comment-' . $row['blog_comment_id'];
						$text = $this->language->get('text_new_reply');
					}else{ // = post
						$link = $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' . $row['blog_entry_id'], '&encode');
						$text = $this->language->get('text_new_entry');
					}
					
					$latest_links[] = array(
						'title' => $row['entry_title'],
						'href' => $link,
						'text' => $text,
						'date_modified' => dateISO2Display($row['date_modified'], $this->language->get('date_format_short')),
					);
				}
				$this->data['latest_links'] = $latest_links;
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
