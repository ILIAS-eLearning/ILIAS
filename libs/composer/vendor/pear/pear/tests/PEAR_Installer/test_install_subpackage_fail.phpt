--TEST--
PEAR_Installer->install() (subpackage that conflicts)
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
$package = $pf->fromPackageFile(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'test_install_subpackage' . DIRECTORY_SEPARATOR . 'package.xml', PEAR_VALIDATE_INSTALLING);

$subpackage = $pf->fromPackageFile(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'test_install_subpackage' . DIRECTORY_SEPARATOR . 'subpackage.xml', PEAR_VALIDATE_INSTALLING);

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
$dp1->setPackageFile($oldpackage);
$dp2 = new test_PEAR_Downloader_Package($installer);
$dp2->setPackageFile($subpackage);
$params = array(&$dp2);
$installer->setDownloadedPackages($params);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'log');
$installer->install($dp2, array('upgrade' => true));
$phpunit->assertErrors(array(
array('package' => 'PEAR_Error', 'message' =>
"pear.php.net/bar: conflicting files found:\n" .
"bar.php (pear.php.net/foo)\n")), 'install');

$params = array(&$dp1, &$dp2);
$installer->setDownloadedPackages($params);
$installer->install($dp2, array('upgrade' => true));
$phpunit->assertErrors(array(
array('package' => 'PEAR_Error', 'message' =>
"pear.php.net/bar: conflicting files found:\n" .
"bar.php (pear.php.net/foo)\n")), 'install 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
