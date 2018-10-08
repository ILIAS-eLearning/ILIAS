--TEST--
PEAR_Dependency2->checkPackageDependency() exclude failure (installed package)
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
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

require_once 'PEAR/PackageFile/v1.php';
$package = new PEAR_PackageFile_v1;
$package->setPackage('foo');
$package->setSummary('foo');
$package->setDescription('foo');
$package->setDate('2004-10-01');
$package->setLicense('PHP License');
$package->setVersion('1.0');
$package->setState('stable');
$package->setNotes('foo');
$package->addFile('/', 'foo.php', array('role' => 'php'));
$package->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$reg = $config->getRegistry();
$reg->addPackage2($package);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => '1.0',
    ), true, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with installed package "pear/foo" version 1.0')
), 'exclude 1');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'exclude 1 log');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => array('0.9','1.0'),
    ), true, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with installed package "pear/foo" version 1.0')
), 'exclude 1 multi');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'exclude 1 log multi');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1 multi');

// conflicts
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'exclude' => '1.0',
        'conflicts' => true
    ), false, array());
$phpunit->assertNoErrors('versioned conflicts 1');
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'exclude' => array('2.0'),
        'conflicts' => true
    ), false, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine conflicts with package "pear/foo" (excluded versions: 2.0), installed version is 1.0')
), 'versioned conflicts 2');

// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => '1.0',
    ), true, array());
$phpunit->showall();
$phpunit->assertNoErrors('exclude 2 nodeps');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'exclude 2 log nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with installed package "pear/foo" version 1.0'), $result, 'exclude 2 nodeps');

// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'exclude' => '1.0',
    ), true, array());
$phpunit->showall();
$phpunit->assertNoErrors('exclude 2 force');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'exclude 2 log force');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with installed package "pear/foo" version 1.0'), $result, 'exclude 2 force');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
