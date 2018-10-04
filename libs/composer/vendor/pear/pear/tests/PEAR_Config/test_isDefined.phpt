--TEST--
PEAR_Config->isDefined()
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
$phpunit->assertEquals(true, $config->isDefined('preferred_mirror'), 'preferred_mirror');
$phpunit->assertEquals(true, $config->isDefined('default_channel'), 'default_channel');
$phpunit->assertEquals(true, $config->isDefined('remote_config'), 'remote_config');
$phpunit->assertEquals(true, $config->isDefined('auto_discover'), 'auto_discover');
$phpunit->assertEquals(true, $config->isDefined('master_server'), 'master_server');
$phpunit->assertEquals(true, $config->isDefined('http_proxy'), 'http_proxy');

$phpunit->assertEquals(true, $config->isDefined('php_dir'), 'php_dir');
$phpunit->assertEquals(true, $config->isDefined('data_dir'), 'data_dir');
$phpunit->assertEquals(true, $config->isDefined('www_dir'), 'www_dir');
$phpunit->assertEquals(true, $config->isDefined('doc_dir'), 'doc_dir');
$phpunit->assertEquals(true, $config->isDefined('ext_dir'), 'ext_dir');
$phpunit->assertEquals(true, $config->isDefined('test_dir'), 'test_dir');
$phpunit->assertEquals(true, $config->isDefined('bin_dir'), 'bin_dir');
$phpunit->assertEquals(true, $config->isDefined('cache_dir'), 'cache_dir');
$phpunit->assertEquals(true, $config->isDefined('php_bin'), 'php_bin');

$phpunit->assertEquals(true, $config->isDefined('username'), 'username');
$phpunit->assertEquals(true, $config->isDefined('password'), 'password');

$phpunit->assertEquals(true, $config->isDefined('verbose'), 'verbose');
$phpunit->assertEquals(true, $config->isDefined('preferred_state'), 'preferred_state');
$phpunit->assertEquals(true, $config->isDefined('umask'), 'umask');
$phpunit->assertEquals(true, $config->isDefined('cache_ttl'), 'cache_ttl');
$phpunit->assertEquals(true, $config->isDefined('sig_type'), 'sig_type');
$phpunit->assertEquals(true, $config->isDefined('sig_bin'), 'sig_bin');
$phpunit->assertEquals(true, $config->isDefined('sig_keyid'), 'sig_keyid');
$phpunit->assertEquals(true, $config->isDefined('sig_keydir'), 'sig_keydir');

$phpunit->assertEquals(false, $config->isDefined('blahblhablha'),'blahblhablha');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
