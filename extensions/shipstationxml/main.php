<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}

$controllers = array(
    'storefront' => array(
        'pages/shipstation/xml_feed'),
    'admin' => array());

$models = array(
    'storefront' => array(
        'shipstation/xml_orders'),
    'admin' => array());

$templates = array(
    'storefront' => array(),
    'admin' => array());

$languages = array(
    'storefront' => array(),
    'admin' => array(
        'english/shipstationxml/shipstationxml'));

