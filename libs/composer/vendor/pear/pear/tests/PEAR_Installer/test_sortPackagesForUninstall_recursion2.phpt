--TEST--
PEAR_Downloader->sortPackagesForUninstall() recursion (complex)
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
$package->setConfig($config);
$package->setLogger($fakelog);
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
$package->addPackageDep('bar', '1.0', 'ge');
$reg = &$config->getRegistry();
$reg->addPackage2($package);

$package->resetFilelist();
$package->addFile('/', 'bar.php', array('role' => 'php'));
$package->clearDeps();
$package->setPackage('bar');
$package->addPackageDep('next', '1.0', 'ge');
$reg->addPackage2($package);

$package->resetFilelist();
$package->addFile('/', 'next.php', array('role' => 'php'));
$package->clearDeps();
$package->setPackage('next');
$package->addPackageDep('foo', '1.0', 'ge');
$reg->addPackage2($package);

$params[] = $reg->getPackage('next');
$params[] = $reg->getPackage('foo');
$params[] = $reg->getPackage('bar');

$dl = new PEAR_Installer($fakelog);
$dl->sortPackagesForUninstall($params);
$packages = array('foo', 'bar', 'next');
$phpunit->assertTrue(in_array($params[0]->getPackage(), $packages), 'pkg1');
$phpunit->assertTrue(in_array($params[1]->getPackage(), $packages), 'pkg2');
$phpunit->assertTrue(in_array($params[2]->getPackage(), $packages), 'pkg2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
