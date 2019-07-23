<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}

class ControllerPagesShipstationXmlFeed extends AController {

	protected $registry;
	protected $access_key;

	public function __construct(){
		$this->registry = Registry::getInstance();
		$this->access_key = $this->registry->get('config')->get('shipstationxml_access_key');
	}

	public function main(){

		// Checking if key not match to the key in admin extension setting.
		if(!isset($this->request->get['key']) && $this->request->get['key'] != $this->access_key){ return false; }

		// Checking if the request is export.
		if(isset($this->request->get['action']) && $this->request->get['action'] == 'export'){
			$this->print_xml_order_feed();
		}

		// Checking if the request is update status
		if(isset($this->request->get['action']) && $this->request->get['action'] == 'shipnotify'){
			$this->update_ship_status();
		}

	}

	// Print Orders feed
	private function print_xml_order_feed(){
		// Checking if the day is Saturday or Sunday then not to execute.
		if(in_array(date("N"), array(6,7))){ return false; }

        $this->load->model('shipstation/xml_orders');
        $this->model_shipstation_xml_orders->getOrders();
	}


	// Update Order Shipping status
	public function update_ship_status(){

		if(!isset($this->request->get['order_number'])) { return false; }

		$order_id = $this->request->get['order_number'];
		$tracking_num = $this->request->get['tracking_number'];
		$tracking_num .= ', Carrier: '. $this->request->get['carrier'];
		$tracking_num .= ', Service: '. $this->request->get['service'];

		$data = array(
			'order_id' => $order_id,
			'shipping_method' => $this->request->get['carrier'],
			'order_status_id' => 3,
			'comment' => 'Status received from shipstation. Tracking#: '.$tracking_num,
			'append' => 1,
			'notify' => 0,
		);


		$this->load->model('shipstation/xml_orders');
        $this->model_shipstation_xml_orders->updateStatus($data);

		return false;


	}

}
