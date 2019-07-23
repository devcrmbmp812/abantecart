<?php
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

//background image and to settings attach
$language_list = $this->model_localisation_language->getLanguages();


$block_info['block_txt_id'] = 'ultra_search';
$block_info['controller'] = 'ultra_search/ultra_search';

$block_info['templates'] = array(
                array('parent_block_txt_id'=>'header','template'=>'blocks/ultra_search/ultra_search.tpl'),
                array('parent_block_txt_id'=>'column_left','template'=>'blocks/ultra_search/ultra_search.tpl'),
                array('parent_block_txt_id'=>'column_right','template'=>'blocks/ultra_search/ultra_search.tpl'),
        array('parent_block_txt_id'=>'header_bottom','template'=>'blocks/ultra_search/ultra_search.tpl'),
        array('parent_block_txt_id'=>'content_top','template'=>'blocks/ultra_search/ultra_search.tpl'),
        array('parent_block_txt_id'=>'content_bottom','template'=>'blocks/ultra_search/ultra_search.tpl'),
                array('parent_block_txt_id'=>'footer','template'=>'blocks/ultra_search/ultra_search.tpl'),
                array('parent_block_txt_id'=>'footer_top','template'=>'blocks/ultra_search/ultra_search.tpl'),
                );

$block_info['descriptions'] = array( array('language_name' => 'english','name' => 'Ultra search'));



$layout = new ALayoutManager();
$layout->saveBlock($block_info);


$this->cache->delete('*');
