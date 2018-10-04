--TEST--
PEAR_PackageFile_Generator_v2->toPackageFile() barebones test, packaging mode
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v2_rw;
$pf->setPackagefile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR
    . 'package.xml');
$pf->setConfig($config);
$pf->setPackage('foo');
$pf->setChannel('pear.php.net');
$pf->setSummary('foo');
$pf->setDate('2004-12-25');
$pf->setDescription('foo
hi there');
$pf->setLicense('PHP License');
$pf->setLogger($fakelog);
$pf->setNotes('here are the
multi-line
release notes');
$pf->setAPIStability('alpha');
$pf->setReleaseStability('alpha');
$pf->setAPIVersion('1.2.0a1');
$pf->setReleaseVersion('1.2.0a1');
$pf->addMaintainer('lead', 'single', 'person', 'joe@example.com');
$pf->setPackageType('php');
$pf->clearContents();
$pf->setPhpDep('4.0.0', '6.0.0');
$pf->setPearinstallerDep('1.4.0a1');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$generator = &$pf->getDefaultGenerator();
$e = $generator->toPackageFile($temp_path, PEAR_VALIDATE_PACKAGING, 'tub.xml');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-25" is not today'),
), 'errors');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 1,
    1 => 'Analyzing foo.php',
  ),
), $fakelog->getLog(), 'packaging log');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'tub.xml', $e, 'filename');
$e = implode('', file($e));
$phpunit->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="' . $generator->getPackagerVersion() . '" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
    http://pear.php.net/dtd/tasks-1.0.xsd
    http://pear.php.net/dtd/package-2.0
    http://pear.php.net/dtd/package-2.0.xsd">
 <name>foo</name>
 <channel>pear.php.net</channel>
 <summary>foo</summary>
 <description>foo
hi there</description>
 <lead>
  <name>person</name>
  <user>single</user>
  <email>joe@example.com</email>
  <active>yes</active>
 </lead>
 <date>' . date('Y-m-d') . '</date>
 <time>' . $pf->getTime() . '</time>
 <version>
  <release>1.2.0a1</release>
  <api>1.2.0a1</api>
 </version>
 <stability>
  <release>alpha</release>
  <api>alpha</api>
 </stability>
 <license>PHP License</license>
 <notes>
here are the
multi-line
release notes
 </notes>
 <contents>
  <dir name="/">
   <file name="foo.php" role="php" />
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
 <phprelease />
</package>', $e, 'xml');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
