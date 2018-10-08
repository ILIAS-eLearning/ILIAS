--TEST--
PEAR_Dependency2->checkPackageDependency() exclude failure (downloaded package)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');


require_once 'PEAR/Downloader/Package.php';
require_once 'PEAR/Downloader.php';
$down = new PEAR_Downloader($fakelog, array(), $config);
$dp = new PEAR_Downloader_Package($down);
$dp->initialize(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR .
    'package.xml');
$params = array(&$dp);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => '1.0',
    ), true, $params);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with downloaded package "pear/foo" version 1.0')
), 'exclude 1');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 2');
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => '1.0',
    ), true, $params);
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'nodeps log');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with downloaded package "pear/foo" version 1.0'), $result, 'nodeps ret');

// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 2');
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => '1.0',
    ), true, $params);
$phpunit->assertNoErrors('force');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'force log');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with downloaded package "pear/foo" version 1.0'), $result, 'force ret');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
