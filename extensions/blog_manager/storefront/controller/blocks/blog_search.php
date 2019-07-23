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
class ControllerBlocksBlogSearch extends AController {
	public $data=array();
	public function main() {
		//init controller data
		$this->extensions->hk_InitData($this,__FUNCTION__);
		$this->loadLanguage('blocks/blog_search');

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['entry_search'] = $this->language->get('entry_search');
		$this->data['search'] = $this->html->buildElement(
												array ('type'=>'input',
					                                    'name'=>'blog_keyword',
					                                    'value'=> (isset($this->request->get['keyword']) ? $this->request->get['keyword'] : $this->language->get('text_blog_search')),
														'placeholder' => $this->language->get('text_blog_search')

												));
		$this->data['button_go'] = $this->language->get('button_go');
		$this->data['search_url'] = $this->blog_html->getSecureURL('blog/blog/search');

		$this->view->batchAssign($this->data);
		$this->processTemplate();
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
}
