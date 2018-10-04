--TEST--
PEAR_Config->isDefinedLayer()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
$phpunit->assertFalse($config->isDefinedLayer('foo'), 'foo');
$phpunit->assertTrue($config->isDefinedLayer('user'), 'user');
$phpunit->assertTrue($config->isDefinedLayer('system'), 'system');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
