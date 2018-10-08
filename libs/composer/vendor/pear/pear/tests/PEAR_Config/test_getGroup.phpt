--TEST--
PEAR_Config->getGroup()
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
$phpunit->assertEquals('Internet Access', $config->getGroup('preferred_mirror'), 'preferred_mirror');
$phpunit->assertEquals('Internet Access', $config->getGroup('default_channel'), 'default_channel');
$phpunit->assertEquals('Internet Access', $config->getGroup('remote_config'), 'remote_config');
$phpunit->assertEquals('Internet Access', $config->getGroup('auto_discover'), 'auto_discover');
$phpunit->assertEquals('Internet Access', $config->getGroup('master_server'), 'master_server');
$phpunit->assertEquals('Internet Access', $config->getGroup('http_proxy'), 'http_proxy');

$phpunit->assertEquals('File Locations', $config->getGroup('php_dir'), 'php_dir');
$phpunit->assertEquals('File Locations (Advanced)', $config->getGroup('data_dir'), 'data_dir');
$phpunit->assertEquals('File Locations (Advanced)', $config->getGroup('www_dir'), 'www_dir');
$phpunit->assertEquals('File Locations', $config->getGroup('doc_dir'), 'doc_dir');
$phpunit->assertEquals('File Locations', $config->getGroup('ext_dir'), 'ext_dir');
$phpunit->assertEquals('File Locations (Advanced)', $config->getGroup('test_dir'), 'test_dir');
$phpunit->assertEquals('File Locations', $config->getGroup('bin_dir'), 'bin_dir');
$phpunit->assertEquals('File Locations (Advanced)', $config->getGroup('cache_dir'), 'cache_dir');
$phpunit->assertEquals('File Locations (Advanced)', $config->getGroup('php_bin'), 'php_bin');

$phpunit->assertEquals('Maintainers', $config->getGroup('username'), 'username');
$phpunit->assertEquals('Maintainers', $config->getGroup('password'), 'password');

$phpunit->assertEquals('Advanced', $config->getGroup('verbose'), 'verbose');
$phpunit->assertEquals('Advanced', $config->getGroup('preferred_state'), 'preferred_state');
$phpunit->assertEquals('Advanced', $config->getGroup('umask'), 'umask');
$phpunit->assertEquals('Advanced', $config->getGroup('cache_ttl'), 'cache_ttl');
$phpunit->assertEquals('Maintainers', $config->getGroup('sig_type'), 'sig_type');
$phpunit->assertEquals('Maintainers', $config->getGroup('sig_bin'), 'sig_bin');
$phpunit->assertEquals('Maintainers', $config->getGroup('sig_keyid'), 'sig_keyid');
$phpunit->assertEquals('Maintainers', $config->getGroup('sig_keydir'), 'sig_keydir');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
