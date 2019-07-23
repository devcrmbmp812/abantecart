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
if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

// if(!class_exists('ExtensionFormsManager')){
// 	include('core/forms_manager.php');
// }
include_once('core/custom_adv_order.php');

$controllers = array(
    'admin' => array(
    	'pages/sale/adv_order',
		'responses/listing_grid/adv_order'
    ),
);

$models = array(
    'admin' => array('sale/adv_order'),
);

$templates = array(
    'admin' => array(
    	'common/listing_grid.tpl',
    )
);