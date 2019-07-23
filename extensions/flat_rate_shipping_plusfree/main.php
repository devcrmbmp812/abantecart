<?php

if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

$controllers = array(
    'storefront' => array(),
    'admin' => array( 'pages/extension/flat_rate_shipping_plusfree' ),
);

$models = array(
    'storefront' => array( 'extension/flat_rate_shipping_plusfree' ),
    'admin' => array( ),
);

$languages = array(
    'storefront' => array(
	    'flat_rate_shipping_plusfree/flat_rate_shipping_plusfree'),
    'admin' => array(
        'flat_rate_shipping_plusfree/flat_rate_shipping_plusfree'));

$templates = array(
    'storefront' => array(),
    'admin' => array('pages/extension/flat_rate_shipping_plusfree.tpl'));