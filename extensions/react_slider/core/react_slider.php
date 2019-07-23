<?php


if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionReactSlider extends Extension
{
    /**************************************************/
    /*public function saveCSS($rules, $file)
    {
        $this->baseObject->log->write(' run dev test 2');
        $text = '';
        foreach ($rules as $selectorname => $variousrules) {
            $text .= $selectorname.' {';
            foreach ($variousrules as $key => $value) {
                $text .= $key.': '.$value.';'. PHP_EOL;
            }
            $text .= '}'. PHP_EOL;
        }
        //return file_put_contents($file, $text);
        return $text;
    }*/

    public function onControllerCommonPage_UpdateData()
    {
        $this->baseObject->loadLanguage('react_slider/react_slider');
        /*if (IS_ADMIN && $this->baseObject->request->get['extension']=='react_slider') {
            $custom_css_local = DIR_EXT.'react_slider/storefront/view/default/css/react_slider_dev.css';
            $rules['#header .header-nav'] = array(
                "background" => ''
            );
            $rules['#header a:not(.dropdown-item), .header-nav .expand-more, #currency-selector-label'] = array(
                "color" => ' '
            );
            //$this->baseObject->log->write(' run dev test 1');
            $this->saveCSS($rules, $custom_css_local);
        }*/


        if (!IS_ADMIN ) {
            $this->baseObject->document->addStyle(
                array(
                'href' => $this->baseObject->view->templateResource('/css/react_slider.css') ,
                'rel' => 'stylesheet',
                'media' => 'screen',

                )
            );
            
            $this->baseObject->document->addScript($this->baseObject->view->templateResource('/js/react.min.js'));
            $this->baseObject->document->addScript($this->baseObject->view->templateResource('/js/react-dom.min.js'));
            $this->baseObject->document->addScript($this->baseObject->view->templateResource('/js/index.min.js'));
            $this->baseObject->document->addScript($this->baseObject->view->templateResource('/js/babel.min.js'));

        }






        if (IS_ADMIN && $this->baseObject->request->get['extension']=='react_slider') {
            $this->baseObject->document->addScript(DIR_EXTENSIONS . 'react_slider'.DIR_EXT_STORE.'view/default/js/bootstrap-colorpicker.js');
            $this->baseObject->document->addScript(DIR_EXTENSIONS . 'react_slider'.DIR_EXT_STORE.'view/default/js/colorpicker-connect.js');



            $this->baseObject->document->addStyle(
                array(
                'href' => DIR_EXTENSIONS . 'react_slider'.DIR_EXT_STORE.'view/default/js/bootstrap-colorpicker.css',
                'rel' => 'stylesheet',
                'media' => 'screen',
                )
            );
        }

    }



    /******************/
}
