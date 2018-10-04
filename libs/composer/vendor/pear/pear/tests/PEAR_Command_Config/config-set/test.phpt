--TEST--
config-set command
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'php', $config->get('php_dir'), 'setup');

$command->run('config-set', array(), array('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'poo'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'config-set succeeded',
    'cmd' => 'config-set',
  ),
), $fakelog->getLog(), 'ui log');

$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'poo', $config->get('php_dir'), 'php_dir');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'php', $config->get('php_dir', 'system'), 'setup system');

$command->run('config-set', array(), array('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'poo', 'system'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'config-set succeeded',
    'cmd' => 'config-set',
  ),
), $fakelog->getLog(), 'ui log');

$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'poo', $config->get('php_dir', 'system'), 'php_dir');
$configinfo = array('master_server' => $server,
    'preferred_state' => 'stable',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'poo',
    'cfg_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cfg',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',
    '__channels' => array('pecl.php.net' => array(), '__uri' => array()));

$info = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.ini')));
$info = unserialize($info[1]);
$phpunit->assertEquals($configinfo, $info, 'saved 1');

$info = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.conf')));
$info = unserialize($info[1]);
$phpunit->assertEquals($configinfo, $info, 'saved 2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
