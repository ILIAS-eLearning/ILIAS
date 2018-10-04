--TEST--
PEAR_PackageFile_Generator_v2->toPackageFile() failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v2;
$pf->setConfig($config);

$generator = &$pf->getDefaultGenerator();
$e = $generator->toPackageFile();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml <package> tag has no version attribute, or version is not 2.0'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <package>, found <> expected one of "name"'),
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v2::toPackageFile: invalid package.xml'),
), 'bad');
$phpunit->assertIsa('PEAR_Error', $e, 'error');

$pf = $parser->parse('<?xml version="1.0" encoding="ISO-8859-1" ?>
<package version="2.0" packagerversion="' . $generator->getPackagerVersion() . '">
 <name>foo</name>
 <channel>pear.php.net</channel>
 <summary>foo</summary>
 <description>foo
hi there
 </description>
 <lead>
  <name>person</name>
  <user>single</user>
  <email>joe@example.com</email>
  <active>yes</active>
 </lead>
 <date>2004-12-25</date>
 <version>
  <release>1.2.0a1</release>
  <api>1.2.0a1</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license>PHP License</license>
  <notes>here are the
multi-line
release notes
  </notes>
  <contents>
   <dir name="\">
    <file role="php" name="foo.php"/>
   </dir>
  </contents>
  <dependencies>
   <required>
    <php>
     <min>4.0.0</min>
     <max>6.0.0</max>
    </php>
    <pearinstaller>
     <min>1.4.0a1</min>
    </pearinstaller>
   </required>
  </dependencies>
 <phprelease/>
</package>
', 'boo.xml');
$generator = &$pf->getDefaultGenerator();

touch ($temp_path . DIRECTORY_SEPARATOR . 'floub');
$e = $generator->toPackageFile($temp_path . DIRECTORY_SEPARATOR . 'floub');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v2::toPackageFile: "' .
    $temp_path . DIRECTORY_SEPARATOR . 'floub" could not be created'),
), 'bad 1');
$phpunit->assertIsa('PEAR_Error', $e, 'error 1');

unlink($temp_path . DIRECTORY_SEPARATOR . 'floub');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'floub');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'floub' . DIRECTORY_SEPARATOR . 'package.xml');
$e = $generator->toPackageFile($temp_path . DIRECTORY_SEPARATOR . 'floub');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v2::toPackageFile: unable to save package.xml as ' .
    $temp_path . DIRECTORY_SEPARATOR . 'floub' . DIRECTORY_SEPARATOR . 'package.xml'),
), 'bad 2');
$phpunit->assertIsa('PEAR_Error', $e, 'error 2');

$pf = $parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'bundlefail1.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'bundlefail1.xml');
$generator = &$pf->getDefaultGenerator();
$e = $generator->toPackageFile($temp_path, PEAR_VALIDATE_PACKAGING, 'tub.xml');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-10" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "' .
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'nosuchpackage-1.9.0.tgz" in package.xml does not exist'),
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v2::toPackageFile: invalid package.xml'),
), 'bad bundle 1');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 1,
    1 => 'Analyzing bundled package fakefoo-1.9.0.tgz',
  ),
  1 => 
  array (
    0 => 1,
    1 => 'Analyzing bundled package fakebar-1.9.0.tgz',
  ),
), $fakelog->getLog(), 'bad bundle 1 log');

$pf = $parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'bundlefail2.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'bundlefail2.xml');
$generator = &$pf->getDefaultGenerator();
$e = $generator->toPackageFile($temp_path, PEAR_VALIDATE_PACKAGING, 'tub.xml');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-10" is not today'),
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v2::toPackageFile: invalid package.xml'),
), 'bad bundle 1.5');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 1,
    1 => 'Analyzing bundled package fakefoo-1.9.0.tgz',
  ),
  1 => 
  array (
    0 => 1,
    1 => 'Analyzing bundled package fakebar-1.9.0.tgz',
  ),
  2 => 
  array (
    0 => 1,
    1 => 'Analyzing bundled package invalid-1.9.0.tgz',
  ),
  3 => 
  array (
    0 => 0,
    1 => 'ERROR: package invalid-1.9.0.tgz is not a valid package',
  ),
), $fakelog->getLog(), 'bad bundle 2 log');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
