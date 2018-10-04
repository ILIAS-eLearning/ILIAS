--TEST--
PEAR_Config->mergeConfigFile()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'user2.input', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
$phpunit->assertTrue($config->mergeConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'merge.input', true), 'first merge');
$phpunit->assertEquals(100, $config->get('verbose'), '$config->get(verbose)');
$phpunit->assertEquals(100, $config->get('verbose', 'user'), '$config->get(verbose, user)');
$phpunit->assertNull($config->get('verbose', 'system'), '$config->get(verbose, system)');
$config = new PEAR_Config(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'user.input', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');

$config->set('php_dir', $temp_path); // use sandbox
$config->setChannels(array('pear.php.net', '__uri', 'test2'));
$ret = $config->mergeConfigFile('foo', true, 'foo');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'unknown config layer `foo\''),
), 'unknown layer');
$phpunit->assertIsa('PEAR_Error', $ret, 'err object');
$ret = $config->mergeConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'toonew.conf');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'toonew.conf: unknown version `2.0\''),
), 'unknown layer');
$phpunit->assertIsa('PEAR_Error', $ret, 'err object');

$phpunit->assertEquals(1, $config->get('verbose'), '$config->get(verbose)');
$phpunit->assertNull($config->get('verbose', 'user', '__uri'), '$config->get(verbose, user, __uri)');
$phpunit->assertNull($config->get('verbose', 'user', 'test2'), '$config->get(verbose, user, test2)');
$phpunit->assertTrue($config->readConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'user3.input'), 'read user3.input');
$config->set('php_dir', $temp_path); // use sandbox
$phpunit->assertTrue($config->mergeConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'merge2.input'), 'merge merge2.input', true);

$phpunit->assertEquals(35, $config->get('verbose'), '$config->get(verbose) after true');
$phpunit->assertEquals(898, $config->get('verbose', 'user', '__uri'), '$config->get(verbose, user, __uri) after true');
$phpunit->assertEquals(899, $config->get('verbose', 'user', 'test2'), '$config->get(verbose, user, test2) after true');

$phpunit->assertTrue($config->readConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'user3.input', 'user'), 'read user3.input');
$config->set('php_dir', $temp_path); // use sandbox
$phpunit->assertTrue($config->mergeConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'merge2.input'), 'merge merge2.input', false);

$phpunit->assertEquals(35, $config->get('verbose'), '$config->get(verbose) after false');
$phpunit->assertEquals(898, $config->get('verbose', 'user', '__uri'), '$config->get(verbose, user, __uri) after false');
$phpunit->assertEquals(899, $config->get('verbose', 'user', 'test2'), '$config->get(verbose, user, test2) after false');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
