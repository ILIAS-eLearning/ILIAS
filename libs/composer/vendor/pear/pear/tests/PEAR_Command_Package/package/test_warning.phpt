--TEST--
package command warnings
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
copy(dirname(__FILE__) . '/packagefiles/validwarnv1.xml', $temp_path . DIRECTORY_SEPARATOR . 'validwarnv1.xml');
copy(dirname(__FILE__) . '/packagefiles/validwarnfakebar.xml', $temp_path . DIRECTORY_SEPARATOR . 'validwarnfakebar.xml');
copy(dirname(__FILE__) . '/packagefiles/foo.php', $temp_path . DIRECTORY_SEPARATOR . 'foo.php');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'sunger');
copy(dirname(__FILE__) . '/packagefiles/sunger/foo.dat', $temp_path . DIRECTORY_SEPARATOR .
    'sunger' . DIRECTORY_SEPARATOR . 'foo.dat');

// test warnings, v1
$ret = $command->run('package', array(), array($temp_path . DIRECTORY_SEPARATOR . 'validwarnv1.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-11-27" is not today'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'in foo.php: class "gronk" not prefixed with package name "foo"'),
), 'warning v1');
$phpunit->assertTrue($ret, 'return warning v1');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo.php',
    1 => true,
  ),
  1 =>
  array (
    0 => 'Warning: in foo.php: class "gronk" not prefixed with package name "foo"',
    1 => true,
  ),
  array (
    0 => 'Warning: Channel validator error: field "date" - Release Date "2004-11-27" is not today',
    1 => true,
  ),
  array (
    0 => 'Package foo-1.2.0a1.tgz done',
    1 => true,
  ),
), $fakelog->getLog(), 'log');
// test warnings, v2
$ret = $command->run('package', array(), array($temp_path . DIRECTORY_SEPARATOR . 'validwarnfakebar.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'in foo.php: class "gronk" not prefixed with package name "fakebar"'),
), 'warning v2');
$phpunit->assertTrue($ret, 'return warning v2');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Analyzing foo.php',
    1 => true,
  ),
  1 =>
  array (
    0 => 'Warning: in foo.php: class "gronk" not prefixed with package name "fakebar"',
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
), $fakelog->getLog(), 'log');
chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
