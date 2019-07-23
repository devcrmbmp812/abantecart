<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}


if(!class_exists('ExtensionCustomProductShow')){
    include_once('core/product_show.php');
}
$controllers = array(
    'storefront' => array(),
    'admin' => array());

$models = array(
    'storefront' => array(),
    'admin' => array());

$templates = array(
    'storefront' => array(),
    'admin' => array());

$languages = array(
    'storefront' => array(),
    'admin' => array(
        'english/custom_product_show/custom_product_show'));

