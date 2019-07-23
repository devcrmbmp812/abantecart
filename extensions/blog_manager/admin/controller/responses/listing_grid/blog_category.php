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
class ControllerResponsesListingGridBlogCategory extends AController {

    public function main() {

	    //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->loadLanguage('blog_manager/blog_category');
		$this->loadModel('design/blog_category');
		$this->loadModel('design/blog_manager');
        $this->loadModel('tool/image');

		//Prepare filter config
		$grid_filter_params = array('name', 'bd.title', 'sort_order', 'status');
	    $filter_grid = new AFilter(array( 'method' => 'post', 'grid_filter_params' => $grid_filter_params ));

	    $filter_data = $filter_grid->getFilterData();
	    //Add custom params
	    $filter_data['parent_id'] = ( isset( $this->request->get['parent_id'] ) ? $this->request->get['parent_id'] : 0 );
	    $new_level = 0;
		//get all leave categories 
		$leafnodes = $this->model_design_blog_category->getBlogLeafCategories();
	    if ($this->request->post['nodeid'] ) {
	    	$sort = $filter_data['sort'];
	    	$order = $filter_data['order'];
	    	//reset filter to get only parent category
	    	$filter_data = array();
	    	$filter_data['sort'] = $sort;
	    	$filter_data['order'] = $order;
	    	$filter_data['parent_id'] = (integer)$this->request->post['nodeid'];
			$new_level = (integer)$this->request->post["n_level"] + 1;
	    }
	    
	    $total = $this->model_design_blog_category->getTotalBlogCategories($filter_data);
	    $response = new stdClass();
		$response->page = $filter_grid->getParam('page');
		$response->total = $filter_grid->calcTotalPages( $total );
		$response->records = $total;
	    $response->userdata = new stdClass();
	    $results = $this->model_design_blog_category->getBlogCategoriesData($filter_data);

	    $i = 0;

	    $resource = new AResource('image');
	    foreach ($results as $result) {
		    $thumbnail = $resource->getMainThumb('blog_category',
			                                     $result['blog_category_id'],
			                                     (int)$this->config->get('config_image_grid_width'),
			                                     (int)$this->config->get('config_image_grid_height'),false);
			$thumbnail = $thumbnail['thumb_html'];

            $response->rows[$i]['id'] = $result['blog_category_id'];
            $cnt = $this->model_design_blog_category->getBlogCategoriesData(array('parent_id'=>$result['blog_category_id']),'total_only');

			if(!$result['article_count']){
				$article_count = 0;
			}else{
				$article_count = (string)$this->html->buildElement(array(
																'type' => 'button',
																'name' => 'view_articles',
																'text' => $result['article_count'],
																'href'=> $this->html->getSecureURL('design/blog_entry','&blog_category='.$result['blog_category_id']),
																'title' => $this->language->get('text_view').' '.$this->language->get('tab_entries')
															));
			}

            //tree grid structure
            if ( $this->config->get('config_show_tree_data') ) {
            	$name_lable = '<label style="white-space: nowrap;">'.$result['basename'].'</label>';
            } else {
            	$name_lable = '<label style="white-space: nowrap;">'.(str_replace($result['basename'],'',$result['name'])).'</label>'
			     .$this->html->buildInput(array(
                    'name'  => 'blog_category_description['.$result['blog_category_id'].']['.$this->session->data['content_language_id'].'][name]',
                    'value' => $result['basename'],
				    'attr' => ' maxlength="32" '
                ));
            }

			$response->rows[$i]['cell'] = array(
				$result['blog_category_id'],
                $thumbnail,
				$result['name'], //$name_lable,
                $this->html->buildInput(array(
                    'name'  => 'sort_order['.$result['blog_category_id'].']',
                    'value' => $result['sort_order'],
                )),
				$this->html->buildCheckbox(array(
                    'name'  => 'status['.$result['blog_category_id'].']',
                    'value' => $result['status'],
                    'style'  => 'btn_switch',
                )),
				$article_count,
                $cnt
                .($cnt ?
                '&nbsp;<a class="btn_action btn_grid grid_action_expand" href="#" rel="parent_id='.$result['blog_category_id'].'" title="'. $this->language->get('text_view') . '">'.
				'<i class="fa fa-folder-open"></i></a>'
                  :''), 
                 'action',
                 $new_level,
                 ( $filter_data['parent_id'] ? $filter_data['parent_id'] : NULL ),
                 ( $result['blog_category_id'] == $leafnodes[$result['blog_category_id']] ? true : false ),
                 false              
			);
			$i++;
		}
		//update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

		$this->load->library('json');
		$this->response->setOutput(AJson::encode($response));
	}

	public function update() {

		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadModel('design/blog_manager');
	    $this->loadModel('design/blog_category');
		$this->loadLanguage('blog_manager/blog_category');
		if (!$this->user->canModify('listing_grid/blog_category')) {
			        $error = new AError('');
			        return $error->toJSONResponse('NO_PERMISSIONS_402',
			                                      array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_category'),
			                                             'reset_value' => true
			                                           ) );
		}

		switch ($this->request->post['oper']) {
			case 'del':
				$ids = explode(',', $this->request->post['id']);
				if ( !empty($ids) )
				foreach( $ids as $id ) {
					$this->model_designg_blog_category->deleteBlogCategory($id);
				}
				break;
			case 'save':
				$allowedFields = array('sort_order', 'status',);

				$ids = explode(',', $this->request->post['id']);
				if ( !empty($ids) ) {
					//resort required. 
					if(  $this->request->post['resort'] == 'yes' ) {
						//get only ids we need
						foreach($ids as $id){
							$array[$id] = $this->request->post['sort_order'][$id];
						}
						$new_sort = build_sort_order($ids, min($array), max($array), $this->request->post['sort_direction']);
	 					$this->request->post['sort_order'] = $new_sort;
					}
					foreach( $ids as $id ) {
						foreach ( $allowedFields as $field ) {
							$this->model_design_blog_category->editBlogCategory($id, array($field => $this->request->post[$field][$id]) );
						}
					}
				}
				break;
			default:

		}

		//update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

    /**
     * update only one field
     *
     * @return void
     */
    public function update_field() {

		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->loadLanguage('design/blog_category');
        if (!$this->user->canModify('listing_grid/blog_category')) {
	        $error = new AError('');
	        return $error->toJSONResponse('NO_PERMISSIONS_402',
	                                      array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_category'),
	                                             'reset_value' => true
	                                           ) );
		}

        $this->loadModel('design/blog_category');

	    if ( isset( $this->request->get['id'] ) ) {
		    //request sent from edit form. ID in url
		    foreach ($this->request->post as $field => $value ) {
				if($field=='keyword'){
					if($err = $this->html->isSEOkeywordExists('blog_category_id='.$this->request->get['id'], $value)){
						$error = new AError('');
						return $error->toJSONResponse('VALIDATION_ERROR_406', array( 'error_text' => $err ));
					}
				}

				$err = $this->_validateField($field, $value);
				if (!empty($err)) {
					$error = new AError('');
					return $error->toJSONResponse('VALIDATION_ERROR_406', array( 'error_text' => $err ));
				}

				$this->model_design_blog_category->editBlogCategory($this->request->get['id'], array($field => $value) );
			}
		    return null;
	    }
		$language_id = $this->language->getContentLanguageID();
	    //request sent from jGrid. ID is key of array
        foreach ($this->request->post as $field => $value ) {
            foreach ( $value as $k => $v ) {
				$this->model_design_blog_category->editBlogCategory($k, array($field => $v) );
            }
        }

		//update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}


	private function _validateField($field, $value) {

		$err = '';
		switch ($field) {
			case 'blog_category_description' :
				$language_id = $this->language->getContentLanguageID();

				if (isset($value[$language_id][ 'name' ]) && ( mb_strlen($value[$language_id][ 'name' ]) < 1 || mb_strlen($value[$language_id][ 'name' ]) > 255 )) {
					$err = $this->language->get('error_name');
				}
				break;
			case 'model' :
				if ( mb_strlen($value) > 64 ) {
					$err = $this->language->get('error_model');
				}
				break;
			case 'keyword' :
				$err = $this->html->isSEOkeywordExists('blog_category_id='.$this->request->get['id'], $value);
				break;
		}
		return $err;
	}


	public function blog_categories() {

		$output = array();
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadModel('design/blog_category');
		if (isset($this->request->post['term'])) {
			$filter = array('limit' => 20,
							'language_id' => $this->language->getContentLanguageID(),
							'subsql_filter' => "bcd.name LIKE '%".$this->request->post['term']."%'
												OR bcd.description LIKE '%".$this->request->post['term']."%'
												OR bcd.meta_keywords LIKE '%".$this->request->post['term']."%'"
							);
			$results = $this->model_design_blog_category->getBlogCategoriesData($filter);

			$resource = new AResource('image');
			foreach ($results as $item) {
				$thumbnail = $resource->getMainThumb('blog_categories',
												$item['blog_category_id'],
												(int)$this->config->get('config_image_grid_width'),
												(int)$this->config->get('config_image_grid_height'),
												true);

				$output[ ] = array(
					'image' => $icon = $thumbnail['thumb_html'] ? $thumbnail['thumb_html'] : '<i class="fa fa-code fa-4x"></i>&nbsp;',
					'id' => $item['blog_category_id'],
					'name' => $item['name'],
					'meta' => '',
					'sort_order' => (int)$item['sort_order'],
				);
			}
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($output));
	}

}
