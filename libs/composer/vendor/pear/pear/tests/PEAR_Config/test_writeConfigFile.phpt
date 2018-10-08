--TEST--
PEAR_Config->writeConfigFile()
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
$config->readConfigFile($temp_path . DIRECTORY_SEPARATOR . 'pear.ini');
$config->readConfigFile($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf', 'system');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Config::readConfigFile fopen(\'' .
        $temp_path . DIRECTORY_SEPARATOR . 'pear.frompf\',\'r\') failed')
), 'setup');
$contents = unserialize(implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.ini')));
$phpunit->assertEquals(array('master_server' => $server,
    'preferred_state' => 'stable',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'php',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',), $contents, 'setup');

$config->set('preferred_state', 'alpha');
$config->set('preferred_state', 'beta', 'system');
$config->writeConfigFile();

$contents = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.ini')));
$phpunit->assertEquals('#PEAR_Config 0.9', $contents[0], 'after write 0');
$phpunit->assertEquals(array('master_server' => $server,
    'preferred_state' => 'alpha',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'php',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',
    '__channels' => array('pecl.php.net' => array(),'__uri' => array(), 'doc.php.net' => array())),
    unserialize($contents[1]), 'after write 1');

$phpunit->assertFileNotExists($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf', 'pear.frompf');
$config->writeConfigFile($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf', 'system');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf', 'pear.frompf');

$contents = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf')));
$phpunit->assertEquals('#PEAR_Config 0.9', $contents[0], 'after write sys 0');
$phpunit->assertEquals(array('__channels' => array('pecl.php.net' => array(),'__uri' => array(), 'doc.php.net' => array()),'preferred_state' => 'beta'), unserialize($contents[1]), 'after write sys 1');

$config->readConfigFile($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf', 'system');

$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'hello');
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'bye', 'system');
$config->writeConfigFile(null, 'both');

$contents = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.ini')));
$phpunit->assertEquals('#PEAR_Config 0.9', $contents[0], 'after both 0');
$phpunit->assertEquals(array('master_server' => $server,
    'preferred_state' => 'alpha',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'hello',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',
    '__channels' => array('pecl.php.net' => array(),'__uri' => array(), 'doc.php.net' => array())), unserialize($contents[1]), 'after both 1');

$contents = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'pear.frompf')));
$phpunit->assertEquals('#PEAR_Config 0.9', $contents[0], 'after both sys 0');
$phpunit->assertEquals(array(
    '__channels' => array('pecl.php.net' => array(),'__uri' => array(), 'doc.php.net' => array()),
    'preferred_state' => 'beta',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bye',
    ), unserialize($contents[1]), 'after both sys 1');

$config->writeConfigFile($temp_path . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR .
    'glonk.ini');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR .
    'glonk.ini', 'glonk.ini');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
