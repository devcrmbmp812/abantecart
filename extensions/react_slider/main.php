<?php
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

if (!class_exists('ExtensionReactSlider')) {
    include_once 'core/react_slider.php';
}

$languages = array(
    'storefront' => array('react_slider/react_slider'),
        'admin' => array('react_slider/react_slider')
);

$controllers = array(
    'storefront' => array(),
    'admin' => array()
);

$templates = array('storefront' => array(
    'blocks/react_slider/react_slider.tpl'),
    'admin' => array('common/head.post.tpl')

);
