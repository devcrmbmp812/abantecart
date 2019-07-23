<?php
    if ( !defined ( 'DIR_CORE' ) ) {
        header ( 'Location: static_pages/' );
    }
/**
 * Class ExtensionFormsManager
 * @property ALanguageManager $language
 * @property AHtml $html
 * @property ARequest $request
 */
class ExtensionAdvOrder extends Extension {

    public $errors = array();
    public $data = array();
    protected $registry;

    public function  __construct() {
        $this->registry = Registry::getInstance();
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function onControllerResponsesListingGridBlocksGrid_UpdateData(){
	}

	public function onControllerPagesDesignBlocks_InitData() {
	}

	public function onControllerPagesDesignBlocks_UpdateData() {
	}


	public function onControllerPagesExtensionBannerManager_UpdateData() {
	}


	public function onControllerResponsesCommonTabs_InitData() {
	}

}