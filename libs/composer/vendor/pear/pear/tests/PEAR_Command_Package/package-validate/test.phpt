--TEST--
package-validate command
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
copy(dirname(__FILE__) . '/packagefiles/validv1.xml', $temp_path . DIRECTORY_SEPARATOR . 'validv1.xml');
copy(dirname(__FILE__) . '/packagefiles/validfakebar.xml', $temp_path . DIRECTORY_SEPARATOR . 'validv2.xml');
copy(dirname(__FILE__) . '/packagefiles/validv1.xml', $temp_path . DIRECTORY_SEPARATOR . 'package.xml');
// 1.0
$ret = $command->run('package-validate', array(), array($temp_path . DIRECTORY_SEPARATOR . 'validv1.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'foo1.php" in package.xml does not exist'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist'),
), 'ret 1');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 'Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'foo1.php" in package.xml does not exist
Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist
Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today
Validation: 2 error(s), 1 warning(s)
',
    'cmd' => 'package-validate',
  ),
), $fakelog->getLog(), 'log 1');

$ret = $command->run('package-validate', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'foo1.php" in package.xml does not exist'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist'),
), 'ret 1.5');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 'Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'foo1.php" in package.xml does not exist
Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist
Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today
Validation: 2 error(s), 1 warning(s)
',
    'cmd' => 'package-validate',
  ),
), $fakelog->getLog(), 'log 1.5');
// 2.0
$ret = $command->run('package-validate', array(), array($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'foo1.php" in package.xml does not exist'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist'),
), 'ret 2');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 'Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'foo1.php" in package.xml does not exist
Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist
Warning: Channel validator warning: field "date" - Release Date "2004-12-25" is not today
Validation: 2 error(s), 1 warning(s)
',
    'cmd' => 'package-validate',
  ),
), $fakelog->getLog(), 'log 2');

copy(dirname(__FILE__) . '/packagefiles/foo1.php', $temp_path . DIRECTORY_SEPARATOR . 'foo1.php');

$ret = $command->run('package-validate', array(), array($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist'),
), 'ret 2.1');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 1,
    1 => 'Analyzing foo1.php',
  ),
  1 => 
  array (
    'info' => 'Error: File "' . $temp_path . DIRECTORY_SEPARATOR .
        'sunger/foo.dat" in package.xml does not exist
Warning: Channel validator warning: field "date" - Release Date "2004-12-25" is not today
Validation: 1 error(s), 1 warning(s)
',
    'cmd' => 'package-validate',
  ),
), $fakelog->getLog(), 'log 2.1');

mkdir($temp_path . DIRECTORY_SEPARATOR . 'sunger');
copy(dirname(__FILE__) . '/packagefiles/sunger/foo.dat', $temp_path . DIRECTORY_SEPARATOR .
    'sunger' . DIRECTORY_SEPARATOR . 'foo.dat');
$contents = file_get_contents($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml');
$contents = str_replace('2004-12-25', date('Y-m-d'), $contents);
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml', 'wb');
fwrite($fp, $contents);
fclose($fp);
$ret = $command->run('package-validate', array(), array($temp_path . DIRECTORY_SEPARATOR . 'validv2.xml'));
$phpunit->assertNoErrors('ret success');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 1,
    1 => 'Analyzing foo1.php',
  ),
  1 => 
  array (
    'info' => 'Validation: 0 error(s), 0 warning(s)
',
    'cmd' => 'package-validate',
  ),
), $fakelog->getLog(), 'log success');

chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
