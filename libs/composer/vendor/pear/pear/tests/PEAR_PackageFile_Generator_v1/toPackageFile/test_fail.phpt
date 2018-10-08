--TEST--
PEAR_PackageFile_Generator_v1->toPackageFile() failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);

$generator = &$pf->getDefaultGenerator();
$e = $generator->toPackageFile();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing Package Name'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No summary found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing description'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing license'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release version found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release state found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release date found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No maintainers found, at least one must be defined'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No files in <filelist> section of package.xml'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release notes found'),
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v1::toPackageFile: invalid package.xml'),
), 'bad');
$phpunit->assertIsa('PEAR_Error', $e, 'error');

$pf = &$parser->parse('<?xml version="1.0" encoding="ISO-8859-1" ?>
<!DOCTYPE package SYSTEM "http://pear.php.net/dtd/package-1.0">
<package version="1.0" packagerversion="' . $generator->getPackagerVersion() . '">
 <name>foo</name>       
 <summary>foo</summary>
 <description>foo
hi there
 </description>
 <maintainers>
  <maintainer>
   <user>single</user>
   <name>person</name>
   <email>joe@example.com</email>
   <role>lead</role>
  </maintainer>
  </maintainers>
 <release>
  <version>1.2.0a1</version>
  <date>2004-12-25</date>
  <license>PHP License</license>
  <state>alpha</state>
  <notes>here are the
multi-line
release notes
  </notes>
  <filelist>
   <file role="php" name="foo.php"/>
  </filelist>
 </release>
</package>
', 'boo.xml');
$generator = &$pf->getDefaultGenerator();

touch ($temp_path . DIRECTORY_SEPARATOR . 'floub');
$e = $generator->toPackageFile($temp_path . DIRECTORY_SEPARATOR . 'floub');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v1::toPackageFile: "' .
    $temp_path . DIRECTORY_SEPARATOR . 'floub" could not be created'),
), 'bad');
$phpunit->assertIsa('PEAR_Error', $e, 'error');

unlink($temp_path . DIRECTORY_SEPARATOR . 'floub');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'floub');
mkdir($temp_path . DIRECTORY_SEPARATOR . 'floub' . DIRECTORY_SEPARATOR . 'package.xml');
$e = $generator->toPackageFile($temp_path . DIRECTORY_SEPARATOR . 'floub');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v1::toPackageFile: unable to save package.xml as ' .
    $temp_path . DIRECTORY_SEPARATOR . 'floub' . DIRECTORY_SEPARATOR . 'package.xml'),
), 'bad');
$phpunit->assertIsa('PEAR_Error', $e, 'error');

echo 'tests done';
?>
--EXPECT--
tests done
