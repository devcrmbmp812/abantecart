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
if (!defined('DIR_CORE') || !IS_ADMIN){
    header('Location: static_pages/');
}
/** @noinspection PhpUndefinedClassInspection */
class ControllerResponsesSaleCode extends AController{
    public $error = array ();
    public $data = array ();
    /**
     * @var AAttribute_Manager
     */
    protected $attribute_manager;
    public function codes(){
        $codes_data = array ();
        $post =& $this->request->post;
        $get =& $this->request->get;
        $exclude = (array)$post['exclude'];
        if(isset($get['exclude'])){
            $get['exclude'] = (array)$get['exclude'];
            $exclude = array_merge($get['exclude'],$exclude);
        }
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->loadModel('sale/coupon');
        if (isset($post['code'])){
            $codes = $post['code'];
            foreach ($codes as $code_id){
                $code_info = $this->model_sale_coupon->getCode($code_id);
                if ($code_info){
                    $codes_data[] = array (
                        'id'         => $code_info['id'],
                        'name'       => $code_info['code_name'],
                    );
                }
            }
        } else if (isset($post['term'])){
            $filter = array ('limit'               => 20,
                'content_language_id' => $this->language->getContentLanguageID(),
                'filter'              => array (
                    'keyword' => $post['term'],
                    'match'   => 'all',
                    'exclude' => array('id' => $exclude)
                ));
            //if need to show only available codes
            if($this->request->post['filter']=='enabled_only'){
                $filter['filter']['status'] = 1;
                $filter['subsql_filter'] = 'date_available<=NOW()';
            }
            $codes = $this->model_sale_coupon->getCodes($filter);

            $code_ids = array ();
            foreach ($codes as $result){
                $code_ids [] = (int)$result['id'];
            }

            foreach ($codes as $code_data){

                $codes_data[] = array (
                    'id'         => $code_data['id'],
                    'name'       => $code_data['code_name']
                );
            }
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($codes_data));
    }

    public function select_codes() {
        $post =& $this->request->post;
        if(isset($post['code_quantity'])) {
            $this->extensions->hk_InitData($this, __FUNCTION__);
            $this->loadModel('sale/coupon');
            $codes = $this->model_sale_coupon->getCodeWithQuantity($post['code_quantity']);
            $codes_data = array();
            if (isset($codes) && count($codes)){
                foreach ($codes as $code_data){

                    $codes_data[] = array (
                        'id'         => $code_data['id'],
                        'name'       => $code_data['code_name']
                    );
                }
            }
            $this->extensions->hk_UpdateData($this, __FUNCTION__);
            $this->load->library('json');
            $this->response->addJSONHeader();
            $this->response->setOutput(AJson::encode($codes_data));
        } else if (isset($post['code_id'])) {
            $this->extensions->hk_InitData($this, __FUNCTION__);
            $this->loadModel('sale/coupon');
            $code_details = $this->model_sale_coupon->getCodeDetailsWithCodeId($post['code_id']);
            $code_detail_data = array();
            $code_detail_data[] = array (
                'coupon_id'         => $code_details['coupon_id'],
                'is_used'       => $code_details['is_used'],
                'start_date'       => $code_details['start_date'],
                'end_date'       => $code_details['end_date'],
            );
            $this->extensions->hk_UpdateData($this, __FUNCTION__);
            $this->load->library('json');
            $this->response->addJSONHeader();
            $this->response->setOutput(AJson::encode($code_detail_data));
        }

    }
}