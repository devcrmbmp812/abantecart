<?php
if (!defined('DIR_CORE') || IS_ADMIN) {
	header('Location: static_pages/');
}

class ModelToolGlobalSearch extends Model {
	/**
	 * registry to provide access to cart objects
	 *
	 * @var object Registry
	 */
	public $registry;

	/**
	 * array with descriptions of controller for search
	 * @var array
	 */
	public $results_controllers = array(
		"product_categories" => array(
			'alias' => 'category',
			'id' => 'category_id',
			'page' => 'product/category',
			'response' => ''),
		"products" => array(
			'alias' => 'product',
			'id' => 'product_id',
			'page' => 'product/product',
			'response' => ''),
		"reviews" => array(
			'alias' => 'review',
			'id' => 'product_id',
			'page' => 'product/product',
			'response' => ''),
		"manufacturers" => array(
			'alias' => 'brand',
			'id' => 'manufacturer_id',
			'page' => 'product/manufacturer',
			'response' => ''),
		"contents" => array(
			'alias' => 'content',
			'id' => 'content_id',
			'page' => 'content/content',
			'response' => '')
	);



	/**
	 * function returns list of accessible search categories
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function getSearchSources($keyword = '') {
		$search_categories = array();
		// limit of keyword length
		if (mb_strlen($keyword) >= 1) {
			foreach ($this->results_controllers as $k => $item) {
				if(
					($k=="reviews" && $this->config->get('ultra_search_reviews'))
					||
					($k=="manufacturers" && $this->config->get('ultra_search_brands'))
					||
					($k=="product_categories" && $this->config->get('ultra_search_categories'))
					||
					($k=="contents" && $this->config->get('ultra_search_pages'))
					||
					($k=="products" && $this->config->get('ultra_search_products'))
				)
				{$search_categories[$k] = $item['alias'];}
			}
		}
		//$this->log->write(print_r($search_categories, true).' $search_categories');
		return $search_categories;
	}

	/**
	 * function returns total counts of search results
	 *
	 * @param string $search_category
	 * @param string $keyword
	 * @return int
	 */
	public function getTotal($search_category, $keyword) {
		// two variants of needles for search: with and without html-entities
		$needle = $this->db->escape(mb_strtolower(htmlentities($keyword, ENT_QUOTES)),true);
		$needle2 = $this->db->escape(mb_strtolower($keyword),true);

		$language_id = (int)$this->config->get('storefront_language_id');

		$all_languages = $this->language->getActiveLanguages();
		$current_store_id = !isset($this->session->data['current_store_id']) ? 0 : $this->session->data['current_store_id'];
		$search_languages = array();
		if($this->config->get('ultra_search_all_lang')) {
			foreach($all_languages as $l){
				$search_languages[] = (int)$l['language_id'];
			}
		} else {
				$search_languages[] = (int)$language_id;
		}


		$output = array();


		switch ($search_category) {
			case 'product_categories' :
				$sql = "SELECT count(*) as total
						FROM " . $this->db->table("category_descriptions") . " c
						WHERE (LOWER(c.name) like '%" . $needle . "%'
								OR LOWER(c.name) like '%" . $needle2 . "%' )
						AND c.language_id IN (" . (implode(",", $search_languages)) . ");";
				$result = $this->db->query($sql);
				$output = $result->row ['total'];
				break;

			case 'products' :
				$sql = "SELECT a.product_id
						FROM " . $this->db->table("products") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND b.language_id IN (" . (implode(",", $search_languages)) . "))
						WHERE LOWER(a.model) like '%" . $needle . "%' OR LOWER(a.model) like '%" . $needle2 . "%'";

						//slower
						if ($this->config->get('ultra_search_pdesc')) {
							$sql .= " OR LOWER(b.description) like '%" . $needle . "%' OR LOWER(b.description) like '%" . $needle2 . "%'";
						}


						$sql .= "UNION
						SELECT product_id
						FROM " . $this->db->table("product_descriptions") . " pd1
						WHERE ( LOWER(pd1.name) like '%" . $needle . "%' OR LOWER(pd1.name) like '%" . $needle2 . "%' )
							AND pd1.language_id	IN (" . (implode(",", $search_languages)) . ")";



						$sql = "UNION
						SELECT DISTINCT a.product_id
						FROM " . $this->db->table("product_option_value_descriptions") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND b.language_id	IN (" . (implode(",", $search_languages)) . "))
						WHERE ( LOWER(a.name) like '%" . $needle . "%' OR LOWER(a.name) like '%" . $needle2 . "%' )
							AND a.language_id IN (" . (implode(",", $search_languages)) . ")";

						if ($this->config->get('ultra_search_ptags')) {
						$sql .= "UNION
						SELECT DISTINCT a.product_id
						FROM " . $this->db->table("product_tags") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND b.language_id	IN (" . (implode(",", $search_languages)) . "))
						WHERE ( LOWER(a.tag) like '%" . $needle . "%' OR LOWER(a.tag) like '%" . $needle2 . "%' )
							AND a.language_id = " . $language_id;
						}

				$result = $this->db->query($sql);
				if ($result->num_rows) {
					foreach ($result->rows as $row) {
						$output [$row ['product_id']] = 0;
					}
				}
				$output = sizeof($output);
				break;

				//case 'reviews' :
				case ($search_category == 'reviews' && $this->config->get('ultra_search_reviews')):
				$sql = "SELECT DISTINCT product_id
						FROM " . $this->db->table("reviews") . " r
						WHERE (LOWER(`text`) like '%" . $needle . "%')
								OR (LOWER(r.`author`) LIKE '%" . $needle . "%') AND r.`status`=1 ";

				$result = $this->db->query($sql);
				if ($result->num_rows) {
					foreach ($result->rows as $row) {
						$output [$row ['product_id']] = 0;
					}
				}
				$output = sizeof($output);
				break;

			case "manufacturers" :
				$sql = "SELECT count(*) as total
						FROM " . $this->db->table("manufacturers") . "
						WHERE (LOWER(name) like '%" . $needle . "%')";

				$result = $this->db->query($sql);
				$output = $result->row ['total'];

				break;

			case "contents" :
				$sql = "SELECT COUNT( DISTINCT c.content_id) as total
						FROM " . $this->db->table("contents") . " c
						RIGHT JOIN " . $this->db->table("content_descriptions") . " cd
							ON (c.content_id = cd.content_id AND cd.language_id IN (" . (implode(",", $search_languages)) . "))
						WHERE
							(LOWER(`name`) like '%" . $needle . "%')
							OR (LOWER(`title`) like '%" . $needle . "%')
							OR (LOWER(`description`) like '%" . $needle . "%')
							OR (LOWER(`content`) like '%" . $needle . "%')
						";
				$result = $this->db->query($sql);
				$output = $result->row ['total'];
				break;
			default :
				break;
		}

		return $output;
	}

	/**
	 * function returns search results in JSON format
	 *
	 * @param string $search_category
	 * @param string $keyword
	 * @param string $mode
	 * @return array
	 */
	public function getResult($search_category, $keyword, $mode = 'listing') {

		$language_id = (int)$this->config->get('storefront_language_id');
		$itemslimit = (int)$this->config->get('ultra_search_items_limit');
		if($itemslimit < 1) {
			$itemslimit = 3;
		}

		// two variants of needles for search: with and without html-entities
		$needle = $this->db->escape(mb_strtolower(htmlentities($keyword, ENT_QUOTES)));
		$needle2 = $this->db->escape(mb_strtolower($keyword));

		$page = (int)$this->request->get_or_post('page');
		$rows = (int)$this->request->get_or_post('rows');

		if ($page) {
			$page = !$page ? 1 : $page;
			$offset = ($page - 1) * $rows;
			$rows_count = $rows;
		} else {
			$offset = 0;
			$rows_count = $mode == 'listing' ? 10 : $itemslimit;
		}

		$all_languages = $this->language->getActiveLanguages();
		$current_store_id = (int)$this->session->data['current_store_id'];
		$search_languages = array();
		if($this->config->get('ultra_search_all_lang')) {
			foreach($all_languages as $l){
				$search_languages[] = (int)$l['language_id'];
			}
		} else {
				$search_languages[] = (int)$language_id;
		}


		switch ($search_category) {
		case ($search_category == 'product_categories' AND $this->config->get('ultra_search_categories')):
				$sql = "SELECT
							a.category_id, a.status,
							c.category_id,
							c.name as title,
							c.name as text,
							c.meta_keywords as text2,
							c.meta_description as text3,
							c.description as text4
						FROM " . $this->db->table("category_descriptions") . " c
						LEFT JOIN " . $this->db->table("categories") . " a
							ON (c.category_id = a.category_id)
						WHERE (LOWER(c.name) like '%" . $needle . "%'
								OR LOWER(c.name) like '%" . $needle2 . "%' )
							AND c.language_id IN (" . (implode(",", $search_languages)) . ")
							AND a.status=1
						LIMIT " . $offset . "," . $rows_count;
				$result = $this->db->query($sql);
				$result = $result->rows;
				break;


			case ($search_category == 'products' AND $this->config->get('ultra_search_products')):

				$sql = "SELECT a.product_id, a.status, b.name as title, a.model as text";
					if ($this->config->get('ultra_search_pdesc')) {
						$sql .= ", b.blurb as blurb, b.description as description ";
					}
					$sql .=	" FROM " . $this->db->table("products") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND a.status IN (1) AND b.language_id	IN (" . (implode(",", $search_languages)) . "))
						WHERE LOWER(a.model) like '%" . $needle . "%'
						";
				if($needle != $needle2){
					$sql .= " OR LOWER(a.model) like '%" . $needle2 . "%' ";
				}
				$sql .= "
						UNION
						SELECT pd1.product_id, pd1.language_id, pd1.name as title, pd1.name as text";
						if ($this->config->get('ultra_search_pdesc')) {///part1
							$sql .=", pd1.blurb as blurb, pd1.description as description ";
						}
						$sql .= " FROM " . $this->db->table("product_descriptions") . " pd1
						LEFT JOIN " . $this->db->table("products") . " c
						ON c.product_id = pd1.product_id
						WHERE ( LOWER(pd1.name) like '%" . $needle . "%'
						";
				if($needle != $needle2){
					$sql .= " OR LOWER(pd1.name) like '%" . $needle2 . "%' ";
				}

				//slower
				if ($this->config->get('ultra_search_pdesc')) {//part2
					$sql .= " OR LOWER(pd1.description) like '%" . $needle . "%' OR LOWER(pd1.description) like '%" . $needle2 . "%'";
				}

				$sql .= " )
							AND c.status=1
							AND pd1.language_id IN (" . (implode(",", $search_languages)) . ")
						UNION
						SELECT a.product_id, a.language_id, b.name as title, a.name as text";
						if ($this->config->get('ultra_search_pdesc')) {
							$sql .= ", b.blurb as blurb, b.description as description ";
						}
						$sql .= " FROM " . $this->db->table("product_option_descriptions") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND b.language_id	IN (" . (implode(",", $search_languages)) . "))
						WHERE ( LOWER(a.name) like '%" . $needle . "%'
						";
				if($needle != $needle2){
					$sql .= " OR LOWER(a.name) like '%" . $needle2 . "%' ";
				}
				$sql .= ")
							AND a.language_id IN (" . (implode(",", $search_languages)) . ")
						UNION
						SELECT a.product_id, a.language_id, b.name as title, a.name as text";
						if ($this->config->get('ultra_search_pdesc')) {
							$sql .= ", b.blurb as blurb, b.description as description ";
						}
						$sql .= " FROM " . $this->db->table("product_option_value_descriptions") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND b.language_id	IN (" . (implode(",", $search_languages)) . "))
						WHERE ( LOWER(a.name) like '%" . $needle . "%'
						";
				if($needle != $needle2){
					$sql .= " OR LOWER(a.name) like '%" . $needle2 . "%' ";
				}
				$sql .= " )
							AND a.language_id IN (" . (implode(",", $search_languages)) . ")";


				//tag search enabled
				if ($this->config->get('ultra_search_ptags')) {
						$sql .= "
						UNION
						SELECT a.product_id, a.language_id, b.name as title, a.tag as text";
						if ($this->config->get('ultra_search_pdesc')) {
							$sql .= ", b.blurb as blurb, b.description as description ";
						}
						$sql .=" FROM " . $this->db->table("product_tags") . " a
						LEFT JOIN " . $this->db->table("product_descriptions") . " b
							ON (b.product_id = a.product_id AND b.language_id	IN (" . (implode(",", $search_languages)) . "))
						LEFT JOIN " . $this->db->table("products") . " c
							ON c.product_id = a.product_id
						WHERE ( a.tag like '" . $needle . "%'
						";
				if($needle != $needle2){
					$sql .= " OR a.tag like '" . $needle2 . "%' ";
				}
				$sql .= " )
							AND c.status=1
							AND a.language_id IN (" . (implode(",", $search_languages)) . ")
						LIMIT " . $offset . "," . $rows_count;
				}

				$sql = "SELECT f.* FROM (" . $sql . ") f LEFT JOIN " . $this->db->table("products") . " g 
							ON f.product_id = g.product_id order by g.price desc";        //mary add 20190619

				//$this->log->write(print_r($sql, true).' products sql ');

				$result = $this->db->query($sql);

				$table = array();
				if ($result->num_rows) {
					//$this->log->write(print_r($result, true).' products sql $result ');
					foreach ($result->rows as $row) {
						if (!isset($table [$row ['product_id']])) {
							$table [$row ['product_id']] = $row;
							//$table [$row ['product_id']]['image'] = '';
						}
					}
				}
				//$this->log->write(print_r($table, true).' products sql $table ');
				$result = $table;
				break;

			case ($search_category == 'reviews' AND $this->config->get('ultra_search_reviews')):
				$sql = "SELECT review_id, r.`text`, pd.`product_id`, pd.`name` as title
						FROM " . $this->db->table("reviews") . " r
						LEFT JOIN " . $this->db->table("product_descriptions") . " pd
							ON (pd.product_id = r.product_id AND pd.language_id	IN (" . (implode(",", $search_languages)) . "))
						WHERE ( LOWER(r.`text`) LIKE '%" . $needle . "%'
								OR LOWER(r.`author`) LIKE '%" . $needle . "%'
						";
				if($needle != $needle2){
					$sql .= " OR LOWER(r.`text`) LIKE '%" . $needle2 . "%'
							  OR LOWER(r.`author`) LIKE '%" . $needle2 . "%' ";
				}
				$sql .= ") AND r.`status`=1 LIMIT " . $offset . "," . $rows_count;
				$result = $this->db->query($sql);
				$result = $result->rows;
				//$this->log->write(print_r($sql, true).' reviews sql ');
				break;

			case ($search_category == 'manufacturers' AND $this->config->get('ultra_search_brands')):
				$sql = "SELECT manufacturer_id, `name` as text, `name` as title
						FROM " . $this->db->table("manufacturers") . "
						WHERE (LOWER(name) like '%" . $needle . "%' OR LOWER(name) like '%" . $needle2 . "%' )
						LIMIT " . $offset . "," . $rows_count;
				$result = $this->db->query($sql);
				$result = $result->rows;
				break;


			case ($search_category == 'contents' AND $this->config->get('ultra_search_pages')):
				$sql = "SELECT c.content_id, c.status, name as title, name  as text
						FROM " . $this->db->table("contents") . " c
						RIGHT JOIN " . $this->db->table("content_descriptions") . " cd
							ON (c.content_id = cd.content_id AND cd.language_id IN (" . (implode(",", $search_languages)) . "))
						WHERE
							(LOWER(`name`) like '%" . $needle . "%')
							OR (LOWER(`title`) like '%" . $needle . "%')
							OR (LOWER(`description`) like '%" . $needle . "%')
							OR (LOWER(`content`) like '%" . $needle . "%')
							AND c.status=1
						LIMIT " . $offset . "," . $rows_count;
				$result = $this->db->query($sql);
				$result = $result->rows;
				break;

			default :
				$result = array(0 => array("text" => "no results! "));
				break;
		}

		if ($mode == 'listing') {

				$result = $this->_prepareResponse($keyword,
					$this->results_controllers[$search_category]['page'],
					$this->results_controllers[$search_category]['id'],
					$result);

		}
		foreach ($result as &$row) {
			$row['controller'] = $this->results_controllers[$search_category]['page'];

			//shorten text for suggestion
			if ($mode != 'listing') {
				$dec_text = htmlentities($row['text'], ENT_QUOTES);
				$len = mb_strlen($dec_text);
				if( $len > 100 ) {
						$ellipsis = '...';
						$row['text'] = mb_substr($dec_text, 0, 100).$ellipsis;
				}
			}
		}
		$output ["result"] = $result;
		$output ['search_category'] = $search_category;

		return $output;
	}

	/**
	 * function prepares array with search results for json encoding
	 *
	 * @param string $keyword
	 * @param string $rt
	 * @param string|array $key_field(s)
	 * @param array $table
	 * @return array
	 */
	private function _prepareResponse($keyword = '', $rt = '', $key_field = '', $table = array()) {
		$output = array();
		if (!$rt || !$key_field || !$keyword) {
			return null;
		}

		$tmp = array();
		$text = '';
		if ($table && is_array($table)) {

			foreach ($table as $row) {
				//let's extract  and colorize keyword in row
				foreach ($row as $key => $field) {
					$field_decoded = htmlentities($field, ENT_QUOTES);

					// if keyword found
					$pos = mb_stripos($field_decoded, $keyword);
					if (is_int($pos) && $key != 'title') {
						$row ['title'] = '<span class="search_res_title">' . strip_tags($row ['title']) . "</span>";
						$start = $pos < 50 ? 0 : ($pos - 50);
						$keyword_len = mb_strlen($keyword);
						$field_len = mb_strlen($field_decoded);
						$ellipsis = ($field_len - $keyword_len > 10) ? '...' : '';
						// before founded word
						$text .= $ellipsis . mb_substr($field_decoded, $start, $pos);
						// founded word
						$len = ($field_len - ($pos + $keyword_len)) > 50 ? 50 : $field_len;
						// after founded word
						$text .= mb_substr($field_decoded, ($pos + $keyword_len), $len) . $ellipsis;

						$row ['text'] = $text;
						break;
					}
				}

				// exception for extension settings
				$temp_key_field = $key_field;
				$url = $rt;

				if ($rt == 'setting/setting' && !empty($row['extension'])) {
					$temp_key_field = $this->results_controllers['extensions']['id'];
					if($row['type']=='total'){ //for order total extensions
						$url = sprintf($this->results_controllers['extensions']['page2'],$row['extension']);
					}else{
						$url = $this->results_controllers['extensions']['page'];
					}
				}

				if (is_array($temp_key_field)) {
					foreach ($temp_key_field as $var) {
						$url .= "&" . $var . "=" . $row [$var];
					}
				} else {
					$url .= "&" . $temp_key_field . "=" . $row [$temp_key_field];
				}
				$tmp ['type'] = $row['type'];
				$tmp ['href'] = $this->html->getSecureURL($url);
				$tmp ['text'] = '<a href="' . $tmp ['href'] . '" target="_blank" title="' . $row ['text'] . '">' . $row ['title'] . '</a>';
				$output [] = $tmp;
			}
		} else {
			$this->load->language('tool/global_search');
			$output [0] = array("text" => $this->language->get('no_results_message'));
		}
		return $output;
	}



}
