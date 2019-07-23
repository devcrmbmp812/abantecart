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
class ControllerResponsesListingGridBlogUser extends AController {

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_user');
		$this->loadModel('design/blog_user');
		$this->loadModel('design/blog_manager');
		$login_data = $this->model_design_blog_manager->getblog_config('login_data');
		
		//Prepare filter config
		$filter_params = array('role_id' );
		$grid_filter_params = array('username', 'status');
		//Build advanced filter
		$filter_form = new AFilter(array( 'method' => 'get', 'filter_params' => $filter_params ));
		$filter_grid = new AFilter(array( 'method' => 'post', 'grid_filter_params' => $grid_filter_params ));
		
		$filter_data = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

		$total = $this->model_design_blog_user->getTotalUsers($filter_data);
		$response = new stdClass();
		$response->page = $filter_grid->getParam('page');
		$response->total = $filter_grid->calcTotalPages($total);
		$response->records = $total;
        $response->userdata = (object)array('');
		$results = $this->model_design_blog_user->getUsers($filter_data);
		$results = !$results ? array() : $results;
		$i = 0;

		foreach ($results as $result) {	
			if(!$result['comments_count']){
				$result['comments_count'] = 0;
			}	
			if($result['source']) {
				if	($result['source'] == 'self') { $result['source'] = 'Blog'; }
				elseif ($result['source'] == 'customer') { $result['source'] = 'Customer'; }
			}
			
			$response->rows[$i]['id'] = $result['blog_user_id'];
			$response->rows[$i]['cell'] = array(
				$result['name'],
				$result['username'],
				$result['role_description'],
				$result['source'],
				$result['comments_count'],
				$this->html->buildCheckbox(array(
					'name' => 'status[' . $result['blog_user_id'] . ']',
					'value' => $result['status'],
					'style' => 'btn_switch',
				)),
				$this->html->buildCheckbox(array(
					'name' => 'approve[' . $result['blog_user_id'] . ']',
					'value' => $result['approve'],
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
		$this->loadLanguage('blog_manager/blog_user');
		$this->loadModel('design/blog_user');

		if (!$this->user->canModify('listing_grid/blog_user')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_user'),
					'reset_value' => true
				));
		}

		switch ($this->request->post[ 'oper' ]) {
			case 'del':
				$ids = explode(',', $this->request->post[ 'id' ]);
				if (!empty($ids))
					foreach ($ids as $id) {
						$this->model_design_blog_user->deleteUser($id);
					}
				break;
			case 'save':
				$allowedFields = array( 'status', 'approve');
				$ids = explode(',', $this->request->post[ 'id' ]);
				if (!empty($ids)) {
					foreach ($ids as $id) {
						foreach ($allowedFields as $field) {
							$this->model_design_blog_user->editUser($id, array($field => $this->request->post[$field][$id]));
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

		if (!$this->user->canModify('listing_grid/blog_user')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_user'),
					'reset_value' => true
				));
		}

		$this->loadLanguage('design/blog_user');

		$this->loadModel('design/blog_user');
		if (isset($this->request->get[ 'id' ])) {
			//request sent from edit form. ID in url
			foreach ($this->request->post as $key => $value) {
				$err = $this->_validateField($key, $value);
				if (!empty($err)) {
					$error = new AError('');
					return $error->toJSONResponse('VALIDATION_ERROR_406', array( 'error_text' => $err ));
				}
                $data = array( $key => $value );
				$this->model_design_blog_user->editUser($this->request->get[ 'id' ], $data);
			}
			return null;
		}

		//request sent from jGrid. ID is key of array
		$fields = array( 'status', 'approve' );
		foreach ($fields as $f) {
			if (isset($this->request->post[ $f ]))
				foreach ($this->request->post[ $f ] as $k => $v) {
					$this->model_design_blog_user->editUser($k, array( $f => $v ));
				}
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	private function _validateField($field, $value) {

		$err = '';
		switch ($field) {
			case 'firstname' :
				if (mb_strlen($this->request->post['firstname']) < 5 || mb_strlen($this->request->post['firstname']) > 32) {
					$err =  $this->language->get('error_name');
				}
				break;
			case 'lastname' :
				if (mb_strlen($this->request->post['lastname']) < 5 || mb_strlen($this->request->post['lastname']) > 32) {
					$err =  $this->language->get('error_name');
				}
				break;
			case 'username' :
				if (mb_strlen($this->request->post['username']) < 5 || mb_strlen($this->request->post['username']) > 20) {
      				$err = $this->language->get('error_username');
    			}
			case 'role_id' :
				if($this->request->post['role_id'] == 0) {
					$err =  $this->language->get('error_role_id');
				}
				break;
			case 'email' :
				if (mb_strlen($this->request->post['email']) > 96 || !preg_match(EMAIL_REGEX_PATTERN, $this->request->post['email'])) {
					$err =  $this->language->get('error_email');
				}
				break;
		}

		return $err;
	}

}
