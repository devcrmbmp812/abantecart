<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}
class ControllerPagesCatalogProductBulk extends AController {
	public $data = array();
	
  	public function main() {
		$this->loadLanguage('catalog/product_bulk');
		$this->document->addStyle(array(
		    'href' => RDIR_TEMPLATE . 'stylesheet/bulk-update.css',
		    'rel' => 'stylesheet'
		));
		$this->document->setTitle( $this->language->get('heading_title') );
  		$this->document->initBreadcrumb( array (
			'href'      => $this->html->getSecureURL('index/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		 ));
		$this->document->addBreadcrumb( array (
			'href'      => $this->html->getSecureURL('catalog/product_bulk'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: ',
			'current' => true,
		 ));
		$this->data['action'] = $this->html->getSecureURL('catalog/product_bulk/preview');
   		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/catalog/product_bulk.tpl' );
	}
	
	public function preview() {
		$stream = '';
		$this->data['sheet'] = array();
		if ($this->request->is_POST() && ($this->request->post['tsv_submit'] == 'Preview')) {
			$str = $this->request->post['tsv_file'];
			$stream = stripslashes($str);
			$stream = "https://docs.google.com/spreadsheets/d/e/2PACX-1vRmykdQpTo49WvEPqkjIWARg0UgxIOpwaTMtIq7ETVrNwHMhviJMPkBTiB81I1lzMxGV8bZmXwRH8S3/pub?gid=302416844&single=true&output=tsv";
		}
		$this->data['tsv_file'] = $stream;
		try {
			if(($fileTsv = fopen($stream, 'r'))) {
				$d = fgets($fileTsv);
				for ($i=0; $i <100 ; $i++) {
					if(!feof($fileTsv)) {
						$d = fgets($fileTsv);
						$pieces= explode("\t",$d);
						$name = $pieces[0];
						$id = $pieces[2];
						$oldprice = $pieces[3];
						$price = $pieces[7];
						if ($id) {
							$this->data['sheet'][] = array("name"=>$name,"id"=>$id, "oldprice"=>$oldprice, "price"=>$price);
						}
						
					} else {
						break;
					}
				}
			}
		} catch (Exception $e) {
			echo $e;
		} finally {
			fclose($stream);
		}
		
		$this->loadLanguage('catalog/product_bulk');
		
		$this->document->addStyle(array(
		    'href' => RDIR_TEMPLATE . 'stylesheet/bulk-update.css',
		    'rel' => 'stylesheet'
		));
		$this->document->setTitle( $this->language->get('heading_title'));
  		$this->document->initBreadcrumb( array (
			'href'      => $this->html->getSecureURL('index/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		 ));
		$this->document->addBreadcrumb( array (
			'href'      => $this->html->getSecureURL('catalog/product_bulk'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: ',
			'current' => true,
		 ));
		$this->data['action'] = $this->html->getSecureURL('catalog/product_bulk/preview');
		$this->data['update'] = $this->html->getSecureURL('catalog/product_bulk/update');
   		
   		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/catalog/product_bulk.tpl');
	}
	
	public function update() {
		$this->loadModel('catalog/product');
		$this->data['sheet'] = array();
		$this->data['test'] = array();
		if ($this->request->is_POST() && $this->request->post['users']) {
			$id = $this->request->post['users'];
			$updated_price = $this->request->post['price'];
			$results = array_intersect_key($id,$updated_price);
			foreach ($results as $result) {
				$this->data['test'][] = array('id'=>$id[$result], 'price'=>$updated_price[$result]);
				$this->model_catalog_product->updateProductDiscountByProduct($id[$result], array('price'=>$updated_price[$result]));
			}
		}
		
		$this->loadLanguage('catalog/product_bulk');
		$this->document->addStyle(array(
		    'href' => RDIR_TEMPLATE . 'stylesheet/bulk-update.css',
		    'rel' => 'stylesheet'
		));
		$this->document->setTitle( $this->language->get('heading_title'));
  		$this->document->initBreadcrumb( array (
			'href'      => $this->html->getSecureURL('index/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		 ));
		$this->document->addBreadcrumb( array (
			'href'      => $this->html->getSecureURL('catalog/product_bulk'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: ',
			'current' => true,
		 ));
		$this->data['action'] = $this->html->getSecureURL('catalog/product_bulk/preview');
		$this->data['update'] = $this->html->getSecureURL('catalog/product_bulk/update');
   		
   		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/catalog/product_bulk.tpl');
	}
}
