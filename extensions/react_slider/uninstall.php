<?php
if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

// delete block
//$layout = new ALayoutManager();
$usage = $this->db->query("DELETE FROM ".$this->db->table("block_descriptions")."
														 WHERE block_wrapper = 'blocks/react_slider/react_slider.tpl'");
if ($usage->num_rows) {
		//return false;
}
//$layout->deleteBlock('react_slider');




//delete banners
$this->load->model('extension/banner_manager');
$language_list = $this->model_localisation_language->getLanguages();
$banners_reinstallblocks_stat = $this->db->query('DELETE FROM '.DB_PREFIX."banners WHERE `banner_group_name` = 'React Image Slider banners' ORDER BY `banner_id`");


$this->cache->delete('*');
