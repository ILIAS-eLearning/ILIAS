--TEST--
PEAR_Config::singleton()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = &PEAR_Config::singleton($temp_path . DIRECTORY_SEPARATOR . 'pear.ini');
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'ok');
$config2 = &PEAR_Config::singleton();
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'ok', $config2->get('php_dir'), 'test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
