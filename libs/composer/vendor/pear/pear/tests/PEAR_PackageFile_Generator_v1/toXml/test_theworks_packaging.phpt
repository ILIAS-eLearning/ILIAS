--TEST--
PEAR_PackageFile_Generator_v1->toXml() all bells and whistles package.xml
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = &$parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'theworks.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'theworks.xml');
$generator = &$pf->getDefaultGenerator();
$e = $generator->toXml(PEAR_VALIDATE_PACKAGING);
$phpunit->assertNoErrors('errors');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 1,
    1 => 'Analyzing foo.php',
  ),
), $fakelog->getLog(), 'packaging log');
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
  <date>' . $pf->getDate() . '</date>
  <license>PHP License</license>
  <state>alpha</state>
  <notes>here are the
multi-line
release notes
  </notes>
  <deps>
   <dep type="ext" rel="ge" version="1.0" optional="yes">xmlrpc</dep>
   <dep type="pkg" rel="lt" version="1.2" optional="no">Console_Getopt</dep>
   <dep type="php" rel="ge" version="4.3.0"/>
  </deps>
  <configureoptions>
   <configureoption name="one" default="three" prompt="two"/>
  </configureoptions>
  <filelist>
   <file role="data" baseinstalldir="freeb" install-as="merbl.dat" name="sunger/foo.dat">
    <replace from="@pv@" to="version" type="package-info"/>
   </file>
   <file role="php" baseinstalldir="freeb" install-as="merbl.php" name="foo.php">
    <replace from="@pv@" to="version" type="package-info"/>
   </file>
  </filelist>
 </release>
 <changelog>
   <release>
    <version>1.3.3</version>
    <date>2004-10-28</date>
    <state>stable</state>
    <notes>Installer:
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
    <version>1.3.2</version>
    <date>2004-10-28</date>
    <state>stable</state>
    <notes>Installer:
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
</package>
'), $e, 'xml');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
