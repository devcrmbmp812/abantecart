<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}


if(!class_exists('')){
    include_once('core/smart_search.php');
}
$controllers = array(
    'storefront' => array(
        'pages/product/searc',
        'api/fetch/fetch_top_result',
        'pages/product/search'),
    'admin' => array());

$models = array(
    'storefront' => array(
        'catalog/ssproduct'),
    'admin' => array());

$templates = array(
    'storefront' => array('common/footer.post.tpl'),
    'admin' => array());

$languages = array(
    'storefront' => array(),
    'admin' => array(
        'english/smart_search/smart_search'));

