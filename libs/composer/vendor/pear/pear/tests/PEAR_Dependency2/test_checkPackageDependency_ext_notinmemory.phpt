--TEST--
PEAR_Dependency2->checkPackageDependency(), extension package, extension is not loaded in memory
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

require_once 'PEAR/PackageFile/v1.php';
$package = new PEAR_PackageFile_v1;
$package->setPackage('foo');
$package->setSummary('foo');
$package->setDescription('foo');
$package->setDate('2004-10-01');
$package->setLicense('PHP License');
$package->setVersion('1.2');
$package->setState('stable');
$package->setNotes('foo');
$package->addFile('/', 'foo.php', array('role' => 'php'));
$package->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$reg = $config->getRegistry();
$reg->addPackage2($package);

$dep = &test_PEAR_Dependency2::singleton($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
        'providesextension' => 'foo',
    ), true, array());
$phpunit->assertNoErrors('required');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'required log');
$phpunit->assertTrue($result, 'required');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'providesextension' => 'foo',
    ), false, array());
$phpunit->assertNoErrors('simple');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'simple log');
$phpunit->assertTrue($result, 'simple');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
