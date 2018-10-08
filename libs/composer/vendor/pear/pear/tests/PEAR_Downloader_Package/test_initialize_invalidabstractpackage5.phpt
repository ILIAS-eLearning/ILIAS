--TEST--
PEAR_Downloader_Package->initialize() with unknown channel
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$dp = newDownloaderPackage(array());
$phpunit->assertNoErrors('after create');
$result = $dp->initialize('foo/test');
$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'invalid package name/package file "foo/test"'
    ),
    array(
        'package' => 'PEAR_Error',
        'message' => '')
    ),
    'after initialize');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 1,
    1 => 'Attempting to discover channel "foo"...',
  ),
  1 =>
  array (
    0 => 1,
    1 => 'Attempting fallback to https instead of http on channel "foo"...',
  ),
  2 =>
  array (
    0 => 0,
    1 => 'unknown channel "foo" in "foo/test"',
  ),
  3 =>
  array (
    0 => 0,
    1 => 'invalid package name/package file "foo/test"',
  ),
  4 =>
  array (
    0 => 2,
    1 => 'Cannot initialize \'foo/test\', invalid or missing package file',
   ),
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
    0 =>
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 =>
    array (
      0 => 'setup',
      1 => 'self',
    ),
    ), $fakelog->getDownload(), 'download callback messages');
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
