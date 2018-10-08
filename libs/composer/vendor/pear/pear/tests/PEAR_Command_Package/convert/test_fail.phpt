--TEST--
convert command failure
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
copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'invalidv1.xml',
    $temp_path . DIRECTORY_SEPARATOR . 'invalid.xml');
copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'nosummary1.xml',
    $temp_path . DIRECTORY_SEPARATOR . 'nosummary.xml');
$e = $command->run('convert', array(), array($temp_path . DIRECTORY_SEPARATOR . 'invalid.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "validv1.xml" in directory "<dir name="/">" has invalid role "ext", should be one of cfg, data, doc, man, php, script, src, test, www'),
), 'invalid packagexml 1');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 'File "validv1.xml" in directory "<dir name="/">" has invalid role "ext", should be one of cfg, data, doc, man, php, script, src, test, www',
    'cmd' => 'no command',
  ),
  1 => 
  array (
    'info' => 'PEAR_Packagefile_v2::toPackageFile: invalid package.xml',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'invalid packagexml');

$e = $command->run('convert', array(), array($temp_path . DIRECTORY_SEPARATOR . 'nosummary.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No summary found'),
    array('package' => 'PEAR_Error', 'message' => 'Parsing of package.xml from file "' . $temp_path .
        DIRECTORY_SEPARATOR . 'nosummary.xml" failed'),
), 'invalid packagexml 2');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 0,
    1 => 'ERROR: No summary found',
  ),
  1 => 
  array (
    'info' => 'No summary found',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'invalid packagexml, no summary');

$e = $command->run('convert', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Unable to open package.xml'),
), 'file not found 1');
$phpunit->assertEquals(array (
), $fakelog->getLog(), 'log 1');

$e = $command->run('convert', array(), array('http://www.example.com/package.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Unable to open http://www.example.com/package.xml'),
), 'file not found 2');
$phpunit->assertEquals(array (
), $fakelog->getLog(), 'log 2');
copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'v2.xml',
    $temp_path . DIRECTORY_SEPARATOR . 'package.xml');

$e = $command->run('convert', array(), array());
$phpunit->assertNoErrors('already a 2.0');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 'package.xml is already a package.xml version 2.0',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'log 3');
chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
