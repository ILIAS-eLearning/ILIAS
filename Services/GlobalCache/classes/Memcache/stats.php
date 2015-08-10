<?php

chdir('../../../../');
echo getcwd();
require_once('./include/inc.header.php');
require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');
echo '<pre>' . print_r(ilGlobalCache::getInstance(ilGlobalCache::COMP_CLNG)->getInfo(), 1) . '</pre>';
?>
