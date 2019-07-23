<?php
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

if (!class_exists('ExtensionUltraSearch')) {
    include_once 'core/ultra_search.php';
}

$languages = array('storefront' => array('ultra_search/ultra_search'),
                                    'admin' => array('ultra_search/ultra_search')
);

$models = array(
   'storefront' => array('tool/global_search'),
    'admin' => array( ),
);

$controllers = array(
    'storefront' => array('responses/search_auto/global_search_result',
    'ultra_search/ultra_search'),
    'admin' => array()
);

$templates = array('storefront' => array('common/footer.post.tpl',
                                        'blocks/ultra_search/ultra_search.tpl'),
                                    'admin' => array('common/head.post.tpl')

);
