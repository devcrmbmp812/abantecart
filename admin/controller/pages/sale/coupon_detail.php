<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 9/12/2019
 * Time: 3:59 PM
 */
if (!defined('DIR_CORE') || !IS_ADMIN) {
    header('Location: static_pages/');
}
class ControllerPagesSaleCouponDetail extends AController {
    public function main() {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->initBreadcrumb(array(
            'href' => $this->html->getSecureURL('index/home'),
            'text' => $this->language->get('text_home'),
            'separator' => FALSE
        ));

        $this->document->addBreadcrumb(array(
            'href' => $this->html->getSecureURL('sale/coupon'),
            'text' => $this->language->get('heading_title'),
            'separator' => ' :: ',
            'current'	=> true
        ));

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $grid_settings = array(
            //id of grid
            'table_id' => 'coupon_grid',
            // url to load data from
            'url' => $this->html->getSecureURL('listing_grid/coupon_detail'),
            'sortname' => 'name',
            'sortorder' => 'asc',
            'multiselect' => 'true',
            'columns_search' => true,
        );

        $grid_settings['colNames'] = array(
            $this->language->get('column_name'),
            $this->language->get('column_customer_name'),
            $this->language->get('column_order_id'),
            $this->language->get('column_date_used'),
            $this->language->get('column_optional_time'),
        );

        $grid_settings['colModel'] = array(
            array(  'name' => 'coupon_name',
                'index' => 'coupon_name',
                'width' => 160,
                'align' => 'left',
                'search' => true),

            array(  'name' => 'customer_name',
                'index' => 'customer_name',
                'width' => 80,
                'align' => 'center',
                'search' => true),

            array(  'name' => 'order_id',
                'index' => 'order_id',
                'width' => 80,
                'align' => 'center',
                'search' => false),

            array(  'name' => 'date_used',
                'index' => 'date_used',
                'width' => 80,
                'align' => 'center',
                'search' => false),

            array(  'name' => 'optional_time',
                'index' => 'optional_time',
                'width' => 80,
                'align' => 'center',
                'search' => false),

        );

        $form = new AForm();
        $form->setForm(array(
            'form_name' => 'coupon_grid_search',
        ));

        $grid_search_form = array();
        $grid_search_form['id'] = 'coupon_grid_search';
        $grid_search_form['form_open'] = $form->getFieldHtml(array(
            'type' => 'form',
            'name' => 'coupon_grid_search',
            'action' => '',
        ));

        $grid_settings['search_form'] = true;

        $grid = $this->dispatch('common/listing_grid', array($grid_settings));
        $this->view->assign('listing_grid', $grid->dispatchGetOutput());

        $this->document->setTitle($this->language->get('heading_title'));
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

        $this->processTemplate('pages/sale/coupon_detail.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}
