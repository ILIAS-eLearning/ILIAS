--TEST--
package-dependencies command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$e = $command->run('package-dependencies', array(), array($temp_path . DIRECTORY_SEPARATOR . 'invalid.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot open \'' .
        $temp_path . DIRECTORY_SEPARATOR . 'invalid.xml'
        . '\' for parsing'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot open \'' .
        $temp_path . DIRECTORY_SEPARATOR . 'invalid.xml'
        . '\' for parsing'),
), 'file not found');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log 1');

$e = $command->run('package-dependencies', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'packagefiles' . DIRECTORY_SEPARATOR . 'packageinvalidv1.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Parsing of package.xml from file "' .
        dirname(__FILE__) . DIRECTORY_SEPARATOR .
        'packagefiles' . DIRECTORY_SEPARATOR . 'packageinvalidv1.xml'
        . '" failed'),
    array('package' => 'PEAR_Error', 'message' => 'Parsing of package.xml from file "' .
        dirname(__FILE__) . DIRECTORY_SEPARATOR .
        'packagefiles' . DIRECTORY_SEPARATOR . 'packageinvalidv1.xml'
        . '" failed'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No summary found'),
), 'validation errors');

$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 0,
    1 => 'ERROR: No summary found',
  ),
), $fakelog->getLog(), 'log 2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
