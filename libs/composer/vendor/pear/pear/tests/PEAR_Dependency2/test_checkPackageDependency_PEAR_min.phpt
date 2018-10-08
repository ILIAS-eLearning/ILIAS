--TEST--
PEAR_Dependency2->checkPackageDependency() min failure
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
        'min' => '1.1',
        'max' => '1.9',
    ), true, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), installed version is 1.0')
), 'min');
$phpunit->assertIsa('PEAR_Error', $result, 'min');

// conflicts
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.10',
        'max' => '1.11',
        'conflicts' => true
    ), false, array());
$phpunit->assertNoErrors('versioned conflicts 1');
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.0',
        'max' => '1.10',
        'conflicts' => true
    ), false, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine conflicts with package "pear/foo" (version >= 1.0, version <= 1.10), installed version is 1.0')
), 'versioned conflicts 2');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), false, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), installed version is 1.0')
), 'min optional');
$phpunit->assertIsa('PEAR_Error', $result, 'min optional');

/****************************** nodeps *************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), true, array());
$phpunit->assertNoErrors('min nodeps');
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min nodeps');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), false, array());
$phpunit->assertNoErrors('min force');
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min nodeps optional');

/****************************** force *************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), true, array());
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min force');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), false, array());
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min force optional');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
