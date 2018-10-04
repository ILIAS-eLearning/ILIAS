--TEST--
package command failure, double package.xml (v1 and v2)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
touch($temp_path . DIRECTORY_SEPARATOR . 'bloob.xml');
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'validv1.xml',
    $temp_path . DIRECTORY_SEPARATOR . 'bloob.xml',
));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile', 'message' => 'package.xml "' .
        $temp_path . DIRECTORY_SEPARATOR . 'bloob.xml" has no package.xml <package> version'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in second package file'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in second package file'),
), 'ret');
$phpunit->assertIsa('PEAR_Error', $ret, 'bloob.xml');
if (version_compare(phpversion(), '5.0.0', '>=')) {
    if (version_compare(phpversion(), '5.0.3', '>=')) {
        $errmsg = 'XML error: Invalid document end at line 1';
    } else {
        $errmsg = 'XML error: XML_ERR_DOCUMENT_END at line 1';
    }
} else {
    $errmsg = 'XML error: no element found at line 1';
}
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
  1 => 
  array (
    0 => $errmsg,
    1 => true,
  ),
), $fakelog->getLog(), 'log');
// v2 with invalid
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'validv1.xml',
    dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'fakebar.xml',
));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <package>, found <time> expected one of "lead, developer, contributor, helper, date"'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in second package file'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in second package file'),
), 'ret');
$phpunit->assertIsa('PEAR_Error', $ret, 'fakebar.xml');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Error: Invalid tag order in <package>, found <time> expected one of "lead, developer, contributor, helper, date"',
    1 => true,
  ),
  2 =>
  array (
    0 => 'Parsing of package.xml from file "' . dirname(__FILE__)  .
    DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'fakebar.xml" failed',
    1 => true,
  ),
), $fakelog->getLog(), 'invalid v2 log');
// v1 with invalid
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'validv1.xml',
    dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'v1.xml',
));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No summary found'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in second package file'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in second package file'),
), 'ret');
$phpunit->assertIsa('PEAR_Error', $ret, 'fakebar.xml');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Error: No summary found',
    1 => true,
  ),
  2 =>
  array (
    0 => 'Parsing of package.xml from file "' . dirname(__FILE__)  .
    DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'v1.xml" failed',
    1 => true,
  ),
), $fakelog->getLog(), 'invalid v1 log');
$savedir = getcwd();
chdir($temp_path);
// test double v1
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'validwarnv1.xml',
    dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'validv1.xml',
));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Error: cannot package two package.xml version 1.0, can only package together a package.xml 1.0 and package.xml 2.0'),
    array('package' => 'PEAR_Error', 'message' => 'Error: cannot package two package.xml version 1.0, can only package together a package.xml 1.0 and package.xml 2.0'),
), 'double v1');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
), $fakelog->getLog(), 'double v1 log');
// test double v2
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'validwarnfakebar.xml',
    dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'validfakebar.xml',
));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Error: cannot package two package.xml version 2.0, can only package together a package.xml 1.0 and package.xml 2.0'),
    array('package' => 'PEAR_Error', 'message' => 'Error: cannot package two package.xml version 2.0, can only package together a package.xml 1.0 and package.xml 2.0'),
), 'double v2');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
), $fakelog->getLog(), 'double v2 log');
// test invalid v2
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'packageinvalidv2.xml',
    dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'validv1.xml',
));
$ds = DIRECTORY_SEPARATOR;
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "' . dirname(__FILE__) . $ds . 'packagefiles' .$ds . 'unknown.php" in package.xml does not exist'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in package'),
    array('package' => 'PEAR_Error', 'message' => 'Cannot package, errors in package'),
), 'warning v1');
$phpunit->assertIsa('PEAR_Error', $ret, 'return invalid v2');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
  1 =>
  array (
    0 => 'Error: File "' . dirname(__FILE__) . $ds . 'packagefiles' .$ds . 'unknown.php" in package.xml does not exist',
    1 => true,
  ),
  array (
    0 => 'Error: Channel validator warning: field "date" - Release Date "2004-12-25" is not today',
     1 => true,
   ),
), $fakelog->getLog(), 'invalid v2 log');
// test non-equivalent
$ret = $command->run('package', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR .
    'validfakebar.xml',
    dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'validv1.xml',
));
$ds = DIRECTORY_SEPARATOR;
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 summary "foo" does not match "fakebar"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 package "foo" does not match "fakebar"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 version "1.2.0a1" does not match "1.9.0"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 state "alpha" does not match "stable"'),
    array('package' => 'PEAR_Error', 'message' => 'The two package.xml files are not equivalent!'),
    array('package' => 'PEAR_Error', 'message' => 'The two package.xml files are not equivalent!'),
), 'non-equivalent');
$phpunit->assertIsa('PEAR_Error', $ret, 'non-equivalent');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Attempting to process the second package file',
    1 => true,
  ),
  1 => 
  array (
    0 => 'Analyzing foo1.php',
    1 => true,
  ),
  array (
    0 => 'Warning: Channel validator warning: field "date" - Release Date "2004-12-25" is not today',
    1 => true,
  ),
  array (
    0 => 'Error: package.xml 1.0 state "alpha" does not match "stable"',
    1 => true,
  ),
  array (
    0 => 'Error: package.xml 1.0 summary "foo" does not match "fakebar"',
    1 => true,
  ),
  array (
    0 => 'Error: package.xml 1.0 version "1.2.0a1" does not match "1.9.0"',
    1 => true,
  ),
  array (
    0 => 'Error: package.xml 1.0 package "foo" does not match "fakebar"',
    1 => true,
  ),
), $fakelog->getLog(), 'non-equivalent log');
chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
