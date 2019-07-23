<?php


if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionUltraSearch extends Extension
{
    /**************************************************/

    public function onControllerCommonPage_UpdateData()
    {


      if (!IS_ADMIN)
      {
        $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ultra_search'.DIR_EXT_STORE.'view/default/js/ajax-chosen.js');

          $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ultra_search'.DIR_EXT_STORE.'view/default/js/chosen.jquery.js');

          $this->baseObject->document->addStyle(
      			array(
      				 'href' => DIR_EXTENSIONS . 'ultra_search'.DIR_EXT_STORE.'view/default/css/chosen.bootstrap.css',
      				 'rel' => 'stylesheet',
      				 'media' => 'screen',
      			)
      		);
      }


      if (IS_ADMIN && $this->baseObject->request->get['extension']=='ultra_search')
      {
        $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ultra_search'.DIR_EXT_STORE.'view/default/js/bootstrap-colorpicker.js');
        $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ultra_search'.DIR_EXT_STORE.'view/default/js/colorpicker-connect.js');

        $this->baseObject->document->addStyle(
    			array(
    				 'href' => DIR_EXTENSIONS . 'ultra_search'.DIR_EXT_STORE.'view/default/js/bootstrap-colorpicker.css',
    				 'rel' => 'stylesheet',
    				 'media' => 'screen',
    			)
    		);
      }


    }


    public function onControllerCommonFooter_UpdateData() {
      if (!IS_ADMIN) {
      $language_id = (int)$language_id;
  		if (!$language_id){
  			$language_id = (int)$this->baseObject->language->getLanguageID();
  		}
      $this->baseObject->loadLanguage('ultra_search/ultra_search');


      if ($_SERVER['HTTPS']) {
          $protocol = 'https';
      } else {
          $protocol = 'http';
      }

      $search_auto = $this->baseObject->html->getSecureURL('search_auto/global_search_result/suggest');
      $url = parse_url($search_auto);
      if($url['scheme'] != $protocol){
         $search_auto = str_replace($url['scheme'], $protocol, $search_auto );
      }

      $ultra_search_text_oops = $this->baseObject->language->get('ultra_search_text_oops');
      $ultra_search_text_search = $this->baseObject->language->get('ultra_search_text_search');
      $ultra_search_text_matches = $this->baseObject->language->get('ultra_search_text_matches');
      //post var to script in footer

      $this->baseObject->view->assign('ultra_search_text_oops', $ultra_search_text_oops);
      $this->baseObject->view->assign('ultra_search_text_search', $ultra_search_text_search);
      //$this->baseObject->view->assign('ultra_search_text_matches', $ultra_search_text_matches);
      $this->baseObject->view->assign('search_auto', $search_auto);

      if (strlen($this->baseObject->config->get('ultra_search_search_id')) > 2) {
        $ultra_search_search_id = $this->baseObject->config->get('ultra_search_search_id');
      } else {
        $ultra_search_search_id = 'filter_keyword';
      }
      $this->baseObject->view->assign('ultra_search_search_id', $ultra_search_search_id);

      if ((bool)($this->baseObject->config->get('ultra_search_new_window'))) {
        $ultra_search_new_window = $this->baseObject->config->get('ultra_search_new_window');
      } else {
        $ultra_search_new_window = 0;
      }

      $this->baseObject->view->assign('ultra_search_new_window', $ultra_search_new_window);
    }
    }

}
