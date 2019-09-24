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
if (!defined('DIR_CORE') || !IS_ADMIN) {
	header('Location: static_pages/');
}

/**
 * Class ModelSaleCoupon
 */
class ModelSaleCoupon extends Model{
	/**
	 * @param array $data
	 * @return int
	 */
	public function addCoupon($data){
		if (has_value($data['date_start'])) {
			$data['date_start'] = "DATE('" . $data['date_start'] . "')";
		} else {
			$data['date_start'] = "NULL";
		}

		if (has_value($data['date_end'])) {
			$data['date_end'] = "DATE('" . $data['date_end'] . "')";
		} else {
			$data['date_end'] = "NULL";
		}

		$codes_selected_array = json_decode(html_entity_decode($data['selected_codes']));
		$codes_name_array = array();
        foreach ($codes_selected_array as $key => $value) {
            $codes_name_array [] = $value->name;
		}

		$code_name_array = [];
		foreach ($data['code'] as $k => $val) {
            $code_name_array [] = $this->getCode($val)['code_name'];
        }

		$this->db->query("INSERT INTO " . $this->db->table("coupons") . " 
							SET code = '" . implode( ",", $codes_name_array ) . "',
								discount = '" . (float)$data['discount'] . "',
								type = '" . $this->db->escape($data['type']) . "',
								code_quantity = '" . $data['code_quantity'] . "',
								total = '" . (float)$data['total'] . "',
								logged = '" . (int)$data['logged'] . "',
								shipping = '" . (int)$data['shipping'] . "',
								date_start = " . $data['date_start'] . ",
								date_end = " . $data['date_end'] . ",
								uses_total = '" . (int)$data['uses_total'] . "',
								uses_customer = '" . (int)$data['uses_customer'] . "',
								status = '" . (int)$data['status'] . "',
								date_added = NOW()");
		$coupon_id = $this->db->getLastId();

		foreach ($data['coupon_description'] as $language_id => $value) {
			$this->language->replaceDescriptions(
					'coupon_descriptions',
					array ('coupon_id' => (int)$coupon_id),
					array ($language_id => array (
									'name'        => $value['name'],
									'description' => $value['description']
							)
					));
		}
		if (isset($data['coupon_product'])) {
			foreach ($data['coupon_product'] as $product_id) {
				$this->db->query("INSERT INTO " . $this->db->table("coupons_products") . " 
									SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['selected_codes'])) {
            foreach ($codes_selected_array as $key => $value) {
                $code_id = $value->id;
                $code_name = $value->name;
                $this->db->query("INSERT INTO " . $this->db->table("coupon_code") . " 
									SET coupon_id = '" . (int)$coupon_id . "', code_id = '" . (int)$code_id. "', code_name = '". $code_name ."', start_date = ".
                    $data['date_start']. ", end_date = ". $data['date_end']);

                $this->db->query("UPDATE ". $this->db->table("codes") . "
                                    SET effective = '1' WHERE id = '".$code_id."'");
            }
        }
		return $coupon_id;
	}

	/**
	 * @param int $coupon_id
	 * @param array $data
	 */
	public function editCoupon($coupon_id, $data){
		if (has_value($data['date_start'])) {
			$data['date_start'] = "DATE('" . $data['date_start'] . "')";
		} else {
			if (isset($data['date_start'])) {
				$data['date_start'] = 'NULL';
			}
		}

		if (has_value($data['date_end'])) {
			$data['date_end'] = "DATE('" . $data['date_end'] . "')";
		} else {
			if (isset($data['date_end'])) {
				$data['date_end'] = 'NULL';
			}
		}

		$coupon_table_fields = array (
				'code',
				'code_quantity',
				'discount',
				'type',
				'total',
				'logged',
				'shipping',
				'date_start',
				'date_end',
				'uses_total',
				'uses_customer',
				'status'
		);
		$update = array ();
		foreach ($coupon_table_fields as $f) {
			if (isset($data[$f])) {
				if (!in_array($f, array ('date_start', 'date_end', 'code'))) {
					$update[] = $f . " = '" . $this->db->escape($data[$f]) . "'";
				} else if (in_array($f, array ('code'))){
				    /*Start of getting previous code of coupon*/
                    $coupon_code_data = array ();

                    $query = $this->db->query("SELECT *
									FROM " . $this->db->table("coupons") . " 
									WHERE coupon_id = '" . (int)$coupon_id . "'");

                    foreach ($query->rows as $result) {
                        $coupon_code_data_string = $result['code'];
                        $coupon_code_data = explode(',', $coupon_code_data_string);
                    }
                    /*End of getting coupon code*/
                    $codes_selected_array = json_decode(html_entity_decode($data['selected_codes']));
                    $codes_name_array = array();
                    foreach ($codes_selected_array as $key => $value) {
                        $codes_name_array [] = $value->name;
                    }
                    $merged_codename_array = array_merge($coupon_code_data,$codes_name_array);
                    $update[] = $f . " = '" . implode( ",", $merged_codename_array ) . "'";
                } else {
					$update[] = $f . " = " . $data[$f] . "";
				}
			}
		}
		if (!empty($update)) {
		    $update[1] = "code_quantity = '".count(explode(',', $update[0]))."'";
			$this->db->query("UPDATE " . $this->db->table("coupons") . " 
							SET " . implode(',', $update) . "
							WHERE coupon_id = '" . (int)$coupon_id . "'");

		}

		if (!empty($data['coupon_description'])) {
			foreach ($data['coupon_description'] as $language_id => $value) {
				$update = array ();
				if (isset($value['name'])) {
					$update["name"] = $value['name'];
				}
				if (isset($value['description'])) {
					$update["description"] = $value['description'];
				}
				if (!empty($update)) {
					$this->language->replaceDescriptions('coupon_descriptions',
							array ('coupon_id' => (int)$coupon_id),
							array ($language_id => array (
											'name'        => $value['name'],
											'description' => $value['description']
									)
							));
				}
			}
		}
	}

	/**
	 * @param int $coupon_id
	 * @param array $data
	 */
	public function editCouponProducts($coupon_id, $data){
		$this->db->query("DELETE FROM " . $this->db->table("coupons_products") . " 
						  WHERE coupon_id = '" . (int)$coupon_id . "'");
		if (isset($data['coupon_product'])) {
			foreach ($data['coupon_product'] as $product_id) {
				$this->db->query("INSERT INTO " . $this->db->table("coupons_products") . " 
									SET coupon_id = '" . (int)$coupon_id . "',
										product_id = '" . (int)$product_id . "'");
			}
		}
	}

    /**
     * @param int $coupon_id
     * @param array $data
     */
	public function editCouponCodes($coupon_id, $data) {
        $codes_selected_array = json_decode(html_entity_decode($data['selected_codes']));
        foreach ($codes_selected_array as $key => $value) {
            $code_id = $value->id;
            $code_name = $value->name;
            $this->db->query("INSERT INTO " . $this->db->table("coupon_code") . " 
									SET coupon_id = '" . (int)$coupon_id . "',
									    code_name = '".$code_name."',
									    start_date = '".$data['date_start']."',
									    end_date = '".$data['date_end']."',
										code_id = '" . (int)$code_id . "'");
            $this->db->query("UPDATE ". $this->db->table("codes") . "
                                    SET effective = '1' WHERE id = '".$code_id."'");
        }
    }

	/**
	 * @param int $coupon_id
	 */
	public function deleteCoupon($coupon_id){
		$this->db->query("DELETE FROM " . $this->db->table("coupons") . " WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("coupon_descriptions") . " WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table("coupons_products") . " WHERE coupon_id = '" . (int)$coupon_id . "'");
	}

	/**
	 * @param int $coupon_id
	 * @return array
	 */
	public function getCouponByID($coupon_id){
		$query = $this->db->query("SELECT DISTINCT * 
									FROM " . $this->db->table("coupons") . " 
									WHERE coupon_id = '" . (int)$coupon_id . "'");
		return $query->row;
	}

	/**
	 * @param array $data
	 * @param string $mode
	 * @return array|int
	 */
	public function getCoupons($data = array (), $mode = 'default'){
		if (!empty($data['content_language_id'])) {
			$language_id = ( int )$data['content_language_id'];
		} else {
			$language_id = (int)$this->config->get('storefront_language_id');
		}

		//Prepare filter config
		$filter_params = array ('status' => 'c.status');
		//Build query string based on GET params first
		$filter_form = new AFilter(array ('method' => 'get', 'filter_params' => $filter_params));
		//Build final filter
		$grid_filter_params = array ('name' => 'cd.name', 'code' => 'c.code');
		$filter_grid = new AFilter(array (
				'method'                   => 'post',
				'grid_filter_params'       => $grid_filter_params,
				'additional_filter_string' => $filter_form->getFilterString()
		));
		$data = array_merge($filter_grid->getFilterData(), $data);

		if ($mode == 'total_only') {
			$total_sql = 'count(*) as total';
		} else {
			$total_sql = "c.coupon_id, cd.name, c.code, c.discount, c.date_start, c.date_end, c.status ";
		}

		$sql = "SELECT " . $total_sql . " 
				FROM " . $this->db->table("coupons") . " c
				LEFT JOIN " . $this->db->table("coupon_descriptions") . " cd
					ON (c.coupon_id = cd.coupon_id AND cd.language_id = '" . $language_id . "')
				WHERE 1=1 ";

		if (!empty($data['search'])) {
			$sql .= " AND " . $data['search'];
		}
		if (!empty($data['subsql_filter'])) {
			$sql .= " AND " . $data['subsql_filter'];
		}

		//If for total, we done building the query
		if ($mode == 'total_only') {
			$query = $this->db->query($sql);
			return $query->row['total'];
		}

		$sort_data = array (
				'name'       => 'cd.name',
				'code'       => 'c.code',
				'discount'   => 'c.discount',
				'date_start' => 'c.date_start',
				'date_end'   => 'c.date_end',
				'status'     => 'c.status'
		);

		if (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY cd.name";
		}

		if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;
	}

	/**
	 * @param array $data
	 * @return int
	 */
	public function getTotalCoupons($data){
		return $this->getCoupons($data, 'total_only');
	}

	/**
	 * @param int $coupon_id
	 * @return array
	 */
	public function getCouponDescriptions($coupon_id){
		$coupon_description_data = array ();

		$query = $this->db->query("SELECT *
									FROM " . $this->db->table("coupon_descriptions") . " 
									WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_description_data[$result['language_id']] = array (
					'name'        => $result['name'],
					'description' => $result['description']
			);
		}

		return $coupon_description_data;
	}

	/**
	 * @param int $coupon_id
	 * @return array
	 */
	public function getCouponProducts($coupon_id){
		$coupon_product_data = array ();

		$query = $this->db->query("SELECT *
									FROM " . $this->db->table("coupons_products") . " 
									WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_product_data[] = $result['product_id'];
		}

		return $coupon_product_data;
	}

    /**
     * @param int $coupon_id
     * @return array
     */
    public function getCouponCode($coupon_id){
        $coupon_code_data = array ();

        $query = $this->db->query("SELECT *
									FROM " . $this->db->table("coupon_code") . " 
									WHERE coupon_id = '" . (int)$coupon_id . "'");

        foreach ($query->rows as $result) {
            $coupon_code_data [] = $result['code_id'];
        }

        return $coupon_code_data;
    }

    /**
     * @param int $code_id
     * @return array
     */

    public function getCode($code_id){
        $query = $this->db->query("SELECT *	FROM " . $this->db->table("codes") . " p
									WHERE p.id = '" . (int)$code_id . "'");
        return $query->row;
    }

    /**
     * @param array $data
     * @param string $mode
     * @return array|int
     */
    public function getCodes($data = array (), $mode = 'default'){

        if (!empty($data['content_language_id'])){
            $language_id = (int)$data['content_language_id'];
        } else{
            $language_id = (int)$this->config->get('storefront_language_id');
        }

        if ($data['store_id']){
            $store_id = (int)$data['store_id'];
        } else{
            $store_id = (int)$this->config->get('config_store_id');
        }

        if ($data || $mode == 'total_only'){
            $match = '';
            $filter = (isset($data['filter']) ? $data['filter'] : array ());

            if ($mode == 'total_only'){
                $sql = "SELECT COUNT(*) as total ";
            } else{
                $sql = "SELECT *";
            }
            $sql .= " FROM " . $this->db->table("codes") . " p ";

            if($mode == 'update') {
                $sql .= ' WHERE 1=1 ';
            } else {
                $sql .= ' WHERE 1=1 AND effective = "0"';
            }

            if (!empty($data['subsql_filter'])){
                $sql .= " AND " . $data['subsql_filter'];
            }

            if (isset($filter['match']) && !is_null($filter['match'])){
                $match = $filter['match'];
            }

            if (isset($filter['exclude']['id'])){
                $exclude = $filter['exclude']['id'];
                $excludes = array ();
                if (is_array($exclude)){
                    foreach ($exclude as $ex){
                        $excludes[] = (int)$ex;
                    };
                } elseif ((int)$exclude){
                    $excludes = array ((int)$exclude);
                }

                if ($excludes){
                    $sql .= " AND p.id NOT IN (" . implode(',', $excludes) . ") ";
                }
            }

            if (isset($filter['keyword']) && !is_null($filter['keyword'])){
                $keywords = explode(' ', $filter['keyword']);

                if ($match == 'any'){
                    $sql .= " AND ";
                    foreach ($keywords as $k => $keyword){
                        $sql .= $k > 0 ? " OR" : "";
                        $sql .= " (LCASE(p.code_name) LIKE '%" . $this->db->escape(mb_strtolower($keyword),true) . "%'";
                    }
                    $sql .= " )";
                } else if ($match == 'all'){
                    $sql .= " AND ";
                    foreach ($keywords as $k => $keyword){
                        $sql .= $k > 0 ? " AND" : "";
                        $sql .= " (LCASE(p.code_name) LIKE '%" . $this->db->escape(mb_strtolower($keyword),true) . "%'";
                    }
                    $sql .= " )";
                }
            }

            //If for total, we done building the query
            if ($mode == 'total_only'){
                $query = $this->db->query($sql);
                return $query->row['total'];
            }


            if (isset($data['start']) || isset($data['limit'])){
                if ($data['start'] < 0){
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1){
                    $data['limit'] = 20;
                }
                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
            $query = $this->db->query($sql);
            return $query->rows;
        } else{
            $cache_key = 'product.lang_' . $language_id;
            $product_data = $this->cache->pull($cache_key);
            if ($product_data === false){
                $query = $this->db->query("SELECT *, p.product_id
											FROM " . $this->db->table("products") . " p
											LEFT JOIN " . $this->db->table("product_descriptions") . " pd
												ON (p.product_id = pd.product_id AND pd.language_id = '" . $language_id . "')
											ORDER BY pd.name ASC");
                $product_data = $query->rows;
                $this->cache->push($cache_key, $product_data);
            }

            return $product_data;
        }
    }

    /**
     * @param int $quantity
     * @return array
     */
    public function getCodeWithQuantity($quantity) {
        $query = $this->db->query("SELECT * FROM ".$this->db->table("codes")." WHERE effective = '0' LIMIT 0, ".$quantity);
        return $query->rows;
    }
    /**
     * @param int $code_id
     * @return array
     */
    public function getCodeDetailsWithCodeId($code_id) {
        $return_array = array();
        $result = $this->db->query("SELECT * FROM ".$this->db->table("coupon_code")." WHERE code_id = '".$code_id."'");
        if ($result->num_rows) {// if the code has already selected for one coupon
            $is_used_result = $this->db->query("SELECT * FROM ".$this->db->table("customer_code")." WHERE code_id = '".$code_id."'");
            if ( $is_used_result->num_rows ) {// if the code was used already
                $return_array['is_used'] = 1;
            } else {
                $return_array['is_used'] = 0;
            }
            foreach ($result->rows as $row) {
                $return_array['coupon_id'] = $row['coupon_id'];
                $return_array['start_date'] = $row['start_date'];
                $return_array['end_date'] = $row['end_date'];
            }
        } else {
            $return_array['coupon_id'] = '';
            $return_array['start_date'] = '';
            $return_array['end_date'] = '';
            $return_array['is_used'] = 0;
        }
        return $return_array;
    }

    /**
     * @param array $data
     * @param string $mode
     * @return array|int
     */
    public function getCouponDetails($data = array (), $mode = 'default'){
        if (!empty($data['content_language_id'])) {
            $language_id = ( int )$data['content_language_id'];
        } else {
            $language_id = (int)$this->config->get('storefront_language_id');
        }

        //Build final filter
        $grid_filter_params = array ('coupon_name' => 'cdef.name', 'customer_name' => 'cde.firstname');
        $filter_grid = new AFilter(array (
            'method'                   => 'post',
            'grid_filter_params'       => $grid_filter_params,
        ));
        $data = array_merge($filter_grid->getFilterData(), $data);

        if ($mode == 'total_only') {
            $total_sql = 'count(*) as total';
        } else {
            $total_sql = "c.id AS customer_code_id, cdef.name AS coupon_name, CONCAT(cde.firstname,' ',cde.lastname) AS customer_name, c.order_id, c.date_used";
        }

        $sql = "SELECT " . $total_sql . " 
				FROM " . $this->db->table("customer_code") . " c
				LEFT JOIN " . $this->db->table("coupons") . " cd
					ON c.coupon_id = cd.coupon_id
				LEFT JOIN " . $this->db->table("customers") . " cde
					ON c.customer_id = cde.customer_id
				LEFT JOIN " . $this->db->table("coupon_descriptions") . " cdef
					ON (c.coupon_id = cdef.coupon_id AND cdef.language_id = '" . $language_id . "')
				WHERE 1=1 ";

        if (!empty($data['search'])) {
            $sql .= " AND " . $data['search'];
        }
        if (!empty($data['subsql_filter'])) {
            $sql .= " AND " . $data['subsql_filter'];
        }

        //If for total, we done building the query
        if ($mode == 'total_only') {
            $query = $this->db->query($sql);
            return $query->row['total'];
        }

        $sort_data = array (
            'name'       => 'coupon_name',
            'code'       => 'customer_name',
            'discount'   => 'c.order_id',
            'date_start' => 'c.date_used',
        );

        if (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $sort_data[$data['sort']];
        } else {
            $sql .= " ORDER BY coupon_name";
        }

        if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * @param array $data
     * @return int
     */
    public function getTotalCouponDetails($data){
        return $this->getCouponDetails($data, 'total_only');
    }
}