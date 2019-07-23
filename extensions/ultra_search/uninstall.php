<?php
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}


// delete block
$layout = new ALayoutManager();
$layout->deleteBlock('ultra_search');

$this->cache->delete('*');
