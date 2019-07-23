<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}

class ControllerApiFetchFetchTopResult extends AController {

	public $data = array ();
	private $error = array ();
	public function main(){
		$res_array = $this->fetchKeywords(true);
		?><table border="1" cellspacing="0" cellpadding="5">
		    <tr>
		        <th>Category Title</th>
		        <th>Products</th>
		    </tr> 
		<?php
	    foreach($res_array as $res_table_value) {
	      $top_products = '';
	      $category_title = '';
	      for ($i=0; $i < count($res_table_value) ; $i++) { 
	      	if($top_products != ''){
	      		$top_products .= ', ';
	      	}
	      	$top_products .= $res_table_value[$i]['name'];
	      	$category_title = $res_table_value[$i]['category_title'];
	      }
	      	?>   
			 <tr>
			 	<td width="20%"><?php echo $category_title; ?></td>
                <td><?php echo $top_products; ?></td>
            </tr>  
		<?php
		}
		?>
		</table> 
		<?php
	}

	
	public function fetchKeywords($preview = FALSE){

		$language_id = (int)$this->config->get('storefront_language_id');
		$sql ="SELECT prod.sku AS title, prod.product_id, cat.category_id, cat_name.name AS category_title FROM " . $this->db->table("products") . " AS prod INNER JOIN " . $this->db->table("product_descriptions") . " AS prod_desc ON prod.product_id = prod_desc.product_id INNER JOIN " . $this->db->table("products_to_categories") . " AS cat ON cat.product_id = prod.product_id INNER JOIN " . $this->db->table("category_descriptions") . " AS cat_name ON cat_name.category_id = cat.category_id WHERE prod.status=1 ORDER BY prod.price DESC  ";
		
	    if ($preview == FALSE){
	    	$sql.= " LIMIT 100";
	    }
	    $result = $this->db->query($sql);
		$res = $result->rows;
		$res_array = array();
		foreach ($res as $res_value) {
			if (count($res_array[$res_value['category_id']]) < 10) {
			  	$res_array[$res_value['category_id']][] = array('prod_id' => $res_value['product_id'],'name' => $res_value['title'],'category_id' => $res_value['category_id'],'category_title' => $res_value['category_title']);
			}
	    }
	    if ($preview == FALSE){		    	 
	    	return json_encode($res_array);
	    }
	    return $res_array;

	} 

	public function searchKeywords(){
		// if(!isset($_GET['term'])) { echo json_encode(array()); exit(); }
        $term =	$this->request->get['term'];
        $keyword_list = fopen('https://docs.google.com/spreadsheets/d/e/2PACX-1vRmykdQpTo49WvEPqkjIWARg0UgxIOpwaTMtIq7ETVrNwHMhviJMPkBTiB81I1lzMxGV8bZmXwRH8S3/pub?gid=1624669221&single=true&output=csv', 'r');
        $matched = array();
        // $i = 0;
        while (($line = fgetcsv($keyword_list)) !== FALSE) {
        	// if($i == 0){
        	// 	$i++;
        	// 	continue;
        	// }
        	$exploded_array = explode(",", $line[0]);
        	foreach ($exploded_array as $key => $value) {
        		// if(stripos($value, $term) > -1){
					$matched[] = $value;
				// }	
        	}
        	// print_r($exploded_array);
		   
		}
		fclose($file);
		echo json_encode($matched); exit();
		// die;
		if (empty($matched)) {
			$keyword_list = $this->fetchKeywords();
			$keyword_list = json_decode($keyword_list, TRUE);
			foreach ($keyword_list as $key => $cat_products) {
			  	foreach($cat_products as $k => $val){
			  		if(stripos($val['category_title'], $term) > -1){
						$matched[] = $val['category_title'];
					}
			  	}
		    }
		}
	}
	function searchDatabase(){
		if(isset($this->request->get['term'])) { 
			$search_term =	$this->request->get['term'];
			$sql = "SELECT name FROM (SELECT DISTINCT(name) FROM " . $this->db->table("product_descriptions") . " WHERE (`name` LIKE '".$search_term."%' OR name LIKE '% ".$search_term."%') UNION SELECT DISTINCT(name) FROM " . $this->db->table("category_descriptions") . " WHERE (`name` LIKE '".$search_term."%' OR name LIKE '% ".$search_term."%')) AS prod LIMIT 20";
			$result = $this->db->query($sql);
			$res = $result->rows;
			$res_array = array();
			foreach ($res as $key => $value) {
				$search_term = strtolower($search_term);
				$searchedPos = stripos($value['name'], $search_term);
				if($searchedPos > -1){
					$wordStart = strrpos($value['name'], " ", (strrpos(substr($value['name'], 0, ($searchedPos + strlen($search_term))), ' ', -1) - strlen($value['name']) - strlen($search_term) - 1));
					$newString = substr($value['name'], $wordStart);
					if(!strpos($newString, ' ', stripos($newString, $search_term))){
						$res_array[] = $newString;
					}else{
						$wordEnd = strpos($newString, ' ', strpos($newString, ' ', stripos($newString, $search_term))+1); 
						if(!$wordEnd){
							$res_array[] = $newString;
						}else{
							$res_array[] = substr($value['name'], $wordStart, $wordEnd+1); 
						}
					}
				}
			}
			$res_array = array_unique($res_array);
			if(!empty($res_array)){
			    echo json_encode($res_array);
			    exit();
			}else{
			  $keyword_list = fopen('https://docs.google.com/spreadsheets/d/e/2PACX-1vRmykdQpTo49WvEPqkjIWARg0UgxIOpwaTMtIq7ETVrNwHMhviJMPkBTiB81I1lzMxGV8bZmXwRH8S3/pub?gid=1624669221&single=true&output=csv', 'r');
		        $matched = array();
		        while (($line = fgetcsv($keyword_list)) !== FALSE) {
		        	$exploded_array = explode(",", $line[0]);
		        	foreach ($exploded_array as $key => $value) {
		        			$matched[] = $value;
		        	}		           
				}
				fclose($file);
				array_shift($matched);
				$matched = array_slice($matched, 0, 10);
				array_unshift($matched, "Search results found in product description may not be exactly what you are looking for, please try these keywords");
				echo json_encode($matched); exit();	 
			}
	    }
	}
	function logKeywords(){
        $term =	$this->request->get['term'];
		$this->loadModel('catalog/ssproduct');
		$insertKeywords =  $this->model_catalog_ssproduct->insertKeywords($term);
		exit();
	}
    function showLog(){
	    $sql = "SELECT * FROM " . $this->db->table("search_log") . "";
	    $result = $this->db->query($sql);
		$res = $result->rows;
		
		?><table border="1" cellspacing="0" cellpadding="5">
		    <tr>
		        <th>Id</th>
		        <th>Search Keyword</th>
		        <th>Created Date</th>
		    </tr> 
		<?php
	    foreach($res as $table_value) {
	      ?>   
			 <tr>
			 	<td><?php echo $table_value['id']; ?></td>
                <td><?php echo $table_value['search_keyword']; ?></td>
                <td><?php echo $table_value['created_on']; ?></td>
            </tr>  
		<?php
		}
		?>
		</table> 
		<?php
		exit();
    }
}
