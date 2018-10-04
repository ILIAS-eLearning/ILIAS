--TEST--
PEAR_Downloader_Package->initialize() with package.xml
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
    'test_initialize_packagexml'. DIRECTORY_SEPARATOR . 'package.xml';
$dp = newDownloaderPackage(array());
$phpunit->assertNoErrors('after create');
$result = $dp->initialize($pathtopackagexml);
$phpunit->assertTrue($result, 'after initialize');
$phpunit->assertNotNull($file = &$dp->getPackageFile(), 'packagefile test');
$phpunit->assertEquals('test', $file->getPackage(), 'package name test');
$phpunit->assertEquals($pathtopackagexml, $file->getPackageFile(), 'package location test');
$phpunit->assertEquals($pathtopackagexml, $file->getArchiveFile(), 'package archive location test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
