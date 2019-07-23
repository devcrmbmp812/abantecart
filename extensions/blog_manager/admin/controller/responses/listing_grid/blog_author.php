<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE') || !IS_ADMIN) {
	header('Location: static_pages/');
}
class ControllerResponsesListingGridBlogAuthor extends AController {

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_author');
		$this->loadModel('design/blog_author');
		$this->loadModel('tool/image');

		//Prepare filter config
		$filter_params = array('blog_author' );
		$grid_filter_params = array('status');
		//Build advanced filter
		$filter_form = new AFilter(array( 'method' => 'get', 'filter_params' => $filter_params ));
		$filter_grid = new AFilter(array( 'method' => 'post', 'grid_filter_params' => $grid_filter_params ));
		$filter_array = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

		$total = $this->model_design_blog_author->getTotalAuthors($filter_array);
		$response = new stdClass();
		$response->page = $filter_grid->getParam('page');
		$response->total = $filter_grid->calcTotalPages($total);
		$response->records = $total;
        $response->userdata = (object)array('');
		$results = $this->model_design_blog_author->getAuthors($filter_array);
		$results = !$results ? array() : $results;
		$i = 0;
		$resource = new AResource('image');

		foreach ($results as $result) {									 
			$thumbnail = $resource->getMainThumb('blog_authors',
			                                     $result['blog_author_id'],
			                                     (int)$this->config->get('config_image_grid_width'),
			                                     (int)$this->config->get('config_image_grid_height'),true);
			$thumbnail = $thumbnail['thumb_html'];
			$thumbnail = '';									 
			if(!$result['entries_count']){
				$entries_count = 0;
			}else{
				$entries_count = (string)$this->html->buildElement(array(
																'type' => 'button',
																'name' => 'view_entries',
																'text' => $result['entries_count'],
																'href'=> $this->html->getSecureURL('design/blog_entry','&blog_author='.$result['blog_author_id']),
																'title' => $this->language->get('text_view').' '.$this->language->get('tab_entries')
															));
			}								 

			$response->rows[ $i ][ 'id' ] = $result[ 'blog_author_id' ];
			$response->rows[ $i ][ 'cell' ] = array(
				$result[ 'blog_author_id' ],
				$thumbnail,
				$result['name'],
				$result['role'],
				$entries_count,
				$this->html->buildCheckbox(array(
					'name' => 'status[' . $result['blog_author_id'] . ']',
					'value' => $result['status'],
					'style' => 'btn_switch',
				)),
				'action',
			);
			$i++;
		}


		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		$this->load->library('json');
		$this->response->setOutput(AJson::encode($response));
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadLanguage('blog_manager/blog_author');
		$this->loadModel('design/blog_author');

		if (!$this->user->canModify('listing_grid/blog_author')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_author'),
					'reset_value' => true
				));
		}

		switch ($this->request->post[ 'oper' ]) {
			case 'del':
				$ids = explode(',', $this->request->post[ 'id' ]);
				if (!empty($ids))
					foreach ($ids as $id) {
						$this->model_design_blog_author->deleteAuthor($id);
					}
				break;
			case 'save':
				$allowedFields = array( 'status', 'approve_entry');
				$ids = explode(',', $this->request->post[ 'id' ]);
				if (!empty($ids)) {
					foreach ($ids as $id) {
						foreach ($allowedFields as $field) {
							if ($field == 'status') {
								$data = array();
								$blog_user_id = $this->model_design_blog_author->getAuthorUserID($id);
								$data['blog_user_id'] = $blog_user_id;
								$data['status'] = $this->request->post[$field][$id];
								$this->model_design_blog_author->editAuthor($id, $data);
								
							}else{
								$this->model_design_blog_author->editAuthor($id, array($field => $this->request->post[$field][$id]));
							}
						}
					}
				}
				break;
			default:
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	/**
	 * update only one field
	 *
	 * @return void
	 */
	public function update_field() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		if (!$this->user->canModify('listing_grid/blog_author')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_author'),
					'reset_value' => true
				));
		}

		$this->loadLanguage('design/blog_author');
		$this->loadModel('design/blog_author');
		
		$this->session->data['author_fields'] = $this->request->post;
		$this->session->data['blog_author_id'] = $this->request->get['id'];
		
		//update controller data
		$this->extensions->hk_ProcessData($this, __FUNCTION__);

		if (isset($this->request->get['id'])) {
			//request sent from edit form. ID in url
			foreach ($this->request->post as $key => $value) {
				
                $data = array( $key => $value );
				if ($key == 'status') {
					$blog_user_id = $this->model_design_blog_author->getAuthorUserID($this->request->get[ 'id' ]);
					$data['blog_user_id'] = $blog_user_id;
				}
				$this->model_design_blog_author->editAuthor($this->request->get[ 'id' ], $data);
				
			}
			return null;
		}

		//request sent from jGrid. ID is key of array
		$fields = array( 'status');
		foreach ($fields as $f) {
			if (isset($this->request->post[ $f ]))
			
			
				foreach ($this->request->post[ $f ] as $k => $v) {
					if ($f == 'status') {
						$data = array();
						$blog_user_id = $this->model_design_blog_author->getAuthorUserID($k);
						$data['blog_user_id'] = $blog_user_id;
						$data['status'] = $v;
						$this->model_design_blog_author->editAuthor($k, $data);

					}else{
						$this->model_design_blog_author->editAuthor($k, array( $f => $v ));
					}
				}
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	public function user_data() {
	
		$blog_user_id = $this->request->get['id'];
		
		if($blog_user_id) {
			
			$this->loadModel('design/blog_author');
			$response = $this->model_design_blog_author->getUserData($blog_user_id);	

			$this->load->library('json');
			$this->response->setOutput(AJson::encode($response));
		}
		return false;
	}
	

}
 