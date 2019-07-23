<?php
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

class ExtensionFraudLabsPro extends Extension {

	public function onControllerPagesCheckoutSuccess_InitData() {
		$this->validateOrder();
	}

	public function onControllerPagesSaleOrder_InitData() {
		$that = $this->baseObject;
		$json = array();


		if(isset($_GET['order_id'])) {
			if (isset($_POST['approve'])) {
				$request = array(
					'key' => $that->config->get('fraudlabspro_api_key'),
					'action' => 'APPROVE',
					'id' => $_POST['transactionId'],
					'format' => 'json'
				);

				if (is_null($json = json_decode(@file_get_contents('https://api.fraudlabspro.com/v1/order/feedback?' . http_build_query($request)))) === false) {
					if ($json->fraudlabspro_error_code == '' || $json->fraudlabspro_error_code == '304') {
						$that->db->query("UPDATE fraudlabspro SET fraudlabspro_status='APPROVE' WHERE order_id=" . (int) $_GET['order_id']);
						$that->db->query("UPDATE " . DB_PREFIX . "orders SET order_status_id=5 WHERE order_id=" . (int) $_GET['order_id']);
					}
				}
			}
			if (isset($_POST['reject'])) {
				$request = array(
					'key' => $that->config->get('fraudlabspro_api_key'),
					'action' => 'REJECT',
					'id' => $_POST['transactionId'],
					'format' => 'json'
				);

				if (is_null($json = json_decode(@file_get_contents('https://api.fraudlabspro.com/v1/order/feedback?' . http_build_query($request)))) === false) {
					if ($json->fraudlabspro_error_code == '' || $json->fraudlabspro_error_code == '304') {
						$that->db->query("UPDATE fraudlabspro SET fraudlabspro_status='REJECT' WHERE order_id=" . (int) $_GET['order_id']);
						$that->db->query("UPDATE " . DB_PREFIX . "orders SET order_status_id=8 WHERE order_id=" . (int) $_GET['order_id']);
					}
				}
			}

			if (isset($_POST['Blacklist'])) {

				//$a = $_POST['flpnote'];
				//echo "<script>alert('$a');</script>";
				$request = array(
					'key' => $that->config->get('fraudlabspro_api_key'),
					'action' => 'REJECT_BLACKLIST',
					'id' => $_POST['transactionId'],
					'format' => 'json',
					'note' => $_POST['flpnote']
				);

				if (is_null($json = json_decode(@file_get_contents('https://api.fraudlabspro.com/v1/order/feedback?' . http_build_query($request)))) === false) {
					if ($json->fraudlabspro_error_code == '' || $json->fraudlabspro_error_code == '304') {
						$that->db->query("UPDATE fraudlabspro SET fraudlabspro_status='REJECT' WHERE order_id=" . (int) $_GET['order_id']);
						$that->db->query("UPDATE " . DB_PREFIX . "orders SET order_status_id=5 WHERE order_id=" . (int) $_GET['order_id']);
					}
				}
			}

			$query = $that->db->query("SELECT * FROM fraudlabspro WHERE order_id=" . (int) $_GET['order_id']);

			if($query->num_rows) {
				$json = $query->row;
			}

			$that->document->addScript('extensions/fraudlabspro/script.php?json=' . rawurlencode(json_encode($json)));
		}
	}

	private function validateOrder() {
		$that = $this->baseObject;

		if(!$that->session->data['order_id']) return;

		$that->db->query("ALTER TABLE `fraudlabspro` CHANGE COLUMN `ip_address` `ip_address` VARCHAR(39);");

		$that->load->model('checkout/order');
		$order_info = $that->model_checkout_order->getOrder($that->session->data['order_id']);

		$qty = 0;
		$products = $that->cart->getProducts();

		foreach($products as $product){
			$qty += $product['quantity'];
		}

		switch($order_info['payment_method']) {
			case 'Cash On Delivery':
				$paymentMode = 'cod';
				break;

			case 'Cheque / Money Order':
				$paymentMode = 'moneyorder';
				break;

			case 'Bank Transfer':
				$paymentMode = 'bankdeposit';
				break;

			case 'Credit Card / Debit Card':
				$paymentMode = 'creditcard';
				break;

			default:
				$paymentMode = 'others';
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		if(isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)){
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$xip = trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));

			if(filter_var($xip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
				$ip = $xip;
			}
		}

		$request = array(
			'key'			=> $that->config->get('fraudlabspro_api_key'),
			'format'		=> 'json',
			'ip'			=> ($that->config->get('fraudlabspro_test_ip')) ? $that->config->get('fraudlabspro_test_ip') : $ip,
			'first_name'	=> $order_info['firstname'],
			'last_name'		=> $order_info['lastname'],
			'bill_addr'		=> trim($order_info['payment_address_1'] . ' ' . $order_info['payment_address_2']),
			'bill_city'		=> $order_info['payment_city'],
			'bill_state'	=> $order_info['payment_zone'],
			'bill_zip_code'	=> $order_info['payment_postcode'],
			'bill_country'	=> $order_info['payment_iso_code_2'],
			'user_phone'	=> $order_info['telephone'],
			'ship_addr'		=> trim($order_info['shipping_address_1'] . ' ' . $order_info['shipping_address_2']),
			'ship_city'		=> $order_info['shipping_city'],
			'ship_state'	=> $order_info['shipping_zone'],
			'ship_zip_code' => $order_info['shipping_postcode'],
			'ship_country'	=> $order_info['shipping_iso_code_2'],
			'email'			=> $order_info['email'],
			'email_domain'	=> substr($order_info['email'], strpos($order_info['email'], '@') + 1),
			'email_hash'	=> $this->hashIt($order_info['email']),
			'user_order_id'	=> $that->session->data['order_id'],
			'amount'		=> $order_info['total'],
			'quantity'		=> $qty,
			'currency'		=> $order_info['currency'],
			'payment_mode'	=> $paymentMode,
			'source'		=> 'abantecart',
			'source_version'=> '1.0.7'
		);

		if (is_null($json = json_decode(@file_get_contents('https://api.fraudlabspro.com/v1/order/screen?' . http_build_query($request)))) === false) {
			$data = array(
				$that->session->data['order_id'],
				$json->is_country_match,
				$json->is_high_risk_country,
				$json->distance_in_km,
				$json->distance_in_mile,
				($that->config->get('fraudlabspro_test_ip')) ? $that->config->get('fraudlabspro_test_ip') : $_SERVER['REMOTE_ADDR'],
				$json->ip_country,
				$json->ip_region,
				$json->ip_city,
				$json->ip_continent,
				$json->ip_latitude,
				$json->ip_longitude,
				$json->ip_timezone,
				$json->ip_elevation,
				$json->ip_domain,
				$json->ip_mobile_mnc,
				$json->ip_mobile_mcc,
				$json->ip_mobile_brand,
				$json->ip_netspeed,
				$json->ip_isp_name,
				$json->ip_usage_type,
				$json->is_free_email,
				$json->is_new_domain_name,
				$json->is_proxy_ip_address,
				$json->is_bin_found,
				$json->is_bin_country_match,
				$json->is_bin_name_match,
				$json->is_bin_phone_match,
				$json->is_bin_prepaid,
				$json->is_address_ship_forward,
				$json->is_bill_ship_city_match,
				$json->is_bill_ship_state_match,
				$json->is_bill_ship_country_match,
				$json->is_bill_ship_postal_match,
				$json->is_ip_blacklist,
				$json->is_email_blacklist,
				$json->is_credit_card_blacklist,
				$json->is_device_blacklist,
				$json->is_user_blacklist,
				$json->fraudlabspro_score,
				$json->fraudlabspro_distribution,
				$json->fraudlabspro_status,
				$json->fraudlabspro_id,
				$json->fraudlabspro_error_code,
				$json->fraudlabspro_message,
				$json->fraudlabspro_credits,
				$that->config->get('fraudlabspro_api_key'),
			);

			$that->db->query("INSERT INTO `fraudlabspro`  VALUES('". implode("','", $data) . "')");
		}

		if ((int)$json->fraudlabspro_score > $that->config->get('fraudlabspro_reject_score') || $json->fraudlabspro_status == 'REJECT') {
			$that->db->query("UPDATE " . DB_PREFIX . "orders SET order_status_id=1 WHERE order_id=" . (int) $that->session->data['order_id']);
			//header('Location: ' . $that->html->getSecureURL('checkout/success'));
			//exit();
		}
	}

	private function hashIt() {
		$hash = 'fraudlabspro_' . $s;

		for ($i = 0; $i < 65536; $i++)
			$hash = sha1('fraudlabspro_' . $hash);

		return $hash;
	}
}
