<?php
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}


$block_info['block_txt_id'] = 'banner_block'; //type
//$block_info['controller'] = 'react_slider/react_slider';

$block_info['type'] = 'banner_block';
$block_info['kind'] = 'custom';
/*
$block_info['templates'] = Array(
array(
    'parent_block_txt_id'=>'header',
    'template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'header_bottom','template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'content_top','template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'content_bottom','template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'column_left','template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'column_right','template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'footer','template'=>'blocks/react_slider/react_slider.tpl'),
array('parent_block_txt_id'=>'footer_top','template'=>'blocks/react_slider/react_slider.tpl'),
);*/

$block_info['block_descriptions'] = Array(
  array(
  'language_name' => 'english',
  'name' => 'React Images Slider',
  'title' => 'React Images Slider',
  'content' => 'a:1:{s:17:"banner_group_name";s:26:"React Image Slider banners";}',
  'block_wrapper' => 'blocks/react_slider/react_slider.tpl'),

);


$layout = new ALayoutManager();
$layout->saveBlock($block_info);



$this->load->model('extension/banner_manager');
$language_list = $this->model_localisation_language->getLanguages();
$language_id = $this->session->data['content_language_id'];
//install React Image Slider banners home page
$banners_reinstallblocks_stat = $this->db->query('SELECT `banner_id` FROM '.DB_PREFIX."banners WHERE `banner_group_name` = 'React Image Slider banners' ORDER BY `banner_id`");

if ($banners_reinstallblocks_stat->num_rows) {
    //skip banners install
} else {
    $new_banners_stat = array();
    $new_banners_stat_text = array('00', 'An exciting place for the whole family to shop',

                                    'most powerful 4‑inch phone ever',

                                    'experience even faster, more responsive, and more delightful',
                );
    for ($i = 1; $i <= 3; ++$i) {
        $new_banners_stat[] = array(
                        'status' => 1,
                        'banner_type' => 1,
                        'banner_group_name' => 'React Image Slider banners',
                        'start_date' => '2010-01-01 04:27:11',
                        'blank' => '0',
                        'target_url' => 'index.php?rt=index/home',
                        'sort_order' => $i,
                        'name' => 'React Banner Name '.$i,
                        'description' => 'React Image Slide',
                        'meta' => ''.$new_banners_stat_text[$i].'',

                        );
    }

    foreach ($new_banners_stat as $banner) {
        $banner_id = $this->model_extension_banner_manager->addBanner($banner);
        foreach ($language_list as $lang) {
            if ($language_id != $lang['language_id']) {
                $this->session->data['content_language_id'] = $lang['language_id'];
                $this->model_extension_banner_manager->editBanner($banner_id, $banner);
            }
        }
    }

    $rmbs = new AResourceManager();
    $rmbs->setType('image'); //bannerimage
    $stat_banners = glob(DIR_EXT.'react_slider/image/banners/*.jpg');
                // $this->log->write(serialize($stat_banners));
    $banners_blocks = $this->db->query('SELECT `banner_id` FROM '.DB_PREFIX."banners WHERE `banner_group_name` = 'React Image Slider banners' ORDER BY `banner_id`");

    if ($banners_blocks->num_rows) {
        foreach ($banners_blocks->rows as $k => $banner) {
            $result = copy($stat_banners[$k], DIR_RESOURCE.'image/'.basename($stat_banners[$k]));
            $resource = array(
                                'language_id' => $this->config->get('storefront_language_id'),
                                'name' => array(),
                                'resource_path' => basename($stat_banners[$k]),
                                'resource_code' => '',
                            );
            foreach ($language_list as $lang) {
                $resource['name'][$lang['language_id']] = basename($stat_banners[$k]);
            }

            $resource_id = $rmbs->addResource($resource);
            if ($resource_id) {
                $rmbs->mapResource('banners', $banner['banner_id'], $resource_id);
            }
        }
    }
}












//static image add to settings attach
$language_list = $this->model_localisation_language->getLanguages();

$rm = new AResourceManager();
$rm->setType('image');

$result = copy(DIR_EXT . 'react_slider'.DIRECTORY_SEPARATOR.'image'.DIRECTORY_SEPARATOR.'banners'.DIRECTORY_SEPARATOR.'static-slide.jpeg', DIR_RESOURCE . 'image'.DIRECTORY_SEPARATOR.'static-slide.jpeg');

$resource = array(
                'language_id' => $this->config->get('storefront_language_id'),
                'name' => array(),
                'title' => array(),
                'description' => array(),
                'resource_path' => 'static-slide.jpeg',
                'resource_code' => ''
);

foreach ($language_list as $lang) {
    $resource['name'][$lang['language_id']]        = 'static-slide.jpeg';
    $resource['title'][$lang['language_id']]       = 'bg_default_image';
    $resource['description'][$lang['language_id']] = 'ReactSlider bg image';
}
$resource_id = $rm->addResource($resource);

if ($resource_id) {
    $resource_info = $rm->getResource($resource_id, $this->config->get('admin_language_id'));
    $settings['react_slider_react_slider'] = 'image/' . $resource_info['resource_path'];
}


$this->cache->delete('*');
