--TEST--
PEAR_Config->definedBy()
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
$config->remove('data_dir');
$config->remove('bin_dir');
$config->remove('doc_dir');
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'hi');
$config->set('data_dir', 'hi', 'system');
$config->set('bin_dir', 'hi');
$config->set('bin_dir', 'hi', 'system');

$phpunit->assertEquals('user', $config->definedBy('php_dir'), 'php_dir');
$phpunit->assertEquals('system', $config->definedBy('data_dir'), 'data_dir');
$phpunit->assertEquals('user', $config->definedBy('bin_dir'), 'bin_dir');
$phpunit->assertEquals('default', $config->definedBy('doc_dir'), 'doc_dir');
$phpunit->assertEquals('', $config->definedBy('fooblha'), 'fooblha');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
