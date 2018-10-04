--TEST--
PEAR_Config->getSetValues()
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
$phpunit->assertEquals(null, $config->getSetValues('preferred_mirror'), 'preferred_mirror');
$phpunit->assertEquals(null, $config->getSetValues('default_channel'), 'default_channel');
$phpunit->assertEquals(null, $config->getSetValues('remote_config'), 'remote_config');
$phpunit->assertEquals(null, $config->getSetValues('auto_discover'), 'auto_discover');
$phpunit->assertEquals(null, $config->getSetValues('master_server'), 'master_server');
$phpunit->assertEquals(null, $config->getSetValues('http_proxy'), 'http_proxy');

$phpunit->assertEquals(null, $config->getSetValues('php_dir'), 'php_dir');
$phpunit->assertEquals(null, $config->getSetValues('data_dir'), 'data_dir');
$phpunit->assertEquals(null, $config->getSetValues('www_dir'), 'www_dir');
$phpunit->assertEquals(null, $config->getSetValues('doc_dir'), 'doc_dir');
$phpunit->assertEquals(null, $config->getSetValues('ext_dir'), 'ext_dir');
$phpunit->assertEquals(null, $config->getSetValues('test_dir'), 'test_dir');
$phpunit->assertEquals(null, $config->getSetValues('bin_dir'), 'bin_dir');
$phpunit->assertEquals(null, $config->getSetValues('cache_dir'), 'cache_dir');
$phpunit->assertEquals(null, $config->getSetValues('php_bin'), 'php_bin');

$phpunit->assertEquals(null, $config->getSetValues('username'), 'username');
$phpunit->assertEquals(null, $config->getSetValues('password'), 'password');

$phpunit->assertEquals(null, $config->getSetValues('verbose'), 'verbose');
$phpunit->assertEquals(array('stable', 'beta', 'alpha', 'devel', 'snapshot'),
    $config->getSetValues('preferred_state'), 'preferred_state');
$phpunit->assertEquals(null, $config->getSetValues('umask'), 'umask');
$phpunit->assertEquals(null, $config->getSetValues('cache_ttl'), 'cache_ttl');
$phpunit->assertEquals(array('gpg'), $config->getSetValues('sig_type'), 'sig_type');
$phpunit->assertEquals(null, $config->getSetValues('sig_bin'), 'sig_bin');
$phpunit->assertEquals(null, $config->getSetValues('sig_keyid'), 'sig_keyid');
$phpunit->assertEquals(null, $config->getSetValues('sig_keydir'), 'sig_keydir');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
