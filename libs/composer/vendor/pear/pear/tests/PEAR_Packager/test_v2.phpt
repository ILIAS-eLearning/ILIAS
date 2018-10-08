--TEST--
PEAR_Packager->package() success, package.xml 2.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$savedir = getcwd();
chdir($temp_path);
// setup
copy(dirname(__FILE__) . '/packagefiles/validfakebar.xml', $temp_path . DIRECTORY_SEPARATOR . 'validv2.xml');
copy(dirname(__FILE__) . '/packagefiles/foo1.php', $temp_path . DIRECTORY_SEPARATOR . 'foo1.php');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'sunger');
copy(dirname(__FILE__) . '/packagefiles/sunger/foo.dat', $temp_path . DIRECTORY_SEPARATOR .
    'sunger' . DIRECTORY_SEPARATOR . 'foo.dat');
// normal
$ret = $packager->package($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
), '1');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'fakebar-1.9.0.tgz', 'fakebar-1.9.0.tgz');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  array (
    0 => 'Warning: Channel validator warning: field "date" - Release Date "2004-12-25" is not today',
    1 => true,
  ),
  array (
    0 => 'Package fakebar-1.9.0.tgz done',
    1 => true,
  ),
), $fakelog->getLog(), 'log 1');

// uncompressed
$ret = $packager->package($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml', false);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
), '1.5');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'fakebar-1.9.0.tar', 'fakebar-1.9.0.tar');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  array (
    0 => 'Warning: Channel validator warning: field "date" - Release Date "2004-12-25" is not today',
    1 => true,
  ),
  array (
    0 => 'Package fakebar-1.9.0.tar done',
    1 => true,
  ),
), $fakelog->getLog(), 'log 1.5');

mkdir ($temp_path . DIRECTORY_SEPARATOR . 'CVS');
touch ($temp_path . DIRECTORY_SEPARATOR . 'CVS' . DIRECTORY_SEPARATOR . 'Root');
// with cvs
$ret = $packager->package($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
), '2');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'fakebar-1.9.0.tgz', 'fakebar-1.9.0.tgz 2');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  array (
    0 => 'Warning: Channel validator warning: field "date" - Release Date "2004-12-25" is not today',
    1 => true,
  ),
  array (
    0 => 'Package fakebar-1.9.0.tgz done',
    1 => true,
  ),
  array (
    0 => 'Tag the released code with `pear cvstag ' . $temp_path . DIRECTORY_SEPARATOR . 'validv2.xml\'',
    1 => true,
  ),
  array (
    0 => '(or set the CVS tag RELEASE_1_9_0 by hand)',
    1 => true,
  ),
), $fakelog->getLog(), 'log 2');
chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
