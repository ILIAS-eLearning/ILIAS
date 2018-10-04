--TEST--
PEAR_Installer->install() upgrade a pecl package when it switches from a pear channel to a pecl channel
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$_test_dep->setPEARVersion('1.4.0a1');
$_test_dep->setPHPVersion('5.0.3');

$packageDir        = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'test_upgrade_pecl'. DIRECTORY_SEPARATOR;
$pathtopackagexml  = $packageDir . 'package.xml';
$pathtopackagexml2 = $packageDir . 'package2.xml';

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$result = $dp->download(array($pathtopackagexml));
$installer->setOptions(array());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$installer->install($result[0]);

$phpunit->assertNoErrors('setup for upgrade');

$fakelog->getLog();
$fakelog->getDownload();

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$phpunit->assertNoErrors('after create');

$result = $dp->download(array($pathtopackagexml2));
$phpunit->assertEquals(1, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf = $result[0]->getPackageFile(), 'right kind of pf');
$phpunit->assertEquals('SQLite', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pecl.php.net', $pf->getChannel(), 'right channel');

$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');

$phpunit->assertEquals($pathtopackagexml2, $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v2', $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('SQLite',           $dlpackages[0]['pkg'],  'SQLite');

$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
