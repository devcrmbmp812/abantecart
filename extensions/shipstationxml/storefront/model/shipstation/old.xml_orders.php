<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}

class ModelShipstationXmlOrders extends Model {

	public $data = array ();
	private $error = array ();

	public function getOrders(){

        header('Content-type: text/xml');
		header('Pragma: public');
		header('Cache-control: private');
		header('Expires: -1');

		$start = (date("N") == 1) ? date("Y-m-d", strtotime("-3 days", strtotime("now"))) : date("Y-m-d", strtotime("-1 days", strtotime("now")));
		$startdate = date("Y-m-d h:i:s", strtotime("-1 day", strtotime($start)));
		$enddate = date("Y-m-d h:i:s", strtotime("+1 day", strtotime("now")));

		$sql = "SELECT *
  		        FROM ".DB_PREFIX."orders WHERE date_added > '".$startdate."' AND date_added < '".$enddate."' ORDER BY order_id DESC";
        $orders = $this->db->query($sql);
        // print_r($orders); die;

        /* create a dom document with encoding utf8 */
	    $domtree = new DOMDocument('1.0', 'UTF-8');

	    /* create the root element of the xml tree */
	    $xmlRoot = $domtree->createElement("Orders");
	    /* append it to the document created */
	    $xmlRoot = $domtree->appendChild($xmlRoot);
	    foreach ($orders->rows as $order) {

	    	$currentOrder = $domtree->createElement("Order");
		    $currentOrder = $xmlRoot->appendChild($currentOrder);

		    $order_id = $currentOrder->appendChild($domtree->createElement('OrderID'));
		    $order_id->appendChild($domtree->createCDATASection($order['order_id']));

		    $order_number = $currentOrder->appendChild($domtree->createElement('OrderNumber'));
		    $order_number->appendChild($domtree->createCDATASection($order['order_id']));
		    $currentOrder->appendChild($domtree->createElement('OrderDate', date("m/d/Y h:i A", strtotime($order['date_added']))));

		    $order_status = $order['order_status_id'];

		    $order_status_info = $this->db->query("SELECT os.*, osi.status_text_id
								    FROM ".$this->db->table('order_statuses')." os
									LEFT JOIN ".$this->db->table('order_status_ids')." osi ON osi.order_status_id = os.order_status_id
								    WHERE os.order_status_id = '".(int)$order_status."'");

		    $order_status = $currentOrder->appendChild($domtree->createElement('OrderStatus'));
		    $order_status->appendChild($domtree->createCDATASection($order_status_info->row['status_text_id']));

		    $customer_note = $currentOrder->appendChild($domtree->createElement('CustomerNotes'));
		    $customer_note->appendChild($domtree->createCDATASection($order['comment']));

		    $internal = $this->db->query("SELECT comment FROM ".$this->db->table("order_history")." WHERE order_id = '".$order['order_id']."' AND notify = '0'");
		    $internal_note = $currentOrder->appendChild($domtree->createElement('InternalNotes'));
		    $internal_note->appendChild($domtree->createCDATASection($internal->row['comment']));

		    $fraudlabs = $this->db->query("SELECT fraudlabspro_score,fraudlabspro_status,fraudlabspro_id FROM fraudlabspro WHERE fraudlabspro_status = 'REVIEW' AND order_id = '".$order['order_id']."'");
		    if($fraudlabs->num_rows > 0 && $fraudlabs->row['fraudlabspro_score'] > 70){
				$data = '<a href="//www.fraudlabspro.com/merchant/transaction-details/'.$fraudlabs->row['fraudlabspro_id'].'">#'.$order['order_id'].','.$fraudlabs->row['fraudlabspro_score'].','.$fraudlabs->row['fraudlabspro_status'].'</a>';
			}else{
				$data = '';
			}

			$customfield1 = $currentOrder->appendChild($domtree->createElement('CustomField1'));
			$customfield1->appendChild($domtree->createCDATASection($data));


		    $currentOrder->appendChild($domtree->createElement('LastModified',date("m/d/Y h:i A", strtotime($order['date_modified']))));

		    $currentOrder->appendChild($domtree->createElement('OrderTotal', round($order['total'], 2)));

		    $shipping_amount = $this->db->query("SELECT * FROM ".$this->db->table('order_totals')." WHERE order_id = '".$order['order_id']."' AND type = 'shipping'");
		    if($shipping_amount->num_rows > 0){
			    $ship_amount = round($shipping_amount->row['value'], 2);
			}else{
				$ship_amount = 0;
			}

			$currentOrder->appendChild($domtree->createElement('ShippingAmount', $ship_amount));
			$shippingMethod = $order['shipping_method'];
			if (stristr($order['shipping_method'],'Free')<>false) $shippingMethod = 'Free Shipping';
			$currentOrder->appendChild($domtree->createElement('ShippingMethod', $shippingMethod));

		    $currentCustomer = $domtree->createElement("Customer");
		    $currentCustomer = $currentOrder->appendChild($currentCustomer);

		    $customer_code = $currentCustomer->appendChild($domtree->createElement('CustomerCode'));
		    $customer_code->appendChild($domtree->createCDATASection($order['email']));

		    $billing = $domtree->createElement("BillTo");
		    $billing = $currentCustomer->appendChild($billing);

		    $billing_name = $billing->appendChild($domtree->createElement('Name'));
		    $billing_name->appendChild($domtree->createCDATASection($order['payment_firstname'].' '.$order['payment_lastname']));

		    $billing_company = $billing->appendChild($domtree->createElement('Company'));
		    $billing_company->appendChild($domtree->createCDATASection($order['payment_company']));

		    $billing_telephone = $billing->appendChild($domtree->createElement('Phone'));
		    $billing_telephone->appendChild($domtree->createCDATASection($order['telephone']));

		    $billing_email = $billing->appendChild($domtree->createElement('Email'));
		    $billing_email->appendChild($domtree->createCDATASection($order['email']));

		    $shipping = $domtree->createElement("ShipTo");
		    $shipping = $currentCustomer->appendChild($shipping);

		    $this->load->model('localisation/country');
		    $country = ($order['shipping_country_id'] != '0') ? $order['shipping_country_id'] : $order['payment_country_id'];
		    $country_info = $this->model_localisation_country->getCountry($country);

		    $this->load->model('localisation/zone');
		    $zone = ($order['shipping_zone_id'] != '0') ? $order['shipping_zone_id'] : $order['payment_zone_id'];
		    $zone_info = $this->model_localisation_zone->getZone($zone);

		    $ship_name = $shipping->appendChild($domtree->createElement('Name'));
		    $ship_name->appendChild($domtree->createCDATASection($order['shipping_firstname'].' '.$order['shipping_lastname']));

		    $ship_company = $shipping->appendChild($domtree->createElement('Company'));
		    $ship_company->appendChild($domtree->createCDATASection($order['shipping_company']));

		    $ship_phone = $shipping->appendChild($domtree->createElement('Phone'));
		    $ship_phone->appendChild($domtree->createCDATASection($order['telephone']));

		    $ship_add1 = $shipping->appendChild($domtree->createElement('Address1'));
		    $ship_add1->appendChild($domtree->createCDATASection($order['shipping_address_1']));

		    $ship_add2 = $shipping->appendChild($domtree->createElement('Address2'));
		    $ship_add2->appendChild($domtree->createCDATASection($order['shipping_address_2']));

		    $ship_city = $shipping->appendChild($domtree->createElement('City'));
		    $ship_city->appendChild($domtree->createCDATASection($order['shipping_city']));

		    $ship_zone = $shipping->appendChild($domtree->createElement('State'));
		    $ship_zone->appendChild($domtree->createCDATASection($zone_info['code']));

		    $country = $shipping->appendChild($domtree->createElement('Country'));
		    $country->appendChild($domtree->createCDATASection($country_info['iso_code_2']));

			$postal_code = ($order['shipping_postcode'] != '') ? $order['shipping_postcode'] : $order['payment_postcode'];
		    $ship_zip = $shipping->appendChild($domtree->createElement('PostalCode'));
		    $ship_zip->appendChild($domtree->createCDATASection($postal_code));

			$Items = $domtree->createElement("Items");
		    $Items = $currentOrder->appendChild($Items);

		    $sql = "SELECT *
  		        FROM ".DB_PREFIX."order_products WHERE order_id = '".$order['order_id']."'";
        	$products = $this->db->query($sql);
        	$product_sum = 0;
        	foreach ($products->rows as $product) {

        		$item = $domtree->createElement("Item");
		    	$item = $Items->appendChild($item);
		    	$sku = ($product['sku'] == '') ? $product['order_product_id'] : $product['sku'];

		    	$item_sku = $item->appendChild($domtree->createElement('SKU'));
		    	$item_sku->appendChild($domtree->createCDATASection($sku));

		    	$item_name = $item->appendChild($domtree->createElement('Name'));
		    	$item_name->appendChild($domtree->createCDATASection(substr($product['name'], 0, 150)));

        		$item->appendChild($domtree->createElement('Quantity',$product['quantity']));
        		$item->appendChild($domtree->createElement('UnitPrice',round($product['price'], 2)));
        	}

	    }


	    $domtree->formatOutput = true;
	    /* get the xml printed */
	    echo $domtree->saveXML(); die;
	}


	public function updateStatus($data){

		$order_id = $data['order_id'];

		$this->db->query("UPDATE `".$this->db->table("orders")."`
							SET order_status_id = '".(int)$data['order_status_id']."',
								date_modified = NOW()
							WHERE order_id = '".(int)$order_id."'");

        if ($data['append']) {
            $this->db->query("INSERT INTO ".$this->db->table("order_history")."
      		                    SET order_id = '".(int)$order_id."',
      		                        order_status_id = '".(int)$data['order_status_id']."',
      		                        notify = '0',
      		                        comment = '".$this->db->escape(strip_tags($data['comment']))."',
      		                        date_added = NOW()");
        }
	}

}
