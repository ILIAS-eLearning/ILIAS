--TEST--
PEAR_Downloader_Package->initialize() with downloadable package.tgz (offline)
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
    'test_initialize_downloadurl'. DIRECTORY_SEPARATOR . 'test-1.0.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tgz', $pathtopackagexml);
$dp = newDownloaderPackage(array('offline' => true));
$phpunit->assertNoErrors('after create');
$result = $dp->initialize('http://www.example.com/test-1.0.tgz');
$phpunit->assertErrors(array('package' => 'PEAR_Error', 'message' =>
    'Cannot download non-local package "http://www.example.com/test-1.0.tgz"'), 'wrong error');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (), $fakelog->getDownload(), 'download callback messages');
$phpunit->assertIsa('PEAR_Error', $result, 'after initialize');
$phpunit->assertNull($dp->getPackageFile(), 'downloadable test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
