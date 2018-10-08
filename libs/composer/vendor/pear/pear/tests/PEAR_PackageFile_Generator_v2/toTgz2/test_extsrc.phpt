--TEST--
PEAR_PackageFile_Generator_v2->toTgz2() (extsrcrelease)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php


$save____dir = getcwd();
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
chdir($temp_path);
require_once 'PEAR/Packager.php';

$pf = $parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'extsrcrelease1.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'extsrcrelease1.xml');
$generator = &$pf->getDefaultGenerator();
$packager = new PEAR_Packager;
$null = null;
mkdir($temp_path . DIRECTORY_SEPARATOR . 'gron');
$e = $generator->toTgz2($packager, $null, true, $temp_path . DIRECTORY_SEPARATOR . 'gron');

$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-10" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-12-10" is not today'),
), 'errors');

$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 3,
    1 => 'doing 1 substitution(s) for ' . $temp_path . DIRECTORY_SEPARATOR . 'gron' .
        DIRECTORY_SEPARATOR . 'sunger/foo.dat',
  ),
  1 =>
  array (
    0 => 3,
    1 => 'doing 1 substitution(s) for ' . $temp_path . DIRECTORY_SEPARATOR . 'gron' .
        DIRECTORY_SEPARATOR . 'foo.php',
  ),
), $fakelog->getLog(), 'packaging log');

$pkg = new PEAR_PackageFile($config);
$newpf = &$pkg->fromTgzFile($e, PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('errors');
$xml = $newpf->getFileContents('package.xml');
$phpunit->showall();

$phpunit->assertEquals('<?xml version="1.0" encoding="ISO-8859-1"?>
<package packagerversion="' . $generator->getPackagerVersion() . '" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
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
 <time>' . $newpf->getTime() . '</time>
 <version>
  <release>1.2.0a1</release>
  <api>1.2.0a1</api>
 </version>
 <stability>
  <release>alpha</release>
  <api>alpha</api>
 </stability>
 <license uri="http://www.php.net/license/3_0.txt">PHP License</license>
 <notes>
here are the
multi-line
release notes
 </notes>
 <contents>
  <dir name="/">
   <file baseinstalldir="freeb" md5sum="ed0384ad29e60110b310a02e95287ee6" name="sunger/foo.dat" role="data">
    <tasks:replace from="@pv@" to="version" type="package-info" />
   </file>
   <file baseinstalldir="freeb" md5sum="' . (OS_WINDOWS ? 'ed0384ad29e60110b310a02e95287ee6' :
    '452925d5182994846dbe3b9518db84d8') . '" name="foo.php" role="src">
    <tasks:replace from="@pv@" to="version" type="package-info" />
   </file>
  </dir>
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.3.0</min>
    <max>6.0.0</max>
   </php>
   <pearinstaller>
    <min>1.4.0a1</min>
   </pearinstaller>
   <package>
    <name>Console_Getopt</name>
    <channel>pear.php.net</channel>
    <max>1.2</max>
    <exclude>1.2</exclude>
   </package>
  </required>
  <optional>
   <extension>
    <name>xmlrpc</name>
    <min>1.0</min>
   </extension>
  </optional>
 </dependencies>
 <providesextension>foo</providesextension>
 <extsrcrelease>
  <configureoption default="hello" name="one" prompt="boo" />
 </extsrcrelease>
 <changelog>
  <release>
   <version>
    <release>1.3.3</release>
    <api>1.3.3</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2004-10-28</date>
   <license uri="http://www.php.net/license/3_0.txt">PHP License</license>
   <notes>
Installer:
 * fix Bug #1186 raise a notice error on PEAR::Common $_packageName
 * fix Bug #1249 display the right state when using --force option
 * fix Bug #2189 upgrade-all stops if dependancy fails
 * fix Bug #1637 The use of interface causes warnings when packaging with PEAR
 * fix Bug #1420 Parser bug for T_DOUBLE_COLON
 * fix Request #2220 pear5 build fails on dual php4/php5 system
 * fix Bug #1163  pear makerpm fails with packages that supply role=&quot;doc&quot;

Other:
 * add PEAR_Exception class for PHP5 users
 * fix critical problem in package.xml for linux in 1.3.2
 * fix staticPopCallback() in PEAR_ErrorStack
 * fix warning in PEAR_Registry for windows 98 users
   </notes>
  </release>
  <release>
   <version>
    <release>1.3.2</release>
    <api>1.3.2</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2004-10-28</date>
   <license uri="http://www.php.net/license/3_0.txt">PHP License</license>
   <notes>
Installer:
 * fix Bug #1186 raise a notice error on PEAR::Common $_packageName
 * fix Bug #1249 display the right state when using --force option
 * fix Bug #2189 upgrade-all stops if dependancy fails
 * fix Bug #1637 The use of interface causes warnings when packaging with PEAR
 * fix Bug #1420 Parser bug for T_DOUBLE_COLON
 * fix Request #2220 pear5 build fails on dual php4/php5 system
 * fix Bug #1163  pear makerpm fails with packages that supply role=&quot;doc&quot;

Other:
 * add PEAR_Exception class for PHP5 users
 * fix critical problem in package.xml for linux in 1.3.2
 * fix staticPopCallback() in PEAR_ErrorStack
 * fix warning in PEAR_Registry for windows 98 users
   </notes>
  </release>
 </changelog>
</package>', $xml, 'xml');

$phpunit->assertEquals('<?php
?>', $newpf->getFileContents('foo.php'), 'foo.php content');
$phpunit->assertEquals('<?php
?>', $newpf->getFileContents('sunger/foo.dat'), 'sunger/foo.dat content');
chdir($save____dir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
