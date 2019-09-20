<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 9/12/2019
 * Time: 5:27 PM
 */
if (!defined('DIR_CORE') || !IS_ADMIN) {
    header('Location: static_pages/');
}
/**
 * Class ModelSaleCoupon
 */
class ModelSaleCouponDetail extends Model {
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
    public function getTotalCouponDetails($data){
        return $this->getCouponDetails($data, 'total_only');
    }
}