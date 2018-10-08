--TEST--
PEAR_Config->getKeys()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'zoo.ini', $temp_path . DIRECTORY_SEPARATOR . 'zoo.ini');
$phpunit->assertEquals(array (
  'default_channel',
  'preferred_mirror',
  'remote_config',
  'auto_discover',
  'master_server',
  'http_proxy',
  'php_dir',
  'ext_dir',
  'doc_dir',
  'bin_dir',
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
  'username',
  'password',
  'verbose',
  'preferred_state',
  'umask',
  'cache_ttl',
  'sig_type',
  'sig_bin',
  'sig_keyid',
  'sig_keydir',
), array_slice($config->getKeys(), 0, 33), 'keys');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
