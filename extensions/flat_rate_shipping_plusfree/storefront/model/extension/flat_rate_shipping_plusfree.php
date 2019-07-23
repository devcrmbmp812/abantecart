<?php
if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
class ModelExtensionFlatRateShippingplusfree extends Model {      
  	public function getQuote($address) {
		
		$this->load->language('flat_rate_shipping_plusfree/flat_rate_shipping_plusfree');
		
		$quote_data = array();

		if ($this->config->get('flat_rate_shipping_plusfree_status')) {
			$query = $this->db->query("SELECT * FROM " . $this->db->table("locations") . " ORDER BY name");
		
			foreach ($query->rows as $result) {			



			if ($this->config->get('flat_rate_shipping_plusfree_' . $result['location_id'] . '_status')) {
   					$query2 = $this->db->query("SELECT * FROM " . $this->db->table("zones_to_locations") . " WHERE location_id = '" . (int)$result['location_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");				
					
					if ($query2->num_rows) {			
						$geo_zone_free_total=$this->config->get('flat_rate_shipping_plusfree_' . $result['location_id'] . '_freecost');
						$tax_id = $this->config->get('flat_rate_shipping_plusfree_' . $result['location_id'] . '_tax_id');
						$cost = $this->config->get('flat_rate_shipping_plusfree_' . $result['location_id'] . '_rate');
						$geo_zone_id=$result['location_id'];
						$status = true;
						break;
					    $status = TRUE;
   					} else {
       					$status = FALSE;
   					}
				
				} else {
					$status = FALSE;
				}


			}
			
			
			if ($status) {
				
				if ($this->cart->getSubTotal() >= $geo_zone_free_total) {
				$status = FALSE;
				}

			}

		
			if (isset($this->session->data['zone_id'])) {
					$zone_id = $this->session->data['zone_id'];			
				} else {
					$zone_id = $address['zone_id'];
				}
		
			if ( $zone_id ) {
			
				$language_id = $this->language->getLanguageID();
				$default_lang_id = $this->language->getDefaultLanguageID();
			
				$query = $this->db->query("SELECT z.*, COALESCE(zd1.name,zd2.name) as name 
										FROM " . $this->db->table("zones") . " z
										LEFT JOIN " . $this->db->table("zone_descriptions") . " zd1
										ON (z.zone_id = zd1.zone_id AND zd1.language_id = '" . (int)$language_id . "')		
										LEFT JOIN " . $this->db->table("zone_descriptions") . " zd2
										ON (z.zone_id = zd2.zone_id AND zd2.language_id = '" . (int)$default_lang_id . "')		
										WHERE z.zone_id = '" . (int)$zone_id . "' AND status = '1'");
						
				$zone_info2=$query->row;
				//$this->loadModel('localisation/zone','storefront');
				//$zone_info2 = $this->model_localisation_zone->getZone($zone_id);
			}		
		
			$zone2 = '';
			if ($zone_info2) {
				$zone2 = $zone_info2['name'];
			}
	
		
		
		
		$method_data = array();

		if ($status) {
			$quote_data = array();
	
      		$quote_data['flat_rate_shipping_plusfree'] = array(
        		'id'         => 'flat_rate_shipping_plusfree.flat_rate_shipping_plusfree',
        		'title'        => sprintf($this->language->get('text_description'),$this->currency->format($geo_zone_free_total),$zone2),
        		'cost'         => $cost,
        		'tax_class_id' => $tax_id,
				'text'         => $this->currency->format($this->tax->calculate($cost,$tax_id , $this->config->get('config_tax')))
      		);

      		$method_data = array(
        		'id'       => 'flat_rate_shipping_plusfree',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('flatplusfree_' . $geo_zone_id . '_sort_order'),
        		'error'      => false
      		);
		}else{
			if ($geo_zone_id ){
			$quote_data = array();
			
      		$quote_data['free'] = array(
        		'id'         => 'flat_rate_shipping_plusfree.free',
        		'title'        => sprintf($this->language->get('text_description_free'),$this->currency->format($geo_zone_free_total),$zone2),
        		'cost'         => 0.00,
        		'tax_class_id' => 0,
				'text'         => $this->currency->format(0.00)
      		);

      		$method_data = array(
        		'id'       => 'free',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('flatplusfree_' . $geo_zone_id . '_sort_order'),
        		'error'      => false
      		);
			
			}
		}
	
		return $method_data;
	}
	}}