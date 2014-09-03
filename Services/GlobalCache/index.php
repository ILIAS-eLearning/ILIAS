<?php
chdir(strstr(__FILE__, 'Services', true));
require_once('./include/inc.header.php');
require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');
$ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_LNG);
echo '<pre>' . print_r($ilGlobalCache->getInfo(), 1) . '</pre>';
?>
