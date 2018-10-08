--TEST--
PEAR_PackageFile_Generator_v1->toPackageFile() barebones test, packaging mode
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
$pf->setPackagefile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR
    . 'package.xml');
$pf->setConfig($config);
$pf->setPackage('foo');
$pf->setSummary('foo');
$pf->setDate('2004-12-25');
$pf->setDescription('foo
hi there');
$pf->setLicense('PHP License');
$pf->setLogger($fakelog);
$pf->setNotes('here are the
multi-line
release notes');
$pf->setState('alpha');
$pf->setVersion('1.2.0a1');
$pf->addMaintainer('lead', 'single', 'person', 'joe@example.com');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$generator = &$pf->getDefaultGenerator();
$e = $generator->toPackageFile($temp_path, PEAR_VALIDATE_PACKAGING, 'tub.xml');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-12-25" is not today'),
),
'errors');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 1,
    1 => 'Analyzing foo.php',
  ),
), $fakelog->getLog(), 'packaging log');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'tub.xml', $e, 'filename');
$e = implode('', file($e));
$phpunit->assertEquals(str_replace(array("\r\n", "\r"), array("\n", "\n"), '<?xml version="1.0" encoding="UTF-8" ?>
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
  <date>' . date('Y-m-d') . '</date>
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
'), $e, 'xml');
echo 'tests done';
?>
--EXPECT--
tests done
