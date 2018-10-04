--TEST--
PEAR_Dependency2->checkPackageDependency() recommended version
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
$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');
$package = new PEAR_PackageFile_v2_rw;
$package->setPackage('foo');
$package->setChannel('pear.php.net');
$package->setSummary('foo');
$package->setDescription('foo');
$package->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$package->setDate('2004-10-01');
$package->setReleaseVersion('1.10');
$package->setAPIVersion('1.0');
$package->setReleaseStability('stable');
$package->setAPIStability('stable');
$package->setLicense('PHP License');
$package->setNotes('foo');
$package->clearContents();
$package->addFile('/', 'foo.php', array('role' => 'php'));
$package->setPhpDep('4.3.0', '6.0.0');
$package->setPearinstallerDep('1.4.0dev13');
$package->addConflictingPackageDepWithChannel('bar', 'pear.php.net');
$package->setPackageType('php');
$package->setConfig($config);
$reg = $config->getRegistry();
$reg->addPackage2($package);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.13',
        'recommended' => '1.9'
    ), true, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine dependency package "pear/foo" installed version 1.10 is not the recommended version 1.9, but may be compatible, use --force to install')
), 'recommended 1');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'recommended 1 log');
$phpunit->assertIsa('PEAR_Error', $result, 'recommended 1');

$package->setReleaseVersion('1.9');
$reg->updatePackage2($package);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'recommended' => '1.9'
    ), true, array());
$phpunit->assertNoErrors('recommended works');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'recommended works log');
$phpunit->assertTrue($result, 'recommended works');

$package = new PEAR_PackageFile_v2_rw;
$package->setPackage('foo');
$package->setChannel('pear.php.net');
$package->setSummary('foo');
$package->setDescription('foo');
$package->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$package->setDate('2004-10-01');
$package->setReleaseVersion('1.10');
$package->setAPIVersion('1.0');
$package->setReleaseStability('stable');
$package->setAPIStability('stable');
$package->setLicense('PHP License');
$package->setNotes('foo');
$package->clearContents();
$package->addFile('/', 'foo.php', array('role' => 'php'));
$package->addCompatiblePackage('mine', 'pear.php.net', '0.9', '2.0');
$package->setPhpDep('4.3.0', '6.0.0');
$package->setPearinstallerDep('1.4.0dev13');
$package->addConflictingPackageDepWithChannel('bar', 'pear.php.net');
$package->setPackageType('php');
$package->setConfig($config);
$reg->updatePackage2($package);

$parent = new PEAR_PackageFile_v1;
$parent->setPackage('mine');
$parent->setSummary('foo');
$parent->setDescription('foo');
$parent->setDate('2004-10-01');
$parent->setLicense('PHP License');
$parent->setVersion('1.10');
$parent->setState('stable');
$parent->setNotes('foo');
$parent->addFile('/', 'foo.php', array('role' => 'php'));
$parent->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$parent->setConfig($config);
$dl = new test_PEAR_Downloader($fakelog, array(), $config);
$dp = new test_PEAR_Downloader_Package($dl);
$dp->setPackageFile($parent);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '2.0',
        'recommended' => '1.8'
    ), true, array(&$dp));
$phpunit->assertNoErrors('compatible works');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'compatible works log');
$phpunit->assertTrue($result, 'compatible works');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
