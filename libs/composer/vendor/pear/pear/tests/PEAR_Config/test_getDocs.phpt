--TEST--
PEAR_Config->getDocs()
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
$phpunit->assertEquals('the default server or mirror to use for channel actions', $config->getDocs('preferred_mirror'), 'preferred_mirror');
$phpunit->assertEquals('the default channel to use for all non explicit commands', $config->getDocs('default_channel'), 'default_channel');
$phpunit->assertEquals('ftp url of remote configuration file to use for synchronized install', $config->getDocs('remote_config'), 'remote_config');
$phpunit->assertEquals('whether to automatically discover new channels', $config->getDocs('auto_discover'), 'auto_discover');
$phpunit->assertEquals('name of the main PEAR server [NOT USED IN THIS VERSION]', $config->getDocs('master_server'), 'master_server');
$phpunit->assertEquals('HTTP proxy (host:port) to use when downloading packages', $config->getDocs('http_proxy'), 'http_proxy');

$phpunit->assertEquals('directory where .php files are installed', $config->getDocs('php_dir'), 'php_dir');
$phpunit->assertEquals('directory where data files are installed', $config->getDocs('data_dir'), 'data_dir');
$phpunit->assertEquals('directory where www frontend files (html/js) are installed', $config->getDocs('www_dir'), 'www_dir');
$phpunit->assertEquals('directory where documentation is installed', $config->getDocs('doc_dir'), 'doc_dir');
$phpunit->assertEquals('directory where loadable extensions are installed', $config->getDocs('ext_dir'), 'ext_dir');
$phpunit->assertEquals('directory where regression tests are installed', $config->getDocs('test_dir'), 'test_dir');
$phpunit->assertEquals('directory where executables are installed', $config->getDocs('bin_dir'), 'bin_dir');
$phpunit->assertEquals('directory which is used for web service cache', $config->getDocs('cache_dir'), 'cache_dir');
$phpunit->assertEquals('PHP CLI/CGI binary for executing scripts', $config->getDocs('php_bin'), 'php_bin');

$phpunit->assertEquals('(maintainers) your PEAR account name', $config->getDocs('username'), 'username');
$phpunit->assertEquals('(maintainers) your PEAR account password', $config->getDocs('password'), 'password');

$phpunit->assertEquals('verbosity level
0: really quiet
1: somewhat quiet
2: verbose
3: debug', $config->getDocs('verbose'), 'verbose');
$phpunit->assertEquals('the installer will prefer releases with this state when installing packages without a version or state specified', $config->getDocs('preferred_state'), 'preferred_state');
$phpunit->assertEquals('umask used when creating files (Unix-like systems only)', $config->getDocs('umask'), 'umask');
$phpunit->assertEquals('amount of secs where the local cache is used and not updated', $config->getDocs('cache_ttl'), 'cache_ttl');
$phpunit->assertEquals('which package signature mechanism to use', $config->getDocs('sig_type'), 'sig_type');
$phpunit->assertEquals('which package signature mechanism to use', $config->getDocs('sig_bin'), 'sig_bin');
$phpunit->assertEquals('which key to use for signing with', $config->getDocs('sig_keyid'), 'sig_keyid');
$phpunit->assertEquals('directory where signature keys are located', $config->getDocs('sig_keydir'), 'sig_keydir');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
