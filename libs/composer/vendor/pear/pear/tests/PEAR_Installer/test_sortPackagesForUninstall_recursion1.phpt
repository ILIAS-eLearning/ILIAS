--TEST--
PEAR_Installer->sortPackagesForUninstall() recursion (simple)
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
$reg = $config->getRegistry();
$reg->addPackage2($package);

$package->resetFilelist();
$package->addFile('/', 'bar.php', array('role' => 'php'));
$package->clearDeps();
$package->setPackage('bar');
$package->addPackageDep('foo', '1.0', 'ge');
$reg->addPackage2($package);

$params[] = $reg->getPackage('bar');
$params[] = $reg->getPackage('foo');

$dl = new PEAR_Installer($fakelog);
$dl->sortPackagesForUninstall($params);
// order is undefined since both packages depend on each other
// we only know both have to be in the list
$phpunit->assertTrue(
    in_array($params[0]->getPackage(), array('foo', 'bar')),
    'foo'
);
$phpunit->assertTrue(
    in_array($params[1]->getPackage(), array('foo', 'bar')),
    'bar'
);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
