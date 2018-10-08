--TEST--
PEAR_Config->getType()
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
$phpunit->assertEquals('string', $config->getType('default_channel'), 'default_channel');
$phpunit->assertEquals('string', $config->getType('preferred_mirror'), 'preferred_mirror');
$phpunit->assertEquals('password', $config->getType('remote_config'), 'remote_config');
$phpunit->assertEquals('integer', $config->getType('auto_discover'), 'auto_discover');
$phpunit->assertEquals('string', $config->getType('master_server'), 'master_server');
$phpunit->assertEquals('string', $config->getType('http_proxy'), 'http_proxy');

$phpunit->assertEquals('directory', $config->getType('php_dir'), 'php_dir');
$phpunit->assertEquals('directory', $config->getType('data_dir'), 'data_dir');
$phpunit->assertEquals('directory', $config->getType('www_dir'), 'www_dir');
$phpunit->assertEquals('directory', $config->getType('doc_dir'), 'doc_dir');
$phpunit->assertEquals('directory', $config->getType('ext_dir'), 'ext_dir');
$phpunit->assertEquals('directory', $config->getType('test_dir'), 'test_dir');
$phpunit->assertEquals('directory', $config->getType('bin_dir'), 'bin_dir');
$phpunit->assertEquals('directory', $config->getType('cache_dir'), 'cache_dir');
$phpunit->assertEquals('file', $config->getType('php_bin'), 'php_bin');

$phpunit->assertEquals('string', $config->getType('username'), 'username');
$phpunit->assertEquals('password', $config->getType('password'), 'password');

$phpunit->assertEquals('integer', $config->getType('verbose'), 'verbose');
$phpunit->assertEquals('set', $config->getType('preferred_state'), 'preferred_state');
$phpunit->assertEquals('mask', $config->getType('umask'), 'umask');
$phpunit->assertEquals('integer', $config->getType('cache_ttl'), 'cache_ttl');
$phpunit->assertEquals('set', $config->getType('sig_type'), 'sig_type');
$phpunit->assertEquals('string', $config->getType('sig_bin'), 'sig_bin');
$phpunit->assertEquals('string', $config->getType('sig_keyid'), 'sig_keyid');
$phpunit->assertEquals('directory', $config->getType('sig_keydir'), 'sig_keydir');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
