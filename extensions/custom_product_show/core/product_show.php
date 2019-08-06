<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}

class ExtensionCustomProductShow extends Extension {
	
	protected $registry;
	protected $is_plugin_enabled;
    public function  __construct() {
        $this->registry = Registry::getInstance();
        $this->is_plugin_enabled = $this->registry->get("config")->get("custom_product_show_status");
	}

	/* Creating product folder with html file of description */
	public function onControllerPagesCatalogProduct_InitData() {
		if(!$this->is_plugin_enabled){
			return false;
		}
		if($this->baseObject_method == 'insert'){
			$that = $this->baseObject;
			if(!empty($that->request->post)){
			echo "<pre>";
		      $description = $that->request->post['product_description']['description'];
		      $sku_name = $that->request->post['sku'];
		        if (!is_dir('prod/' . $sku_name)) {
			      mkdir('prod/'. $sku_name , 0777, true);
	    	      chmod('prod/'. $sku_name , 0755);
			      $newFileName = 'prod/'.$sku_name.'/'.$sku_name.'.html';
				  file_put_contents($newFileName,$description);	
				  chmod($newFileName , 0755);
			    }		
			}
		}
    }

    /* Making quantity field to be editable */
    public function onControllerResponsesListingGridProduct_UpdateData() {
    	if(!$this->is_plugin_enabled){
			return false;
		}
    	foreach($this->baseObject->data['response']->rows as $key => $data){
		    $qty = $this->baseObject->data['response']->rows[$key]['cell'][4];
	    	if (is_numeric($qty)) {
			    $this->baseObject->data['response']->rows[$key]['cell'][4] = $this->baseObject->html->buildInput(array(
		            'name'  => 'quantity['.$this->baseObject->data['response']->rows[$key]['id'].']',
		            'value' => $qty,
		        ));
	    	}elseif (is_numeric($this->baseObject->data['response']->rows[$key]['cell'][5])){
	            $qty = $this->baseObject->data['response']->rows[$key]['cell'][5];	    		
		        $this->baseObject->data['response']->rows[$key]['cell'][5] = $this->baseObject->html->buildInput(array(
		            'name'  => 'quantity['.$this->baseObject->data['response']->rows[$key]['id'].']',
		            'value' => $qty,
		        ));	    	    	
	    	}
    	}
	}

}
