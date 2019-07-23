<?php
/* Main extension driver containing details about extension files */

if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

if(!class_exists('ExtensionJivoChat')){
	include('core/jivo_chat.php');
}


$languages = array(
     'admin' => array('jivo_chat/jivo_chat'),
     'storefront' => array(),
);

$templates = array(
    'storefront' => array(
		'pages/jivo_chat/jivo_chat_outside.tpl',
		'common/footer.post.tpl',
    ),
	'admin' => array(),
);
