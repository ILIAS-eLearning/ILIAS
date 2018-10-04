--TEST--
PEAR_Installer->install() (subpackage that conflicts, upgrade)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/PackageFile.php';
require_once 'PEAR/PackageFile/v1.php';

$pf = new PEAR_PackageFile($config);

$packageDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_install_subpackage' . DIRECTORY_SEPARATOR;
$package    = $pf->fromPackageFile($packageDir . 'package.xml', PEAR_VALIDATE_INSTALLING);
$subpackage = $pf->fromPackageFile($packageDir . 'subpackage.xml', PEAR_VALIDATE_INSTALLING);

$oldpackage = new PEAR_PackageFile_v1;
$oldpackage->setConfig($config);
$oldpackage->setLogger($fakelog);
$oldpackage->setPackage('foo');
$oldpackage->setSummary('foo');
$oldpackage->setDescription('foo');
$oldpackage->setDate('2004-10-01');
$oldpackage->setLicense('PHP License');
$oldpackage->setVersion('1.0');
$oldpackage->setState('stable');
$oldpackage->setNotes('foo');
$oldpackage->addFile('/', 'foo.php', array('role' => 'php'));
$oldpackage->addFile('/', 'bar.php', array('role' => 'php'));
$oldpackage->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$oldpackage->addPackageDep('bar', '1.0', 'ge');
$reg = &$config->getRegistry();
$reg->addPackage2($oldpackage);

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.4.0a1');
$phpunit->assertNoErrors('setup');

$dp1 = new test_PEAR_Downloader_Package($installer);
$dp1->setPackageFile($package);
$dp2 = new test_PEAR_Downloader_Package($installer);
$dp2->setPackageFile($subpackage);
$params = array(&$dp1, &$dp2);
$installer->setOptions(array());
$installer->sortPackagesForInstall($params);
$err = $installer->setDownloadedPackages($params);
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 3,
    1 => 'skipping installed package check of "pear/foo", version "1.1" will be downloaded and installed',
  ),
), $fakelog->getLog(), 'log');

$phpunit->assertNoErrors('dl setup');
$installer->install($dp2, array('upgrade' => true));

$phpunit->assertNoErrors('install');
$installer->install($dp1, array('upgrade' => true));

$phpunit->assertNoErrors('install 2');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'foo.php', 'foo.php');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'bar.php', 'bar.php');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
