--TEST--
PEAR_Config->getREST()
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
$phpunit->assertIsa('PEAR_REST_10', $config->getREST('1.0'), 'test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
