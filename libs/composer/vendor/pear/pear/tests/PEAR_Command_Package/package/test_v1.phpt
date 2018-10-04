--TEST--
package command success, package.xml 1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$savedir = getcwd();
chdir($temp_path);
// setup
copy(dirname(__FILE__) . '/packagefiles/validv1.xml', $temp_path . DIRECTORY_SEPARATOR . 'validv1.xml');
copy(dirname(__FILE__) . '/packagefiles/validv1.xml', $temp_path . DIRECTORY_SEPARATOR . 'package.xml');
copy(dirname(__FILE__) . '/packagefiles/foo1.php', $temp_path . DIRECTORY_SEPARATOR . 'foo1.php');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'sunger');
copy(dirname(__FILE__) . '/packagefiles/sunger/foo.dat', $temp_path . DIRECTORY_SEPARATOR .
    'sunger' . DIRECTORY_SEPARATOR . 'foo.dat');

$ret = $command->run('package', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today')
), '1');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'foo-1.2.0a1.tgz', 'foo-1.2.0a1.tgz');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today',
    1 => true,
  ),
  array (
    0 => 'Package foo-1.2.0a1.tgz done',
    1 => true,
  ),
), $fakelog->getLog(), 'log 1');

$ret = $command->run('package', array(), array($temp_path . DIRECTORY_SEPARATOR .
    'validv1.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today')
), '2');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'foo-1.2.0a1.tgz', 'foo-1.2.0a1.tgz');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today',
    1 => true,
  ),
  array (
    0 => 'Package foo-1.2.0a1.tgz done',
    1 => true,
  ),
), $fakelog->getLog(), 'log 1');

$ret = $command->run('package', array('nocompress' => true), array($temp_path . DIRECTORY_SEPARATOR .
    'validv1.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today')
), '2.5');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'foo-1.2.0a1.tar', 'foo-1.2.0a1.tar');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today',
    1 => true,
  ),
  array (
    0 => 'Package foo-1.2.0a1.tar done',
    1 => true,
  ),
), $fakelog->getLog(), 'log 1.5');

mkdir ($temp_path . DIRECTORY_SEPARATOR . 'CVS');
touch ($temp_path . DIRECTORY_SEPARATOR . 'CVS' . DIRECTORY_SEPARATOR . 'Root');

$ret = $command->run('package', array(), array($temp_path . DIRECTORY_SEPARATOR .
    'validv1.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today')
), '2.6');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'foo-1.2.0a1.tgz', 'foo-1.2.0a1.tgz 2');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today',
    1 => true,
  ),
  array (
    0 => 'Package foo-1.2.0a1.tgz done',
    1 => true,
  ),
  array (
    0 => 'Tag the released code with `pear cvstag ' . $temp_path . DIRECTORY_SEPARATOR . 'validv1.xml\'',
    1 => true,
  ),
  array (
    0 => '(or set the CVS tag RELEASE_1_2_0a1 by hand)',
    1 => true,
  ),
), $fakelog->getLog(), 'log 2');
chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
