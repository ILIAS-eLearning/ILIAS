--TEST--
config-set command, channel-specific value
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'php', $config->get('php_dir'), $config->get('php_dir', 'user', '__uri'), 'setup');

$command->run('config-set', array('channel' => '__uri'), array('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'poo'));
$phpunit->assertNoErrors('after opt user');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'config-set succeeded',
    'cmd' => 'config-set',
  ),
), $fakelog->getLog(), 'ui log, opt user');

$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'poo', $config->get('php_dir', 'user', '__uri'), 'php_dir opt system');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'php', $config->get('php_dir', 'system', '__uri'), 'setup opt system');

$command->run('config-set', array('channel' => '__uri'), array('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'poo', 'system'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'config-set succeeded',
    'cmd' => 'config-set',
  ),
), $fakelog->getLog(), 'ui log opt system');

$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'poo', $config->get('php_dir', 'system', '__uri'), 'php_dir system opt');
$configinfo = array('master_server' => $server,
    'preferred_state' => 'stable',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'php',
    'cfg_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cfg',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',
    '__channels' => array('pecl.php.net' => array(),
        '__uri' => array('php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'poo')));

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
