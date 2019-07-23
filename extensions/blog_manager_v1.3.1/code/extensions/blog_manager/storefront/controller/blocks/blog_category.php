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
class ControllerBlocksBlogCategory extends AController {
	public $data = array();
	protected $blog_category_id = 0;
	protected $bcat = array();
	protected $selected_root_id = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadModel('blog/blog');
		$this->view->assign('heading_title', $this->language->get('blog_category_title') );
		
		$status = $this->model_blog_blog->getblog_config('category_block_status');
		$min = $this->model_blog_blog->getblog_config('category_block_min');
		$this->data['limit'] = $this->model_blog_blog->getblog_config('category_block_limit');
		$this->data['max'] = $this->model_blog_blog->getblog_config('category_block_max');
		
		if (isset($this->request->get['bcat'])) {
			$this->bcat = explode('_', $this->request->get['bcat']);
			$this->blog_category_id = end($this->bcat);
		}
		$this->view->assign('selected_category_id', $this->blog_category_id);
		$this->view->assign('bcat', $this->request->get['bcat']);
		$this->view->assign('more', $this->language->get('text_more'));
		$this->view->assign('less', $this->language->get('text_less'));
		
		if($status) {
			
			$top_level_categories = $this->model_blog_blog->getBlogCategories(0,$this->data['max']);
			$this->data['cat_count'] = count($top_level_categories);
			
			if($this->data['cat_count'] >= $min) {
				//load main level categories
				$all_categories = $this->model_blog_blog->getAllBlogCategories();
				$this->view->assign('blog_category_list', $this->_buildCategoryTree($all_categories));
				
				// framed needs to show frames for generic block.
				//If tpl used by listing block framed was set by listing block settings
				$this->view->assign('block_framed',true);
		
				//Load nested categories and with all details based on whole categories list array in $this->data
				$this->data['resource_obj'] = new AResource('image');
				$this->view->assign('home_href', $this->blog_html->getBLOGSEOURL('blog/blog','&blog'));
				$this->view->assign('blog_categories', $this->_buildNestedCategoryList());
				$this->view->batchAssign($this->data);
				$this->processTemplate();
			}
		}
		
        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}

	/** Function builds one dimentional category tree based on given array
	 *
	 * @param array $all_categories
	 * @param int $parent_id
	 * @param string $bcat
	 * @return array
	 */

	private function _buildCategoryTree($all_categories = array(), $parent_id=0, $bcat=''){
	
		$output = array();
		foreach($all_categories as $category){
			if($parent_id!=$category['parent_id']){ continue; }
			$category['bcat'] = $bcat ? $bcat.'_'.$category['blog_category_id'] : $category['blog_category_id'];
			$category['parents'] = explode("_",$category['bcat']);
			$category['level'] = sizeof($category['parents'])-1; //digin' level
			if($category['blog_category_id']==$this->blog_category_id){ //mark root
				$this->selected_root_id = $category['parents'][0];
			}
			$output[] = $category;
			$output = array_merge($output,$this->_buildCategoryTree($all_categories,$category['blog_category_id'], $category['bcat']));
		}
		if($parent_id==0){
			$this->data['all_blog_categories'] = $output; //place result into memory for future usage (for menu. see below)
			// cut list and expand only selected tree branch
			$cutted_tree = array();
			foreach($output as $category){
				if($category['parent_id']!=0 && !in_array($this->selected_root_id,$category['parents'])){ continue; }
				$category['href'] = $this->blog_html->getBLOGSEOURL('blog/category', '&bcat='.$category['bcat'], '&encode');
				$cutted_tree[] = $category;
			}
			return $cutted_tree;
		}else{
			return $output;
		}
	}

	/** Function builds one multi-dimentional (nested) category tree for menu
	 *
	 * @param int $parent_id
	 * @return array
	 */
	private function _buildNestedCategoryList($parent_id=0){
		/**
		 * @var $resource AResource
		 */
		$this->loadModel('blog/blog');
		$blog_category_image_width = $this->model_blog_blog->getblog_config('blog_category_image_width');
		$blog_category_image_height = $this->model_blog_blog->getblog_config('blog_category_image_height');
		 
		$resource = $this->data['resource_obj'];
		$output = array();
		foreach($this->data['all_blog_categories'] as $category){
			if( $category['parent_id'] != $parent_id ){ continue; }
			$category['children'] = $this->_buildNestedCategoryList($category['blog_category_id']);
			$thumbnail = $resource->getMainThumb( 'blog_category',
													$category['blog_category_id'],
													(int)$blog_category_image_width,
													(int)$blog_category_image_height,
													false);
			$category['thumb'] = $thumbnail['thumb_url'];

			$category['href'] = $this->blog_html->getBLOGSEOURL('blog/category', '&bcat=' . $category['bcat'], '&encode');
			//mark current category
			if(in_array($category['blog_category_id'], $this->bcat)) {
				$category['current'] = true;
			}
			$output[] = $category;
		}
		return $output;
	}

}
