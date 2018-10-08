--TEST--
PEAR_Downloader_Package->initialize() with invalid abstract package (Package not found)
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

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tgz', $pathtopackagexml);

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/test/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/allreleases.xml", false, false);

$dp = newDownloaderPackage(array());
$phpunit->assertNoErrors('after create');
$result = $dp->initialize('test');

$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'No releases available for package "pear.php.net/test"'
    ),
    array(
        'package' => 'PEAR_Error',
        'message' => 'No releases available for package "pecl.php.net/test"'
    ),
    array(
        'package' => 'PEAR_Error',
        'message' => ''
    ),
    array(
        'package' => 'PEAR_Error',
        'message' => 'File http://pear.php.net:80/rest/r/test/allreleases.xml not valid (received: HTTP/1.1 404 http://pear.php.net/rest/r/test/allreleases.xml Is not valid)',
    ),
    array(
        'package' => 'PEAR_Error',
        'message' => 'File http://pecl.php.net:80/rest/r/test/allreleases.xml not valid (received: HTTP/1.1 404 http://pecl.php.net/rest/r/test/allreleases.xml Is not valid)',
    ),
), 'after initialize');

$phpunit->assertEquals(array (
  array (
    0 => 0,
    1 => 'No releases available for package "pear.php.net/test"',
  ),
  array (
    0 => 2,
    1 => 'Cannot initialize \'test\', invalid or missing package file',
   ),
), $fakelog->getLog(), 'log messages');

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
