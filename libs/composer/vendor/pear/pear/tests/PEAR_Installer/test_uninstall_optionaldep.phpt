--TEST--
PEAR_Installer->uninstall() (package remains that optionally depends on uninstalled package)
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
$reg = &$config->getRegistry();
$reg->addPackage2($package);

$package->resetFilelist();
$package->addFile('/', 'bar.php', array('role' => 'php'));
$package->clearDeps();
$package->addPackageDep('foo', '1.0', 'ge', 'yes');
$package->setPackage('bar');
$reg->addPackage2($package);

$params[] = $reg->getPackage('foo');

$dl = new PEAR_Installer($fakelog);
$dl->setUninstallPackages($params);
$dl->uninstall('foo');
$phpunit->assertNoErrors('foo');
$phpunit->assertEquals( array (
  0 => 
  array (
    0 => 'pear/foo (version >= 1.0) can be optionally used by installed package "pear/bar"',
    1 => true,
  ),
 )
, $fakelog->getLog(), 'foo');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
