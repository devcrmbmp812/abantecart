<?php
/* Hooks */
if ( !defined ( 'DIR_CORE' )) {
    header ( 'Location: static_pages/' );
}

class ExtensionJivoChat extends Extension {

	public $data = array();

	public function  __construct()
	{ $this->registry  = Registry::getInstance(); }

	public function onControllerPagesProductProduct_InitData()
	{
			//$this->baseObject->loadLanguage('jivo_chat/jivo_chat');


		//	$data['jivo_chat_code'] = html_entity_decode($this->baseObject->config->get('jivo_chat_code'));
				$data['page_a'] = $this->baseObject->config->get('page_a');
					$data['hide_mobile'] = html_entity_decode($this->baseObject->config->get('hide_mobile'));



	if( $this->baseObject->config->get('page_a') == '1' ){
	$view = new AView($this->registry, 0);
	$view->batchAssign($data);
	$this->baseObject->view->addHookVar('product_features', $view->fetch('pages/jivo_chat/jivo_chat_outside.tpl'));
	}



	}


}
