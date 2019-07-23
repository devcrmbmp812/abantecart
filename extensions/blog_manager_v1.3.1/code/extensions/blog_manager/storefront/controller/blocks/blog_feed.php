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
class ControllerBlocksBlogFeed extends AController {
	public $data = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadModel('blog/blog');
		$this->view->assign('heading_title', $this->language->get('blog_feed_title'));
		
		$this->data['default_feed_href'] = $this->blog_html->getBlogFeedURL();
		$default_feed = $this->model_blog_blog->getblog_config('feed_type');
		if ($default_feed == 'atom') {
			$this->data['feed_image'] = '<img src="' . $this->view->templateResource('/image/rss.png') . '" alt="' . $this->language->get('text_subscribe') . '" title="' . $this->language->get('text_atom') . '" width="32" height="32" />';
		}else{
			$this->data['feed_image'] = '<img src="' . $this->view->templateResource('/image/rss.png') . '" alt="' . $this->language->get('text_subscribe') . '" title="' . $this->language->get('text_rss2') . '" width="32" height="32" />';
		}
		$this->data['rss_feed_href'] = $this->blog_html->getBlogFeedURL('rss2');
		$this->data['rss_feed'] = $this->language->get('text_rss2');
		$this->data['atom_feed_href'] = $this->blog_html->getBlogFeedURL('atom');
		$this->data['atom_feed'] = $this->language->get('text_atom');
		$this->data['subscribe'] = $this->language->get('text_subscribe');
		
		
		
		//$this->view->assign('block_framed',true);
		
		$this->view->batchAssign($this->data);
		$this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}	

}
