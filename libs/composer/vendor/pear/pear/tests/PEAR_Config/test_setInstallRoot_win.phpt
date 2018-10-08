--TEST--
PEAR_Config->setInstallRoot() [windows only]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
} elseif (substr(PHP_OS, 0, 3) != 'WIN') {
    echo 'skip windows-only test';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
$config->set('sig_keydir', 'abogado');
$phpunit->assertEquals($php_dir, $config->get('php_dir'), 'php_dir');
$phpunit->assertEquals($data_dir, $config->get('data_dir'), 'data_dir');
$phpunit->assertEquals($www_dir, $config->get('www_dir'), 'www_dir');
$phpunit->assertEquals($doc_dir, $config->get('doc_dir'), 'doc_dir');
$phpunit->assertEquals($test_dir, $config->get('test_dir'), 'test_dir');
$phpunit->assertEquals($cache_dir, $config->get('cache_dir'), 'cache_dir');
$phpunit->assertEquals($bin_dir, $config->get('bin_dir'), 'bin_dir');
$phpunit->assertEquals('abogado', $config->get('sig_keydir'), 'sig_keydir');
$dl = substr($temp_path, 0, 2);
$config->setInstallRoot($dl . '\poo');
$phpunit->assertEquals(PEAR_Config::_prependPath($php_dir, $dl . '\poo'), $config->get('php_dir'), 'php_dir');
$phpunit->assertEquals(PEAR_Config::_prependPath($data_dir, $dl . '\poo'), $config->get('data_dir'), 'data_dir');
$phpunit->assertEquals(PEAR_Config::_prependPath($www_dir, $dl . '\poo'), $config->get('www_dir'), 'wwww_dir');
$phpunit->assertEquals(PEAR_Config::_prependPath($doc_dir, $dl . '\poo'), $config->get('doc_dir'), 'doc_dir');
$phpunit->assertEquals(PEAR_Config::_prependPath($test_dir, $dl . '\poo'), $config->get('test_dir'), 'test_dir');
$phpunit->assertEquals(PEAR_Config::_prependPath($cache_dir, $dl . '\poo'), $config->get('cache_dir'), 'cache_dir');
$phpunit->assertEquals(PEAR_Config::_prependPath($bin_dir, $dl . '\poo'), $config->get('bin_dir'), 'bin_dir');
$phpunit->assertEquals($dl . '\poo' . substr($php_dir, 2), PEAR_Config::_prependPath($php_dir, $dl . '\poo'), 'make sure there is no C:\C:\ action');
$phpunit->assertEquals('abogado', $config->get('sig_keydir'), 'sig_keydir');
$config->setInstallRoot(false);
$phpunit->assertEquals($php_dir, $config->get('php_dir'), 'php_dir');
$phpunit->assertEquals($data_dir, $config->get('data_dir'), 'data_dir');
$phpunit->assertEquals($www_dir, $config->get('www_dir'), 'www_dir');
$phpunit->assertEquals($doc_dir, $config->get('doc_dir'), 'doc_dir');
$phpunit->assertEquals($test_dir, $config->get('test_dir'), 'test_dir');
$phpunit->assertEquals($cache_dir, $config->get('cache_dir'), 'cache_dir');
$phpunit->assertEquals($bin_dir, $config->get('bin_dir'), 'bin_dir');
$phpunit->assertEquals('abogado', $config->get('sig_keydir'), 'sig_keydir');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
