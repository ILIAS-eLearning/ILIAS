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
    'package' => 'foo'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

require_once 'PEAR/PackageFile/v2/rw.php';
require_once 'PEAR/Downloader.php';
$package = new PEAR_PackageFile_v2_rw;
$package->setLogger($fakelog);
$package->setPackage('foo');
$package->setChannel('pear.php.net');
$package->setSummary('foo');
$package->setDescription('foo');
$package->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$package->setDate('2004-10-01');
$package->setReleaseVersion('1.0');
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

$pkg2 = new PEAR_PackageFile_v2_rw;
$pkg2->setPackage('bar');
$pkg2->setChannel('pear.php.net');
$dl = new PEAR_Downloader($fakelog, array(), $config);
$result = $dep->validatePackage($pkg2, $dl);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/foo cannot be installed, conflicts with installed packages')), 'vp');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'pear/foo conflicts with package "pear/bar"',
    1 => true,
  ),
), $fakelog->getLog(), 'vp');
$phpunit->assertIsa('PEAR_Error', $result, 'vp');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
