--TEST--
PEAR_Config->removeLayer()
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
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'hello');
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'systemhello', 'system');
$config->set('data_dir', 'hello');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'hello', $config->get('php_dir'), 'user php_dir');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'systemhello', $config->get('php_dir', 'system'), 'system php_dir');
$phpunit->assertEquals('hello', $config->get('data_dir'), 'user data_dir');
$phpunit->assertFalse($config->removeLayer('foo'), 'foo');
$phpunit->assertTrue($config->removeLayer('user'), 'user');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'systemhello', $config->get('php_dir'), 'user php_dir after remove');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'systemhello', $config->get('php_dir', 'system'), 'system php_dir after remove');
$phpunit->assertEquals(PEAR_CONFIG_DEFAULT_DATA_DIR, $config->get('data_dir'), 'user data_dir after remove');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
