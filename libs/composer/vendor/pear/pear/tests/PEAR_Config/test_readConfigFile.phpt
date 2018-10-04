--TEST--
PEAR_Config->readConfigFile()
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
    'ini' . DIRECTORY_SEPARATOR . 'user.input', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
$config->setChannels(array('pear.php.net', '__uri', 'test2'));
$ret = $config->readConfigFile('foo', 'foo');
$config->set('php_dir', $temp_path); // use sandbox
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'unknown config layer `foo\''),
), 'unknown layer');
$phpunit->assertIsa('PEAR_Error', $ret, 'err object');
$ret = $config->readConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'toonew.conf');
$config->set('php_dir', $temp_path); // use sandbox
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'toonew.conf: unknown version `2.0\''),
), 'unknown layer');
$phpunit->assertIsa('PEAR_Error', $ret, 'err object');

$phpunit->assertEquals(1, $config->get('verbose'), '$config->get(verbose)');
$phpunit->assertNull($config->get('verbose', 'user'), '$config->get(verbose, user)');
$phpunit->assertNull($config->get('verbose', 'system'), '$config->get(verbose, system)');
$phpunit->assertTrue($config->readConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'user2.input'), 'read user2.input');
$config->set('php_dir', $temp_path); // use sandbox

$phpunit->assertEquals(2, $config->get('verbose'), '$config->get(verbose) after');
$phpunit->assertEquals(2, $config->get('verbose', 'user'), '$config->get(verbose, user) after');
$phpunit->assertEquals(null, $config->get('verbose', 'system'), '$config->get(verbose, system) after');

$phpunit->assertTrue($config->readConfigFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 
    'ini' . DIRECTORY_SEPARATOR . 'user3.input', 'system'), 'read user3.input');
$config->set('php_dir', $temp_path); // use sandbox

$phpunit->assertEquals(2, $config->get('verbose'), '$config->get(verbose) after system');
$phpunit->assertEquals(2, $config->get('verbose', 'user'), '$config->get(verbose, user) after system');
$phpunit->assertEquals(60, $config->get('verbose', 'system'), '$config->get(verbose, system) after system');
$phpunit->assertEquals(70, $config->get('verbose', 'system', '__uri'), '$config->get(verbose, system) after system __uri');
$phpunit->assertEquals(71, $config->get('verbose', 'system', 'test2'), '$config->get(verbose, system) after system test2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
