--TEST--
PEAR_Downloader->download() with simple local package.xml
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'simplepackage.xml';
$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array($pathtopackagexml));
$phpunit->assertEquals(1, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf = $result[0]->getPackageFile(), 'right kind of pf');
$phpunit->assertEquals('PEAR', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');
$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertEquals($pathtopackagexml,
    $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v1',
    $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('PEAR',
    $dlpackages[0]['pkg'], 'PEAR');
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');
$phpunit->assertEquals(array (
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
