<?php

if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}




$file = DIR_EXT . '/custom_product_show/layout.xml';
$layout = new ALayoutManager('default');
$layout->loadXml(array('file' => $file));

$sql = "SELECT abc_product_descriptions.description, abc_products.sku FROM ".$this->db->table("products")."
                INNER JOIN ".$this->db->table("product_descriptions")." 
                WHERE abc_products.product_id = abc_product_descriptions.product_id AND abc_products.sku !=  ''";

$result = $this->db->query($sql);
foreach ($result as $product) {
	foreach ($product as $product_value) {
	        $sku_name = $product_value['sku'];
	    if (!is_dir('prod/' . $sku_name)) {
		    mkdir('prod/'. $sku_name, 0755, true);
	    	chmod('prod/'. $sku_name , 0755);
	    	$description = $product_value['description'];
	    	$newFileName = 'prod/'.$sku_name.'/'.$sku_name.'.html';
			file_put_contents($newFileName,$description);	
			chmod($newFileName , 0755);
		}			
	}
}