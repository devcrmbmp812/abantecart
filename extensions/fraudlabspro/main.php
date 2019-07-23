<?php
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}

if(!class_exists('ExtensionFraudLabsPro')){
	include_once('core/fraudlabspro.php');
}

$controllers = array(
	'storefront' => array('responses/extension/fraudlabspro'),
	'admin' => array(),
);

$models = array(
	'storefront' => array('extension/fraudlabspro'),
	'admin' => array(),
);

$languages = array(
	'storefront' => array(
		'fraudlabspro/fraudlabspro'),
	'admin' => array(
		'fraudlabspro/fraudlabspro'));

$templates = array(
	'storefront' => array(
		'responses/fraudlabspro.tpl'),
	'admin' => array());