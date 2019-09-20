<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 9/12/2019
 * Time: 4:52 PM
 */
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
    header ( 'Location: static_pages/' );
}
class ControllerResponsesListingGridCouponDetail extends AController {
    public $data = array();
    public $error;
    public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->loadLanguage('sale/coupon_detail');
        $this->loadModel('sale/coupon');

        $limit = $this->request->post['rows']; // get how many rows we want to have into the grid

        $total = $this->model_sale_coupon->getTotalCouponDetails(array());
        if( $total > 0 ) {
            $total_pages = ceil($total/$limit);
        } else {
            $total_pages = 0;
        }

        $response = new stdClass();
        $response->page = $this->request->post['page'];
        $response->total = $total_pages;
        $response->records = $total;

        $results = $this->model_sale_coupon->getCouponDetails(array('content_language_id' => $this->language->getContentLanguageID()));
        $i = 0;
        foreach ($results as $result) {
            // check date range

            $response->rows[$i]['id'] = $result['customer_code_id'];
            $response->rows[$i]['cell'] = array(
                $result['coupon_name'],
                $result['customer_name'],
                $result['order_id'],
                $result['date_used'],
            );
            $i++;
        }
        $this->data['response'] = $response;
        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['response']));
    }
}
