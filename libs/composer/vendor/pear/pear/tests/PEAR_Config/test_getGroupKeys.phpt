--TEST--
PEAR_Config->getGroupKeys()
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
$phpunit->assertEquals(array (
  0 => 'default_channel',
  1 => 'preferred_mirror',
  2 => 'remote_config',
  3 => 'auto_discover',
  4 => 'master_server',
  5 => 'http_proxy',
), array_slice($config->getGroupKeys('Internet Access'), 0, 6), 'Internet Access');
$phpunit->assertEquals(array (
  0 => 'php_dir',
  1 => 'ext_dir',
  2 => 'doc_dir',
  3 => 'bin_dir',
), array_slice($config->getGroupKeys('File Locations'), 0, 4), 'File Locations');
$phpunit->assertEquals(array (
  'data_dir',
  'cfg_dir',
  'www_dir',
  'man_dir',
  'test_dir',
  'cache_dir',
  'temp_dir',
  'download_dir',
  'php_bin',
  'php_prefix',
  'php_suffix',
  'php_ini',
  'metadata_dir',
), array_slice($config->getGroupKeys('File Locations (Advanced)'), 0, 13), 'File Locations (Advanced)');
$phpunit->assertEquals(array (
  0 => 'username',
  1 => 'password',
  2 => 'sig_type',
  3 => 'sig_bin',
  4 => 'sig_keyid',
  5 => 'sig_keydir',
), array_slice($config->getGroupKeys('Maintainers'), 0, 6), 'Maintainers');
$phpunit->assertEquals(array (
  0 => 'verbose',
  1 => 'preferred_state',
  2 => 'umask',
  3 => 'cache_ttl',
), array_slice($config->getGroupKeys('Advanced'), 0, 4), 'Advanced');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
