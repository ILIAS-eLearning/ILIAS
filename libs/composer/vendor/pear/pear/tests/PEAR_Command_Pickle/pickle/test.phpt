--TEST--
package command success, package.xml 2.0
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
copyItem(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'http');
$savedir = getcwd();
chdir($temp_path);
$res = $command->run('pickle', array(), array());
chdir($savedir);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2005-01-01" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2005-01-01" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"'),
), 'afterwards');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 0,
    1 => 'WARNING: Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"',
  ),
  1 =>
  array (
    0 => 'Warning: Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"',
    1 => true,
  ),
  2 =>
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
  3 =>
  array (
    0 => 'Warning: Channel validator warning: field "date" - Release Date "2005-01-01" is not today',
    1 => true,
  ),
  4 =>
  array (
    0 => 'Warning: Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"',
    1 => true,
  ),
  5 =>
  array (
    0 => 'Package pecl_http-0.16.0.tgz done',
    1 => true,
  ),
), $fakelog->getLog(), 'log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
