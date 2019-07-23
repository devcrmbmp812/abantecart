<?php
if (!defined('DIR_CORE')) {
		header('Location: static_pages/');
}

class ControllerUltraSearchUltraSearch extends AController
{
		public function main() {
				//$data['menu'] = $getmenu;
				//$this->view->assign('data', $data);
				$this->processTemplate('blocks/ultra_search/ultra_search.tpl');
				// init controller data
				$this->extensions->hk_UpdateData($this);
		}
}
