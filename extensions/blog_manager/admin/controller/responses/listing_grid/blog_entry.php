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
class ControllerResponsesListingGridBlogEntry extends AController {

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadLanguage('blog_manager/blog_entry');
		$this->loadModel('design/blog_entry');

		//Prepare filter config
		$filter_params = array( 'blog_author', 'blog_category' );
		$grid_filter_params = array( 'bed.entry_title', 'status');
		//Build advanced filter
		$filter_form = new AFilter(array( 'method' => 'get', 'filter_params' => $filter_params ));
		$filter_grid = new AFilter(array( 'method' => 'post', 'grid_filter_params' => $grid_filter_params ));
		$filter_array = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

		$total = $this->model_design_blog_entry->getTotalEntries($filter_array);
		$response = new stdClass();
		$response->page = $filter_grid->getParam('page');
		$response->total = $filter_grid->calcTotalPages($total);
		$response->records = $total;
        $response->userdata = (object)array('');
		$results = $this->model_design_blog_entry->getEntries($filter_array);
		$results = !$results ? array() : $results;
		$i = 0;


		foreach ($results as $result) {									 
			
			if(!$result['comments_count']){
				$comments_count = 0;
			}else{
				$comments_count = (string)$this->html->buildElement(array(
																'type' => 'button',
																'name' => 'view_comments',
																'text' => $result['comments_count'],
																'href'=> $this->html->getSecureURL('design/blog_comment','&blog_entry_id='.$result['blog_entry_id']),
																'title' => $this->language->get('text_view').' '.$this->language->get('tab_comments')
															));
			}
			if(!$result['views']){
				$views = 0;
			}else{
				$views = $result['views'];
			}

			$response->rows[$i]['id'] = $result['blog_entry_id'];
			$response->rows[$i][ 'cell' ] = array(
				$result['blog_entry_id'],
				$result['entry_title'],
				$result['author'],
				$this->html->buildCheckbox(array(
					'name' => 'status[' . $result['blog_entry_id'] . ']',
					'value' => $result['status'],
					'style' => 'btn_switch',
				)),
				$views,
				$comments_count,
				dateISO2Display($result['release_date'], $this->language->get('date_format_short')),
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
		$this->loadLanguage('blog_manager/blog_entry');
		$this->loadModel('design/blog_entry');

		if (!$this->user->canModify('listing_grid/blog_entry')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_entry'),
					'reset_value' => true
				));
		}

		switch ($this->request->post[ 'oper' ]) {
			case 'del':
				$ids = explode(',', $this->request->post[ 'id' ]);
				if (!empty($ids))
					foreach ($ids as $id) {
						$this->model_design_blog_entry->deleteEntry($id);
					}
				break;
			case 'save':
				$allowedFields = array( 'status');
				$ids = explode(',', $this->request->post[ 'id' ]);
				if (!empty($ids)) {
					foreach ($ids as $id) {
						foreach ($allowedFields as $field) {
							$this->model_design_blog_entry->editEntry($id, array($field => $this->request->post[$field][$id]));
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

		if (!$this->user->canModify('listing_grid/blog_entry')) {
			$error = new AError('');
			return $error->toJSONResponse('NO_PERMISSIONS_402',
				array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_entry'),
					'reset_value' => true
				));
		}
		$this->loadModel('design/blog_entry');
		$this->loadLanguage('design/blog_entry');

	    if ( isset( $this->request->get['id'] ) ) {
		    //request sent from edit form. ID in url
		    foreach ($this->request->post as $field => $value ) {
				if($field=='keyword'){
					if($err = $this->html->isSEOkeywordExists('blog_entry_id='.$this->request->get['id'], $value)){
						$error = new AError('');
						return $error->toJSONResponse('VALIDATION_ERROR_406', array( 'error_text' => $err ));
					}
				}

				$err = $this->_validateField($field, $value);
				if (!empty($err)) {
					$error = new AError('');
					return $error->toJSONResponse('VALIDATION_ERROR_406', array( 'error_text' => $err ));
				}

				$this->model_design_blog_entry->editEntry($this->request->get['id'], array($field => $value) );
			}
		    return null;
	    }

		//request sent from jGrid. ID is key of array
		$fields = array( 'status' );
		foreach ($fields as $f) {
			if (isset($this->request->post[ $f ]))
				foreach ($this->request->post[ $f ] as $k => $v) {
					$this->model_design_blog_entry->editEntry($k, array( $f => $v ));
				}
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
	
	private function _validateField($field, $value) {
		$this->loadLanguage('design/blog_entry');
		$err = '';
		switch ($field) {
			case 'blog_entry_descriptions' :
				$language_id = $this->language->getContentLanguageID();
				if (isset($value[$language_id][ 'entry_title' ]) && ( mb_strlen($value[$language_id][ 'entry_title' ]) < 2 || mb_strlen($value[$language_id][ 'entry_title' ]) > 250 )) {
					$err = $this->language->get('error_title');
				}
				break;
			case 'keyword' :
				$err = $this->html->isSEOkeywordExists('blog_entry_id='.$this->request->get['id'], $value);
				break;
			case 'release_date' :
				if (!$this->request->post['release_date']) {
					$err =  $this->language->get('error_date');	
				}
				break;
		}
		return $err;
	}

}
