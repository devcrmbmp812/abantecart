<?php

if (!defined('DIR_CORE') || IS_ADMIN) {
	header('Location: static_pages/');
}
class ControllerResponsesSearchAutoGlobalSearchResult extends AController {
	public $error = array();

	public $data = array();

	public function main() {
		$registry = Registry::getInstance();
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadModel('tool/global_search');

		//$this->loadModel('catalog/product');
		//$this->loadModel('tool/seo_url');
		//$this->html->getSEOURL('product/category', '&path=' . $path, '&encode');
		//$this->html->getSEOURL('product/manufacturer', '&manufacturer_id=' . $request['manufacturer_id'], '&encode');
		//$this->html->getSEOURL('product/product', $url . '&product_id=' . $product_id, '&encode');
		//$this->loadModel('catalog/category');

		//$this->loadModel('tool/seo_url', 'storefront');
		if($this->config->get('ultra_search_images')){
			$this->loadModel('catalog/product', 'storefront');
		}

		$this->loadLanguage('ultra_search/ultra_search');
		//$this->baseObject->
		$page = (int)$this->request->post['page']; // get the requested page
		$limit = $this->request->post['rows']; // get how many rows we want to have into the grid

		$results = $this->model_tool_global_search->getResult($this->request->get['search_category'], $this->request->get['keyword']);
		// prevent repeat request to db for total
		if (!isset($this->session->data['search_totals'][ $this->request->get['search_category'] ])) {
			$total = $this->model_tool_global_search->getTotal($this->request->get['search_category'], $this->request->get['keyword']);
		} else {
			$total = $this->session->data['search_totals'][ $this->request->get['search_category'] ];
			unset($this->session->data['search_totals'][ $this->request->get['search_category'] ]);
		}

		if ($total > 0) {
			$total_pages = (int)ceil($total / $limit);
		} else {
			$total_pages = 0;
		}

		//$page = $page>$total_pages ? $total_pages : $page;

		$response = new stdClass();
		$response->page = $page;
		$response->total = $total_pages;
		$response->records = $total;
		$response->userdata = new stdClass();
		$i = 0;
		foreach ($results['result'] as $result) {
			$response->rows[ $i ]['id'] = $i + 1;
			$response->userdata->type[$i + 1] = $result['type'];
			$response->rows[ $i ]['cell'] = array( $i + 1, $result['text']
			);
			$i++;
		}
		$this->data['response'] = $response;

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($this->data['response']));

	}

	/**
	 * function check access rights to search results
	 * @param string $permissions
	 * @return boolean
	 */
	private function validate($permissions = null) {
		// check access to global search
		if (!$this->user->canAccess('tool/global_search')) {
			$this->error ['warning'] = $this->language->get('error_permission');
		}
		$this->extensions->hk_ValidateData($this);
		return !$this->error ? true : false;
	}

	public function suggest() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->loadModel('tool/global_search');
		$this->loadLanguage('ultra_search/ultra_search');

		$search_categories = $this->model_tool_global_search->getSearchSources('all');
		$result_controllers = $this->model_tool_global_search->results_controllers;
		$results['response'] = array();

		foreach ($search_categories as $id => $name) {
			//$this->log->write(print_r($id, true).' search id');

			switch ($id) {
			case ($id == 'product_categories'):
			$r = $this->model_tool_global_search->getResult($id, $this->request->get['term'], 'suggest');
			break;
			case ($id == 'products'):
			$r = $this->model_tool_global_search->getResult($id, $this->request->get['term'], 'suggest');
			break;
			case ($id == 'reviews'):
			$r = $this->model_tool_global_search->getResult($id, $this->request->get['term'], 'suggest');
			break;
			case ($id == 'manufacturers'):
			$r = $this->model_tool_global_search->getResult($id, $this->request->get['term'], 'suggest');
			break;
			case ($id == 'contents'):
			$r = $this->model_tool_global_search->getResult($id, $this->request->get['term'], 'suggest');
			break;
			}


			if(is_array($r['result'])) {
			foreach ($r['result'] as $item) {
				if (!$item) { continue; }
				$tmp = array();
				// exception for extension settings
				/*if( $id=='settings' && !empty($item['extension'])){
					$tmp_id='extensions';
					if($item['type']=='total'){
						$page_rt = sprintf($result_controllers[$tmp_id]['page2'],$item['extension']);
					}else{
						$page_rt = $result_controllers[ $tmp_id ]['page'];
					}
				} else {*/
					$tmp_id = $id;
					$page_rt = $result_controllers[ $tmp_id ]['page'];
				//}

				if (!is_array($result_controllers[ $tmp_id ]['id'])) {
					$tmp[ ] = $result_controllers[ $tmp_id ]['id'] . '=' . $item[ $result_controllers[ $tmp_id ]['id'] ];
				} else {
					foreach ($result_controllers[ $tmp_id ]['id'] as $al => $j) {
						// if some id have alias - build link with it
						$tmp[ ] = $j . '=' . $item[ $j ];
					}
				}

				//$this->log->write(print_r($item, true).' $item array');

				if($item['controller'] == 'product/product' && $this->config->get('ultra_search_images')){
					//$this->log->write(print_r($item, true).' $item product array');
					$resource = new AResource('image');
					//$this->config->get('config_image_cart_width')
					$thumbnail = $resource->getMainThumb('products',
							$item['product_id'],
							(int)$this->config->get('config_image_grid_width'),
							(int)$this->config->get('config_image_grid_height')
					);
					if(!preg_match('/no_image/', $thumbnail['thumb_url'])){
						$item['image'] = $thumbnail['thumb_url'];  // only path
						//$item['image'] = $thumbnail['thumb_html']; // img src
					}
					//$thumbnail = $thumbnails[$item['product_id']];
					//$this->log->write(print_r($thumbnail, true).' $item product image');
				}

				if($item['controller'] == 'product/manufacturer' && $this->config->get('ultra_search_images')){
					//$this->log->write(print_r($item, true).' $item product array');
					$resource = new AResource('image');
					//$this->config->get('config_image_cart_width')
					$thumbnail = $resource->getMainThumb('manufacturers',
							$item['manufacturer_id'],
							(int)$this->config->get('config_image_grid_width'),
							(int)$this->config->get('config_image_grid_height')
					);
					if(!preg_match('/no_image/', $thumbnail['thumb_url'])){
						$item['image'] = $thumbnail['thumb_url'];  // only path
						//$item['image'] = $thumbnail['thumb_html']; // img src
					}
					//$thumbnail = $thumbnails[$item['product_id']];
					//$this->log->write(print_r($thumbnail, true).' $item product image');
				}


				if($item['controller'] == 'product/category' && $this->config->get('ultra_search_images')){
					//$this->log->write(print_r($item, true).' $item product array');
					$resource = new AResource('image');
					//$this->config->get('config_image_cart_width')
					$thumbnail = $resource->getMainThumb('categories',
							$item['category_id'],
							(int)$this->config->get('config_image_grid_width'),
							(int)$this->config->get('config_image_grid_height')
					);
					if(!preg_match('/no_image/', $thumbnail['thumb_url'])){
						$item['image'] = $thumbnail['thumb_url'];  // only path
						//$item['image'] = $thumbnail['thumb_html']; // img src
					}
					//$thumbnail = $thumbnails[$item['product_id']];
					//$this->log->write(print_r($thumbnail, true).' $item product image');
				}



				//$this->log->write(print_r($item, true).' $item  with iamge');


				if($item['controller'] == 'setting/setting'){
					$a = explode('-',$item['active']);
					if($a[0] == 'appearance' || $a[0] == 'im'){
						unset($result_controllers[ $tmp_id ]['response']);
					}
				}

				/*if( $id=='commands'){
					$item['page'] = $item['url'];
					unset($item['url']);
				} else {*/
					$item['controller'] = $result_controllers[ $tmp_id ]['response'] ? $this->html->getSecureURL($result_controllers[ $tmp_id ]['response'], '&' . implode('&', $tmp)) : '';
					$item['page'] = $this->html->getSecureURL($page_rt, '&' . implode('&', $tmp));
				//}

				$item['category'] = $id;
				$item['category_name'] = $this->language->get('text_' . $id);
				$item['label'] = mb_strlen($item['title']) > 40 ? mb_substr($item['title'], 0, 40) . '...' : $item['title'];

				$item['text'] = htmlentities( $item['text'], ENT_QUOTES, 'utf-8', FALSE);
				$item['text'] = !$item['text'] ? $item['title'] : $item['text'];

				$results['response'][ ] = $item;
			}
			}
		}

		$this->data['response'] = $results;
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($this->data['response']));
	}
}
