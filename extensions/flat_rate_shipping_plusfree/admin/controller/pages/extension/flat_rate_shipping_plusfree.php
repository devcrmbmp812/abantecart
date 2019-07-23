<?php
if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

class ControllerPagesExtensionFlatRateShippingPlusFree extends AController {
	private $error = array();
	public $data = array();
	private $fields = array('flat_rate_shipping_plusfree_tax_class_id', 'flat_rate_shipping_plusfree_sort_order');
	
	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->request->get['extension'] = 'flat_rate_shipping_plusfree';

		$this->loadLanguage('flat_rate_shipping_plusfree/flat_rate_shipping_plusfree');
		$this->document->setTitle( $this->language->get('flat_rate_shipping_plusfree_name') );
		$this->load->model('setting/setting');

		//set store id based on param or session.
		$store_id = (int)$this->config->get('config_store_id');
		if ($this->session->data['current_store_id']) {
			$store_id = (int)$this->session->data['current_store_id'];
		}

		if ($this->request->is_POST() && ($this->_validate())) {
			$this->model_setting_setting->editSetting('flat_rate_shipping_plusfree', $this->request->post, $store_id );
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('extension/flat_rate_shipping_plusfree'));
		}
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		$this->data['success'] = $this->session->data['success'];
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

  		$this->document->initBreadcrumb( array (
       		'href'      => $this->html->getSecureURL('index/home'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		 ));
   		$this->document->addBreadcrumb( array ( 
       		'href'      => $this->html->getSecureURL('extension/extensions/shipping'),
       		'text'      => $this->language->get('text_shipping'),
      		'separator' => ' :: '
   		 ));
   		$this->document->addBreadcrumb( array ( 
       		'href'      => $this->html->getSecureURL('extension/flat_rate_shipping_plusfree'),
       		'text'      => $this->language->get('flat_rate_shipping_plusfree_name'),
      		'separator' => ' :: ',
		    'current'   => true
   		 ));

		$this->data['form_store_switch'] = $this->html->getStoreSwitcher();

		$this->load->model('localisation/tax_class');
		$results = $this->model_localisation_tax_class->getTaxClasses();
		$tax_classes = array( 0 => $this->language->get ( 'text_none' ));
		foreach ( $results as $k => $v ) {
			$tax_classes[ $v['tax_class_id'] ] = $v['title'];
		}

		$this->load->model('localisation/location');
		$this->data['locations'] = $this->model_localisation_location->getLocations();
		$locations = array( 0 => $this->language->get ( 'text_all_zones' ));
		foreach ( $this->data['locations'] as $k => $v ) {
			$locations[ $v['location_id'] ] = $v['name'];
		}

		$settings = $this->model_setting_setting->getSetting('flat_rate_shipping_plusfree',$store_id);

		foreach ( $this->fields as $f ) {
			if (isset ( $this->request->post [$f] )) {
				$this->data [$f] = $this->request->post [$f];
			} else {
				$this->data [$f] = $settings[$f];
			}
		}
		
		foreach ($this->data['locations'] as $location) {
			if (isset($this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_rate'])) {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_rate'] = $this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_rate'];
			} else {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_rate'] = $settings['flat_rate_shipping_plusfree_' . $location['location_id'] . '_rate'];
			}		
			
			if (isset($this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_status'])) {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_status'] = $this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_status'];
			} else {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_status'] = $settings['flat_rate_shipping_plusfree_' . $location['location_id'] . '_status'];
			}		
		
			if (isset($this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_freecost'])) {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_freecost'] = $this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_freecost'];
			} else {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_freecost'] = $settings['flat_rate_shipping_plusfree_' . $location['location_id'] . '_freecost'];
			}		
			if (isset($this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_tax_id'])) {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_tax_id'] = $this->request->post['flat_rate_shipping_plusfree_' . $location['location_id'] . '_tax_id'];
			} else {
				$this->data['flat_rate_shipping_plusfree_' . $location['location_id'] . '_tax_id'] = $settings['flat_rate_shipping_plusfree_' . $location['location_id'] . '_tax_id'];
			}		
		}


		
		
		
		
		$this->data ['action'] = $this->html->getSecureURL ( 'extension/flat_rate_shipping_plusfree' );
		$this->data['cancel'] = $this->html->getSecureURL('extension/shipping');
		$this->data ['heading_title'] = $this->language->get ( 'text_additional_settings' );
		$this->data ['form_title'] = $this->language->get ( 'flat_rate_shipping_plusfree_name' );
		$this->data ['update'] = $this->html->getSecureURL ( 'listing_grid/extension/update', '&id=flat_rate_shipping_plusfree' );

		$form = new AForm ( 'HS' );
		$form->setForm ( array (
				'form_name' => 'editFrm',
				'update' => $this->data ['update'] ) );

		$this->data['form']['form_open'] = $form->getFieldHtml ( array (
				'type' => 'form',
				'name' => 'editFrm',
				'action' => $this->data ['action'],
				'attr' => 'data-confirm-exit="true" class="aform form-horizontal"',
		) );
		$this->data['form']['submit'] = $form->getFieldHtml ( array (
				'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get ( 'button_save' )
		) );
		$this->data['form']['cancel'] = $form->getFieldHtml ( array (
				'type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get ( 'button_cancel' )
		) );


		foreach ($this->data['locations'] as $location) {

			$rate = 'flat_rate_shipping_plusfree_' . $location['location_id'] . '_rate';
			$status = 'flat_rate_shipping_plusfree_' . $location['location_id'] . '_status';
			$freecost = 'flat_rate_shipping_plusfree_' . $location['location_id'] . '_freecost';
			$tax_id = 'flat_rate_shipping_plusfree_' . $location['location_id'] . '_tax_id';
			
			$this->data['form']['fields']['rates'][$status] = $form->getFieldHtml(array(
				'type' => 'checkbox',
				'name' => $status,
				'value' => $this->data[$status],
				'style'  => 'btn_switch',
			));
			
			$this->data['form']['fields']['rates'][$freecost] = $form->getFieldHtml(array(
				 'type' => 'input',
				'name' => $freecost,
				'value' => $this->data[$freecost],
				'style' => 'xl-field',
			));
			
			
			$this->data['form']['fields']['rates'][$rate] = $form->getFieldHtml(array(
				 'type' => 'input',
				'name' => $rate,
				'value' => $this->data[$rate],
				'style' => 'xl-field',
			));
		
			$this->data['form']['fields']['rates'][$tax_id] = $form->getFieldHtml(array(
				'type' => 'selectbox',
				'name' => $tax_id,
				'options' => $tax_classes,
		         'value' => $this->data[$tax_id],
			));
		
		
		}
	
		
		
		$this->data['form']['fields']['sort_order'] = $form->getFieldHtml(array(
		    'type' => 'input',
		    'name' => 'flat_rate_shipping_plusfree_sort_order',
		    'value' => $this->data['flat_rate_shipping_plusfree_sort_order'],
	    ));

		$this->view->batchAssign (  $this->language->getASet () );

		//load tabs controller

		$this->data['groups'][] = 'additional_settings';
		$this->data['link_additional_settings'] = $this->data['add_sett']->href;
		$this->data['active_group'] = 'additional_settings';

		$tabs_obj = $this->dispatch('pages/extension/extension_tabs', array( $this->data ) );
		$this->data['tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$obj = $this->dispatch('pages/extension/extension_summary', array( $this->data ) );
		$this->data['extension_summary'] = $obj->dispatchGetOutput();
		unset($obj);

		$this->view->batchAssign( $this->data );
		$this->processTemplate('pages/extension/flat_rate_shipping_plusfree.tpl' );

        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
		
	private function _validate() {
		if (!$this->user->canModify('extension/flat_rate_shipping_plusfree')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}